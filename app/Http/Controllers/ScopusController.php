<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Citation;
use App\CitationMember;
use App\CitationMetadata;
use App\Collection;
use App\Document;
use App\PublishedMetadata;
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
	 * Array of document types from Scopus mapped to citation types.
	 *
	 * @var array
	 * @see https://dev.elsevier.com/tips/ScopusSearchTips.htm
	 */
	protected $scopusTypes = [
		'ar' => 'article',
		'ab' => 'abstract_report',
		'ip' => 'article_in_press',
		'bk' => 'book',
		'ch' => 'chapter',
		'cp' => 'conference_paper',
		'cr' => 'conference_review',
		'ed' => 'editorial',
		'er' => 'erratum',
		'le' => 'letter',
		'no' => 'note',
		'pr' => 'press_release',
		're' => 'review',
		'sh' => 'short_survey',
	];

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
		if(in_array($entry->subtype, array_keys($this->scopusTypes))) {
			$entryType = $this->scopusTypes[$entry->subtype];
		}
		else
		{
			// we do not have a valid entry type so just skip it
			return null;
		}

		// is there an author affiliation link? If so, we can use that
		// later on to retrieve the set of collaborators
		foreach($entry->link as $link) {
			if($link->{'@ref'} == 'author-affiliation') {
				$entryArray['author_affiliation'] = $link->{'@href'};
			}
		}

		// grab the Scopus ID, title, and creator information
		$entryArray['scopus_id'] = str_ireplace('SCOPUS_ID:', '', $entry->{'dc:identifier'});
		$entryArray['title'] = (!empty($entry->{'dc:title'}) ? $entry->{'dc:title'} : null);
		$entryArray['creator'] = $entry->{'dc:creator'};

		// figure out the publication data
		$pubName = (!empty($entry->{'prism:publicationName'}) ? $entry->{'prism:publicationName'} : null);
		$pubVol = (!empty($entry->{'prism:volume'}) ? $entry->{'prism:volume'} : null);
		$pubIssue = (!empty($entry->{'prism:issueIdentifier'}) ? $entry->{'prism:issueIdentifier'} : null);
		$pubPages = (!empty($entry->{'prism:pageRange'}) ? $entry->{'prism:pageRange'} : null);

		$entryArray['publication'] = [
			'type' => $entryType,
			'name' => $pubName,
			'volume' => $pubVol,
			'issue' => $pubIssue,
			'pages' => $pubPages,
			'published_date' => $entry->{'prism:coverDate'},
		];

		// let's get some information about the document
		$entryArray['document'] = [
			'issn' => (!empty($entry->{'prism:issn'}) ? $entry->{'prism:issn'} : null),
			'isbn' => (!empty($entry->{'prism:isbn'}) ? $entry->{'prism:isbn'} : null),
			'doi' => (!empty($entry->{'prism:doi'}) ? $entry->{'prism:doi'} : null),
		];

		return $entryArray;
	}

	/**
	 * Performs a Scopus query and returns an array containing the set of
	 * citation metadata for the specified URI.
	 *
	 * @param string $startUri The URI to start from; this URI grabs the
	 * initial set of data but the pagination links will be followed
	 * @param array $citations Array of existing citations for recursion
	 *
	 * @return array
	 */
	protected function doScopusCitationQuery($startUri, $citations=[]) {
		// perform the Guzzle call and retrieve the search results
		$response = $this->doGuzzle($startUri);
		$search_results = $response->{'search-results'};
		$next = "";

		// if no results were retrieved, return an empty array
		if($search_results->{'opensearch:totalResults'} == '0') {
			return [];
		}

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
			return $this->doScopusCitationQuery($next, $citations);
		}

		// we are at the end of the search results, so return the citations
		return $citations;
	}

	/**
	 * Inserts the citations array into the database. Returns a count representing
	 * the number of records that were inserted successfully. Returns -1 if there
	 * was an error inserting records.
	 *
	 * @param User $user The user to whom these citations will be associated
	 * @param array $citations The array of citations generated from Scopus
	 * @return int
	 */
	protected function insertCitationRecords($user, $citations) {
		// let's remove all citations in the array that are already associated
		// with this individual
		$existing_ids = $user->citations->pluck('scopus_id')->toArray();
		$citations = array_where($citations, function($value, $key) use ($existing_ids) {
			return !in_array($value['scopus_id'], $existing_ids);
		});

		// only attempt the DB insertion if there are new citations
		if(!empty($citations)) {
			try {
				DB::beginTransaction();

				// grab the next auto-incrementing ID
	            $nextId = 1;
	            $latestCitation = Citation::orderBy('id', 'DESC')->first();
	            if(!empty($latestCitation)) {
	                $nextId = $latestCitation->id + 1;
	            }

	            $citationInserts = [];
	            $citationMemberInserts = [];
	            $citationMetadataInserts = [];
	            $collectionInserts = [];
	            $documentInserts = [];
	            $publishedMetadataInserts = [];

	            // Assign a citation collection ID to each entry; I know this
	            // isn't the best way to do it but I can't think of any other
	            // way that doesn't involve eight separate database calls per
	            // citation. I'm doing everything I can to do these imports as
	            // batch operations to keep the number of database calls the
	            // same regardless of how many citations there are to import.
	            foreach($citations as $citation) {
	            	$citationId = 'citations:' . $nextId++;

	            	$citationInserts[] = [
	            		'citation_id' => $citationId,
	            		'scopus_id' => $citation['scopus_id'],
	            		'citation_type' => $citation['publication']['type'],
	            		'collaborators' => $citation['creator'],
	            	];

	            	$citationMemberInserts[] = [
	            		'parent_entities_id' => $citationId,
	            		'individuals_id' => $user->user_id,
	            		'role_position' => 'author',
	            		'precedence' => '0',
	            	];

	            	// special care needs to be taken with the citation metadata
	            	// so the correct column is filled in for publication title
	            	$citationMetadata = [
	            		'citation_id' => $citationId,
	            		'title' => $citation['title'],
	            		'book_title' => null,
	            		'journal' => null,
	            	];
	            	if($citation['publication']['type'] == 'book') {
	            		$citationMetadata['book_title'] = $citation['publication']['name'];
	            	}
	            	else if($citation['publication']['type'] == 'article') {
	            		$citationMetadata['journal'] = $citation['publication']['name'];
	            	}
	            	$citationMetadataInserts[] = $citationMetadata;

	            	$collectionInserts[] = [
	            		'citation_id' => $citationId,
	            		'number' => $citation['publication']['issue'],
	            		'volume' => $citation['publication']['volume'],
	            		'pages' => $citation['publication']['pages'],
	            	];

	            	$documentInserts[] = [
	            		'citation_id' => $citationId,
	            		'doi' => $citation['document']['doi'],
	            		'issn' => $citation['document']['issn'],
	            		'isbn' => $citation['document']['isbn'],
	            	];

	            	$publishedMetadataInserts[] = [
	            		'citation_id' => $citationId,
	            		'date' => $citation['publication']['published_date'],
	            	];

	            	// we are not getting back publisher information from Scopus
	            	// so there are no inserts for the Publisher model
	            }

	            // perform the inserts
	            Citation::insert($citationInserts);
	            CitationMember::insert($citationMemberInserts);
	            CitationMetadata::insert($citationMetadataInserts);
	            Collection::insert($collectionInserts);
	            Document::insert($documentInserts);
	            PublishedMetadata::insert($publishedMetadataInserts);

				DB::commit();
			}
			catch(\Exception $e) {
				DB::rollBack();
	            logErrorException('Could not import ' . count($citations) . ' citation(s).', $e);
	            return -1;
			}
		}

		return count($citations);
	}

	/**
	 * Generates and returns an array representing an import response. The
	 * array of citations that were imported and the number that was actually
	 * imported are included as parameters.
	 *
	 * @param array $citations The set of citations that were generated
	 * @param int $numImported The number of citations that were actually imported
	 *
	 * @return array
	 */
	protected function generateImportResponse(Request $request, $citations, $numImported) {
		if(count($citations) == 0) {
			// no citations retrieved from Scopus
			// (not successful but the request did not fail)
			return generateMessageResponse(
                $request, 'No citations to import for that person', 200, false
            );
		}
		else
		{
			if($numImported == -1) {
				// an error occurred during import
				return generateErrorResponse(
                	$request, 'The ' . count($citations) . ' citation(s) could not be imported', 500
            	);
			}
			else if($numImported == 0) {
				// no new citations to import
				return generateMessageResponse(
					$request, 'There are no new citations to import for that person', 200
				);
			}
		}

        // new citations were imported
		return generateMessageResponse($request,
			count($citations) . ' new citation(s) imported successfully'
		);
	}

	/**
	 * Imports a set of citations from Scopus using the Scopus Search API based
	 * upon the ORCID of an individual.
	 *
	 * @param string $orcid The ORCID of the individual
	 * @return Response
	 */
	public function importByORCID(Request $request, $orcid) {
		// retrieve the user by ORCID first
		$user = User::with('citations')->whereOrcid($orcid)
			->firstOrFail();

		// perform the Scopus query
		$citations = $this->doScopusCitationQuery(
			"/content/search/scopus?query=orcid($orcid)"
		);

		// import the records and return a JSON response
		$numImported = $this->insertCitationRecords($user, $citations);
		return $this->generateImportResponse($request, $citations, $numImported);
	}

	/**
	 * Imports a set of citations from Scopus using the Scopus Search API based
	 * upon the author ID of an individual.
	 *
	 * @param string $author_id The author ID of the individual
	 * @return Response
	 */
	public function importByAuthorId(Request $request, $author_id) {
		// retrieve the user by author ID first
		$user = User::with('citations')->whereAuthorId($author_id)
			->firstOrFail();

		// perform the Scopus query
		$citations = $this->doScopusCitationQuery(
			"/content/search/scopus?query=au-id($author_id)"
		);

        // import the records and return a JSON response
		$numImported = $this->insertCitationRecords($user, $citations);
		return $this->generateImportResponse($request, $citations, $numImported);
	}
}