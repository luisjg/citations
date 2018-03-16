<?php

/**
 * Generates a response array based upon a given static message as well as an
 * optional response code and optional success value.
 *
 * @param string $message Some kind of static message
 * @param int $code Optional response code (defaults to 200)
 * @param bool $success Optional success value (defaults to true)
 *
 * @return array
 */
function generateMessageResponse($message, $code=200, $success=true) {
	return [
		"status" => "$code",
		"success" => ($success ? "true" : "false"),
		"api" => "citations",
		"version" => "1.0",
		"message" => $message
	];
}

/**
 * Generates a response array based upon a given error message as well as an
 * optional response code and optional success value.
 *
 * @param string $message Some kind of error message
 * @param int $code Optional response code (defaults to 404)
 * @param bool $success Optional success value (defaults to false)
 *
 * @return array
 */
function generateErrorResponse($message, $code=404, $success=false) {
	return generateMessageResponse($message, $code, $success);
}

/**
 * Generates a response array based upon a given collection type, collection
 * or model with data, an optional code, and an optional success value.
 *
 * @param string $collectionType Some kind of collection name (like "articles")
 * @param Collection|Model $data Some kind of data collection or individual model
 * @param int $code Optional response code (defaults to 200)
 * @param bool $success Optional success value (defaults to true)
 *
 * @return array
 */
function generateCollectionResponse($collectionType, $data, $code=200, $success=true) {
	$isCollection = is_a($data, 'Illuminate\Support\Collection');
	$arr = [
		"status" => "$code",
		"success" => ($success ? "true" : "false"),
		"api" => "citations",
		"version" => "1.0",
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
 * @return Citation
 */
function fixCitationAttributes(&$citation) {
	// update the precedence and faculty URLs if available
	foreach($citation['members'] as &$member) {
		$member['profile'] = (!empty($member['facultyUrl'])
			? $member['facultyUrl']['url'] : null);
		$member['precedence'] = "" . $member['pivot']['precedence'];

		// unset the pivot and facultyUrl object for the member
		unset($member['pivot']);
		unset($member['facultyUrl']);
	}

	$citation['membership'] = [
		'type' => 'public',
		'members' => $citation['members']
	];
	unset($citation['members']);

	return $citation;
}