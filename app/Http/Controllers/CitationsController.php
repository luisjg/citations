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
     * This is the key of the citation metadata sub-object that may appear
     * within a JSON request body.
     *
     * @var string
     */
    protected $metaKey = "metadata";

    /**
     * This is the key of the published metadata sub-object that may appear
     * within a JSON request body.
     *
     * @var string
     */
    protected $pubMetaKey = "published_metadata";

    /**
     * This is the key of the author membership sub-object that may appear
     * within a JSON request body.
     *
     * @var string
     */
    protected $membersKey = "members";

    /**
     * This is the key of the citation document sub-object that may appear
     * within a JSON request body.
     *
     * @var string
     */
    protected $docKey = "document";

    /**
     * This is the key of the citation publisher sub-object that may appear
     * within a JSON request body.
     *
     * @var string
     */
    protected $pubKey = "publisher";

    /**
     * This is the key of the citation collection sub-object that may appear
     * within a JSON request body.
     *
     * @var string
     */
    protected $collKey = "collection";

    /**
     * This is the set of all possible citation types that can be processed
     * in requests within this controller.
     *
     * @var array
     */
    protected $citationTypes = [
        'article',
        'book',
        'chapter',
        'thesis'
    ];

    /**
     * Returns the common base citation query that will be used for all other
     * controller methods here.
     *
     * @return Builder
     */
    protected function getBaseCitationQuery() {
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
     *
     * @throws InvalidPayloadTypeException
     */
    public function store(Request $request) {
        // ensure this is a JSON request
        $this->checkRequestTypeJson($request);

        // define the JSON sub-object keys
        $metaKey = $this->metaKey;
        $pubMetaKey = $this->pubMetaKey;
        $membersKey = $this->membersKey;
        $docKey = $this->docKey;
        $pubKey = $this->pubKey;
        $collKey = $this->collKey;

        // now we need to validate the minimum data in the payload
        $this->validate($request, [
            'type' => 'required|in:' . implode(',' $this->citationTypes),
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
            Log::error('Could not create citation: ' . $e->getMessage() .
                '\n' . $e->getTraceAsString());
            return generateErrorResponse(
                'The citation could not be created', 500, false
            );
        }

        // return the success response
        return generateMessageResponse('The citation has been added successfully');
    }

    /**
     * Processes a citation update request. The data is expected to be JSON.
     *
     * @param Request $request The request to process
     * @param int $id The ID of the citation to update
     *
     * @return Response
     *
     * @throws InvalidPayloadTypeException
     */
    public function update(Request $request, $id) {
        // ensure this is a JSON request
        $this->checkRequestTypeJson($request);

        $citation = Citation::wherePartialId($id)->firstOrFail();

        // generate a conditional set of validation rules and their matching
        // input based upon data that was received in the request body
        $arr = $this->generateUpdateValidationRulesAndInput($request);
        $rules = $arr['rules'];
        $input = $arr['input'];

        // perform validation if any rules have been established; otherwise,
        // nothing worthwhile was sent so trigger an exception
        if(!empty($rules)) {
            $this->validate($request, $rules);
        }
        else
        {
            // TODO: throw InvalidRequestException here (wait until DELETE pull request gets merged)
        }

        // we have to be incredibly careful with the JSON body since we don't
        // want to delete data accidentally by nature of it merely not being
        // present in the request; we also want to be able to load different
        // relationships conditionally so we're not loading everything if only
        // a few basic fields are being updated.
        try {
            DB::beginTransaction();

            // TODO: perform the modifications

            DB::commit();
        }
        catch(\Exception $e) {
            DB::rollBack();
            Log::error('Could not update citation: ' . $e->getMessage() .
                '\n' . $e->getTraceAsString());
            return generateErrorResponse(
                'The citation could not be updated', 500, false
            );
        }

        // return the success response
        return generateMessageResponse('The citation has been updated successfully');
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

    /**
     * Generates the set of validation rules and matching input for a citation
     * update request. Returns an associative array containing the rules and
     * the matching input.
     *
     * @param Request $request The request to check for data
     * @return array
     */
    protected function generateUpdateValidationRulesAndInput(Request $request) {
        $basicDataRule = "string|nullable"; // string type but can also be null

        $rules = [];
        $input = [];

        // ensure the type is within the acceptable set of citation types if
        // it has been provided
        if($request->has('type')) {
            $rules['type'] = 'in:' . implode(',', $this->citationTypes);
            $input['type'] = $request->input('type');
        }

        // these are the keys of the possible sub-objects in the request body
        $metaKey = $this->metaKey;
        $pubMetaKey = $this->pubMetaKey;
        $docKey = $this->docKey;
        $pubKey = $this->pubKey;
        $collKey = $this->collKey;

        // generate the rules iteratively using a multidimensional associative
        // array based upon the attributes of the sub-objects
        $possibleInput = [
            // basic citation data (attributes not in sub-objects)
            'collaborators',
            'citation_text',
            'note',
            // citation metadata
            $metaKey => [
                'title',
                'abstract',
                'book_title',
                'journal',
            ],
            // published metadata
            $pubMetaKey => [
                'how',
                'date',
            ],
            // citation collection
            $collKey => [
                'edition',
                'series',
                'number',
                'volume',
                'chapter',
                'pages',
            ],
            // citation document
            $docKey => [
                'doi',
                'handle',
                'url',
            ],
            // citation publisher
            $pubKey => [
                'institution',
                'organization',
                'publisher',
                'school',
                'address',
            ],
        ];
        foreach($possibleInput as $key => $value) {
            // only check the input attributes if the sub-object exists in the
            // request body
            if(is_array($value)) {
                if($request->has($key)) {
                    foreach($value as $attribute) {
                        if($request->has("{$key}.{$attribute}")) {
                            $rules["{$key}.{$attribute}"] = $basicDataRule;
                            $input["{$key}.{$attribute}"] = $request->input("{$key}.{$attribute}");
                        }
                    }
                }
            }
            else
            {
                // check for the attribute in the base object
                if($request->has($value)) {
                    $rules[$value] = $basicDataRule;
                    $input[$value] = $request->input($value);
                }
            }
        }

        return [
            'rules' => $rules,
            'input' => $input,
        ];
    }
}
