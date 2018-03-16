<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Citation;
use App\User;

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
        $this->validate($request, [
            'type' => 'required|in:article,book,chapter,thesis',
            'metadata.title' => 'required',
            'published_metadata.date' => 'required',
            'members.*.user_id' => 'required',
            'members.*.precedence' => 'required',
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
                'title' => $request->input('metadata.title'),
                'abstract' => $request->input('metadata.abstract'),
                'book_title' => $request->input('metadata.book_title'),
                'journal' => $request->input('metadata.journal'),
            ]);

            // create the published metadata
            $citation->publishedMetadata()->create([
                'how' => $request->input('published_metadata.how'),
                'date' => $request->input('published_metadata.date'),
            ]);

            // attach the set of associated individuals
            $people = [];
            $members = $request->input('members');
            foreach($members as $member) {
                $people[$member['user_id']] = [
                    'role_position' => 'author',
                    'precedence' => $member['precedence'],
                ];
            }
            $citation->members()->attach($people);

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
}
