<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Citation;
use App\User;

use App\Exceptions\InvalidPayloadTypeException;

use DB;
use Log;

class CitationsController extends Controller
{
    /**
     * Returns the common base citation query that will be used for all other
     * controller methods here.
     *
     * @return Builder
     */
    private function getBaseCitationQuery() {
        return Citation::with(
            'metadata',
            'collection',
            'document',
            'publishedMetadata',
            'publisher',
            'members.facultyUrl'
        );
    }

    /**
     * Returns a set of citations, optionally filtered by the email address
     * of a particular individual and a particular citation type.
     *
     * @param string $type Optional citation type by which to filter
     * @return Response
     */
    public function index(Request $request, $type="citations") {
        $citations = $this->getBaseCitationQuery();

        // if we have an actual citation type, filter by that value
        if($type != "citations") {
            // make the citation type singular
            $citations = $citations->where('citation_type',
                str_singular($type));
        }

        // if we have a provided email address, filter by that specific
        // individual
        if($request->has('email')) {
            $email = $request->input('email');
            $user = User::whereEmail($email)->first(); // intentionally not findOrFail()
            $citations = $citations->whereHas('members', function($q) use ($user) {
                if(!empty($user)) {
                    $q->where('user_id', $user->user_id);
                }
                else
                {
                    // there should never be a NULL record retrieved by the
                    // members relationship so this will effectively clear out
                    // the set of data retrieved by the query
                    $q->whereNull('user_id');
                }
            });
        }

        // generate the response and send everything back
        return generateCollectionResponse($type, $citations->get());
    }

    /**
     * Returns a single citation based upon a given ID.
     *
     * @param int $id The ID of the citation that will be found
     * @return Response
     */
    public function show($id) {
        $citation = $this->getBaseCitationQuery();

        // filter record by the citation's partial ID
        $citation = $citation->wherePartialId($id)
            ->firstOrFail(); // intentionally firstOrFail()
        $type = $citation->citation_type;

        // generate the response and send everything back
        return generateCollectionResponse($type, $citation);
    }

    /**
     * Processes a citation creation request. The data is expected to be JSON.
     *
     * @param Request $request The request to process
     * @return Response
     */
    public function store(Request $request) {
        // ensure this is a JSON request
        $this->checkRequestTypeJson($request);

        // define the JSON sub-object keys
        $metaKey = "metadata";
        $pubMetaKey = "published_metadata";
        $membersKey = "members";
        $docKey = "document";
        $pubKey = "publisher";
        $collKey = "collection";

        // now we need to validate the minimum data in the payload
        $this->validate($request, [
            'type' => 'required|in:article,book,chapter,thesis',
            "{$metaKey}.title" => 'required',
            "{$pubMetaKey}.date" => 'required',
            "{$membersKey}" => 'required|array|min:1',
            "{$membersKey}.*.user_id" => 'required',
            "{$membersKey}.*.precedence" => 'required',
        ]);

        // process the request as a transaction; we are doing the transaction
        // within a try...catch so we can customize how the error response is
        // returned when it fails via an exception
        try
        {
            DB::beginTransaction();

            // grab the next auto-incrementing ID
            $nextId = 1;
            $latestCitation = Citation::orderBy('id', 'DESC')->first();
            if(!empty($latestCitation)) {
                $nextId = $latestCitation->id + 1;
            }

            // create the citation object before we start attaching stuff to it
            $citation = Citation::create([
                'citation_id' => "citations:{$nextId}",
                'citation_type' => $request->input('type'),
                'collaborators' => $request->input('collaborators'),
                'citation_text' => $request->input('citation_text'),
                'note' => $request->input('note'),
            ]);

            // create the metadata for the citation
            $citation->metadata()->create([
                'title' => $request->input("{$metaKey}.title"),
                'abstract' => $request->input("{$metaKey}.abstract"),
                'book_title' => $request->input("{$metaKey}.book_title"),
                'journal' => $request->input("{$metaKey}.journal"),
            ]);

            // create the published metadata
            $citation->publishedMetadata()->create([
                'how' => $request->input("{$pubMetaKey}.how"),
                'date' => $request->input("{$pubMetaKey}.date"),
            ]);

            // attach the set of associated individuals
            $people = [];
            $members = $request->input($membersKey);
            foreach($members as $member) {
                $people[$member['user_id']] = [
                    'role_position' => 'author',
                    'precedence' => $member['precedence'],
                ];
            }
            $citation->members()->attach($people);

            // create the document data if it exists
            if($request->filled($docKey)) {
                $citation->document()->create([
                    'doi' => $request->input("{$docKey}.doi"),
                    'handle' => $request->input("{$docKey}.handle"),
                    'url' => $request->input("{$docKey}.url"),
                ]);
            }

            // create the publisher data if it exists
            if($request->filled($pubKey)) {
                $citation->publisher()->create([
                    'institution' => $request->input("{$pubKey}.institution"),
                    'organization' => $request->input("{$pubKey}.organization"),
                    'publisher' => $request->input("{$pubKey}.publisher"),
                    'school' => $request->input("{$pubKey}.school"),
                    'address' => $request->input("{$pubKey}.address"),
                ]);
            }

            // create the collection data if it exists
            if($request->filled($collKey)) {
                $citation->collection()->create([
                    'edition' => $request->input("{$collKey}.edition"),
                    'series' => $request->input("{$collKey}.series"),
                    'number' => $request->input("{$collKey}.number"),
                    'volume' => $request->input("{$collKey}.volume"),
                    'chapter' => $request->input("{$collKey}.chapter"),
                    'pages' => $request->input("{$collKey}.pages"),
                ]);
            }

            DB::commit();
        }
        catch(\Exception $e) {
            DB::rollBack();
            Log::error('Could not create citation: ' . $e->getMessage());
            return generateErrorResponse(
                'The citation could not be created', 500, false
            );
        }

        // return the success response
        return generateMessageResponse('The citation has been added successfully');
    }

    /**
     * Checks that the request instance is a JSON request. Throws an exception
     * if the request is not a JSON request. Returns true otherwise.
     *
     * @param Request $request The request to check
     *
     * @return bool
     * @throws InvalidPayloadTypeException
     */
    protected function checkRequestTypeJson(Request $request) {
        if(!$request->isJson()) {
            throw new InvalidPayloadTypeException();
        }

        return true;
    }
}
