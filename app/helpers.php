<?php

use App\Formatters\IEEE\IEEEFormatter;

/**
 * Generates a response array based upon a given static message as well as an
 * optional response code and optional success value.
 *
 * @param Request $request The request to which we are responding
 * @param string $message Some kind of static message
 * @param int $code Optional response code (defaults to 200)
 * @param bool $success Optional success value (defaults to true)
 *
 * @return array
 */
function generateMessageResponse($request, $message, $code=200, $success=true) {
	return [
		"status" => "$code",
		"success" => ($success ? "true" : "false"),
		"api" => "citations",
		"version" => $request->headers->get('X-API-VERSION', '1.0'),
		"message" => $message
	];
}

/**
 * Generates a response array based upon a given error message as well as an
 * optional response code and optional success value.
 *
 * @param Request $request The request to which we are responding
 * @param string $message Some kind of error message
 * @param int $code Optional response code (defaults to 404)
 * @param bool $success Optional success value (defaults to false)
 *
 * @return array
 */
function generateErrorResponse($request, $message, $code=404, $success=false) {
	return generateMessageResponse($request, $message, $code, $success);
}

/**
 * Generates a response array based upon a given collection type, collection
 * or model with data, an optional code, and an optional success value.
 *
 * @param Request $request The request to which we are responding
 * @param string $collectionType Some kind of collection name (like "articles")
 * @param Collection|Model $data Some kind of data collection or individual model
 * @param int $code Optional response code (defaults to 200)
 * @param bool $success Optional success value (defaults to true)
 *
 * @return array
 */
function generateCollectionResponse($request, $collectionType, $data, $code=200, $success=true) {
	$isCollection = is_a($data, 'Illuminate\Support\Collection');

	$arr = [
		"status" => "$code",
		"success" => ($success ? "true" : "false"),
		"api" => "citations",
		"version" => $request->headers->get('X-API-VERSION', '1.0'),
		"collection" => $collectionType,
		"count" => "" . ($isCollection ? $data->count() : 1),
		$collectionType => $data,
	];

	// perform some array surgery if we have a collection; otherwise
	// we will just work on a single instance
	if($isCollection) {
		foreach($arr[$collectionType] as &$citation) {
			$citation = fixCitationAttributes($citation);
		}
	}
	else
	{
		// fix up the individual model
		$arr[$collectionType] = fixCitationAttributes($arr[$collectionType]);
	}

	return $arr;
}

/**
 * Performs some array surgery on a single citation record. Specifically, we
 * are moving data around and removing unnecessary data from the citation.
 *
 * @param $citation Citation a single Citation instance
 * @param string $format Optional formatting style for the citation
 *
 * @return Citation
 */
function fixCitationAttributes(&$citation, $format="ieee") {
	// format the citation based upon its type and add a "formatted" key to
	// the resultant JSON object if a formatter can be resolved
	$formatters = [
		'ieee' => 'App\Formatters\IEEE\IEEEFormatter',
	];
	$citation['formatted'] = "";
	if(array_key_exists($format, $formatters)) {
		$class = $formatters[$format];
		$citation['formatted'] = (new $class($citation))->format();
	}

	// NOTE: Addition of the "formatted" attribute needs to be done prior to
	// the removal and setting of additional attributes below; it was reloading
	// the unset relationships since it needed to load them when performing
	// the formatting steps. That resulted in unnecessary database calls and
	// a slow-down of the web service.

	// update the precedence and faculty URLs if available
	foreach($citation['members'] as &$member) {
		$member['profile'] = (!empty($member['facultyUrl'])
			? $member['facultyUrl']['url'] : null);
		$member['precedence'] = "" . $member['pivot']['precedence'];
		$member['role'] = $member['pivot']['role_position'];

		// unset the pivot and facultyUrl object for the member
		unset($member['pivot']);
		unset($member['facultyUrl']);
	}

	$citation['membership'] = [
		'type' => 'public',
		'members' => $citation['members']
	];
	unset($citation['members']);

	// transform the published_metadata attribute into "published"
	$citation['published'] = $citation['publishedMetadata'];
	unset($citation['publishedMetadata']);

	// turn the wasPublished boolean attribute into a string
	$citation['is_published'] = ($citation['wasPublished'] ? "true" : "false");

	return $citation;
}

/**
 * Logs an error that resulted in an exception being raised.
 *
 * @param string $message A descriptive (non-exception) error message
 * @param Exception $e The exception that was raised
 */
function logErrorException($message, Exception $e) {
	Log::error($message . " " . $e->getMessage() .
        '\n' . $e->getTraceAsString());
}