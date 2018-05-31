<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Citation;
use App\User;

use App\Exceptions\InvalidPayloadTypeException;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\NoDataException;

use CSUNMetaLab\Guzzle\Factories\HandlerGuzzleFactory;

use DB;
use Log;

class ScopusController extends Controller
{
	/**
	 * The HandlerGuzzle instance.
	 *
	 * @var HandlerGuzzle
	 */
	protected $guzzle;

	/**
	 * Constructs a new ScopusController instance.
	 */
	public function __construct() {
		// resolve a Guzzle instance and add the API key header
		$this->guzzle = HandlerGuzzleFactory::fromDefaults();
		$this->guzzle->setHeader('X-ELS-APIKey', config('scopus.key'));
	}

	/**
	 * Performs a Guzzle call and returns the result as JSON.
	 *
	 * @param string $uri The URI to hit
	 * @param string $method Optional HTTP method (default is GET)
	 *
	 * @return JSON object
	 */
	protected function doGuzzle($uri, $method='GET') {
		// apply the niceness first so we do not get throttled constantly
		sleep(config('scopus.niceness'));

		$method = strtolower($method);
		$response = $this->guzzle->$method($uri);

		// resolve and return the response body as JSON
		return $this->guzzle->resolveResponseBody($response, 'json');
	}

	/**
	 * Generates the array for an entry based upon the JSON object passed
	 * as an argument. This can also return null if the entry type is invalid.
	 *
	 * @param JSON $entry The entry to parse
	 * @return array|null
	 */
	protected function generateEntryArray($entry) {
		$entryArray = [];

		// let's get some information about the publication
		$entryType = '';
		if($entry->subtype == 'ar' || $entry->subtype == 'cp') {
			// article or conference paper (Scopus classifies conference
			// papers under searches for articles as well)
			$entryType = 'article';
		}
		else if($entry->subtype == 'bk') {
			$entryType = 'book';
		}
		else if($entry->subtype == 'ch') {
			$entryType = 'chapter';
		}

		// if we do not have a valid entry type, just skip it
		if(empty($entryType)) {
			return null;
		}

		// is there an author affiliation link? If so, we can use that
		// later on to retrieve the set of collaborators
		foreach($entry->link as $link) {
			if($link->{'@ref'} == 'author-affiliation') {
				$entryArray['author_affiliation'] = $link->{'@href'};
			}
		}

		// grab the title and creator information
		$entryArray['title'] = $entry->{'dc:title'};
		$entryArray['creator'] = $entry->{'dc:creator'};

		$entryArray['publication'] = [
			'type' => $entryType,
			'name' => $entry->{'prism:publicationName'},
			'volume' => $entry->{'prism:volume'},
			'issue' => $entry->{'prism:issueIdentifier'},
			'pages' => $entry->{'prism:pageRange'},
		];

		// everything should have a published date
		$entryArray['publication']['published_date'] = $entry->{'prism:coverDate'};

		// let's get some information about the document
		$entryArray['document'] = [
			'doi' => $entry->{'prism:doi'},
		];

		return $entryArray;
	}

	/**
	 * Performs a Scopus query and returns an array containing the set of
	 * citation metadata for the specified URI.
	 *
	 * @param string $startUri The URI to start from; this URI grabs the
	 * initial set of data but the pagination links will be followed
	 *
	 * @return array
	 */
	protected function doScopusCitationQuery($startUri) {
		$citations = [];

		// perform the Guzzle call and retrieve the search results
		$response = $this->doGuzzle($startUri);
		$search_results = $response->{'search-results'};
		$next = "";

		// do we have a "next" link? If so, that describes the next page
		// of the results we will need to retrieve
		foreach($search_results->link as $link) {
			if($link->{'@ref'} == 'next') {
				$next = $link->{'@href'};
			}
		}

		// let's build up the citations array
		foreach($search_results->entry as $entry) {
			// add the entry onto the set of citations
			$entryArr = $this->generateEntryArray($entry);
			if(!empty($entryArr)) {
				$citations[] = $entryArr;
			}
		}

		// if we do have a "next" link, we will recurse, grab the next page
		// of results, and add them onto the existing array
		if(!empty($next)) {
			return $citations + $this->doScopusCitationQuery($next);
		}

		// we are at the end of the search results, so return the citations
		return $citations;
	}

	/**
	 * Imports a set of citations from Scopus using the Scopus Search API based
	 * upon the ORCID of an individual.
	 *
	 * @param string $orcid The ORCID of the individual
	 * @return Response
	 */
	public function importByORCID(Request $request, $orcid) {
		// perform the Scopus query
		$response = $this->doScopusCitationQuery(
			"/content/search/scopus?query=orcid($orcid)"
		);

        return response()->json($response);
	}

	/**
	 * Imports a set of citations from Scopus using the Scopus Search API based
	 * upon the author ID of an individual.
	 *
	 * @param string $author_id The author ID of the individual
	 * @return Response
	 */
	public function importByAuthorId(Request $request, $author_id) {
		// perform the Scopus query
		$response = $this->doScopusCitationQuery(
			"/content/search/scopus?query=au-id($author_id)"
		);

        return response()->json($response);
	}
}