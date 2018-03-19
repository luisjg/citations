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
        if(empty($id) && !$request->filled('email')) {
            // the route was accessed with no parameter, no email, and directly
            // via the DELETE method so throw an exception
            throw new InvalidRequestException(
                "Please specify either a citation ID or an email address."
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

        // now that our sanity checks are done we can process the actual request
        // by retrieving the citation (or set of citations) in a specific way
        $citationIds = [];
        if(!empty($id)) {
            $citation = Citation::wherePartialId($id)->first();
            $citationIds[] = $citation->citation_id;
        }
        else
        {
            // set of citations by user email
            $email = $request->input('email');
            $user = User::where('email', $email)->first();
            if(empty($user)) {
                throw new InvalidRequestException(
                    "The individual with that email address does not exist."
                );
            }

            // resolve the collection based on the ID of the user
            $citation = Citation::whereHas('members', function($q) use ($user) {
                return $q->where('individuals_id', $user->user_id);
            })->get();

            $citationIds = $citation->pluck('citation_id');
        }

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
            $citationPK = "citation_id";
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

    /**
     * Checks that the request instance is a JSON request. Throws an exception
     * if the request is not a JSON request. Returns true otherwise.
     *
     * @param Request $request The request to check for an email address
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
