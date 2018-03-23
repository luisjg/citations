<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Citation;
use App\User;

use App\Exceptions\InvalidPayloadTypeException;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\NoDataException;

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
     * Deletes either a single citation or deletes all citations for a given
     * email address. Throws an exception if both the citation ID and the email
     * query parameter are empty or both are filled. Throws another exception
     * if there are no citations to delete.
     *
     * @param Request $request The request to check for an email address
     * @param int $id The optional ID of the citation that will be deleted
     *
     * @return Response
     * @throws InvalidRequestException
     * @throws NoDataException
     */
    public function destroy(Request $request, $id=null) {
        if(empty($id) && !$request->filled('email') && !$request->filled('citations')) {
            // the route was accessed with no parameter, no email, and directly
            // via the DELETE method so throw an exception
            throw new InvalidRequestException(
                "Please specify either a citation ID, an email address, or an array of citation IDs."
            );
        }
        else if(!empty($id) && $request->filled('email')) {
            // specifying BOTH an ID and an email address is also a problem
            // since the idea behind the method is not to handle both cases
            // at the same time since it could result in a confusing response
            // to the consuming client
            throw new InvalidRequestException(
                "You may only specify either a citation ID or an email address, not both."
            );
        }
        else if($request->filled('citations')) {
            // we got citation IDs in the DELETE body, so let's validate it first
            $this->checkRequestTypeJson($request);
            $this->validate($request, [
                'citations' => 'array'
            ]);
        }

        // PK column that represents the textual IDs of the citations
        $citationPK = "citation_id";

        // now that our sanity checks are done we can process the actual request
        // by retrieving the citation (or set of citations) in a specific way
        if(!empty($id)) {
            $citations = Citation::wherePartialId($id);
        }
        else
        {
            // set of citations by user email or set of citations based on the
            // IDs of the citations in the request body
            if(!empty($email)) {
                $email = $request->input('email');
                $user = User::where('email', $email)->first();
                if(empty($user)) {
                    throw new NoDataException(
                        "The individual with that email address does not exist."
                    );
                }

                // resolve the collection based on the ID of the user
                $citations = Citation::whereHas('members', function($q) use ($user) {
                    return $q->whereMembersId($user->user_id);
                });
            }
            else if($request->filled('citations')) {
                // prepend the collection to all of the IDs
                $ids = array_map(function($v) {
                    return "citations:{$v}";
                },
                $request->input('citations'));

                // resolve the collection based on the IDs
                $citations = Citation::whereIn($citationPK, $ids);
            }
            else
            {
                // we received nothing to work with, so treat it as a bad request
                throw new InvalidRequestException(
                    "Please specify either a citation ID, an email address, or an array of citation IDs."
                );
            }
        }

        // the get() is intentional even for a single instance because we want
        // to resolve a Collection so we can use pluck()
        $citationIds = $citations->get()->pluck('citation_id')->toArray();

        // make sure we have citations
        if(empty($citationIds)) {
            throw new NoDataException(
                "No matching citation(s) to delete."
            );
        }

        // we're going to delete the citations in a specific way to prevent the
        // need to do a separate database call per citation in the case that we
        // are destroying a set of citations; we will have the same number of
        // database calls regardless of the number of citations being destroyed
        try {
            DB::beginTransaction();

            // delete the various related data in the reverse order of their creation;
            // we associate the models with their PK that stores the citation ID
            $ns = "App\\"; // model namespace
            $models = [
                'Collection' => $citationPK,
                'Publisher' => $citationPK,
                'Document' => $citationPK,
                'CitationMember' => 'parent_entities_id',
                'PublishedMetadata' => $citationPK,
                'CitationMetadata' => $citationPK,
                'Citation' => $citationPK,
            ];

            // generate the full model namespace for each model and then delete
            // all matching data based on the set of citation IDs
            foreach($models as $modelName => $pk) {
                $model = "{$ns}{$modelName}";
                $model::whereIn($pk, $citationIds)->delete();
            }
          
            DB::commit();
        }
        catch(\Exception $e) {
            DB::rollBack();
            Log::error('Could not delete citation(s): [' .
                implode(",", $citationIds) . "]. " . $e->getMessage());
            return generateErrorResponse('The citation(s) could not be deleted', 500);
        }

        // return the success response
        return generateMessageResponse(
            count($citationIds) . " citation(s) were deleted successfully!"
        );
    }
            
    /*
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
            throw new InvalidPayloadTypeException(
                "Please ensure your Content-Type header is set to application/json."
            );
        }

        return true;
    }
}
