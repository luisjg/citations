<?php

namespace App\Formatters\IEEE;

use App\Citation;
use App\Formatters\AbstractFormatter;

class IEEEFormatter extends AbstractFormatter
{
	/**
	 * Array describing the set of citation types and their matching formatters
	 * that can be auto-selected.
	 *
	 * @var array
	 */
	const FORMATTERS = [
		'article' => 'App\Formatters\IEEE\ArticleFormatter',
		'book' => 'App\Formatters\IEEE\BookFormatter',
		'chapter' => 'App\Formatters\IEEE\ChapterFormatter',
		'thesis' => 'App\Formatters\IEEE\ThesisFormatter',
	];

	/**
	 * Constructs a new instance of GenericFormatter.
	 *
	 * @param App\Citation $citation Citation instance
	 */
	public function __construct(Citation $citation) {
		parent::__construct($citation);
	}

	/**
	 * Generates an array representing the set of collaborators for the
	 * citation attached to this format.
	 *
	 * @return array
	 */
	protected function generateCollaboratorArray() {
		// authorship of articles becomes interesting when taking Scopus data
		// into account since our information is complete based upon our
		// abilities within its API
		$isScopus = !empty($this->citation->scopus_id);
		$authorArr = [];

		if($isScopus) {
			// reverse the Scopus format to place it in the IEEE format
			$mainAuthor = array_reverse(explode(" ", $this->citation->collaborators));
			$authorArr[] = implode(" ", $mainAuthor);
		}

		// now we need to look at the actual collaborator memberships; the
		// members have already been ordered by precdence
		foreach($this->citation->members as $member) {
			$authorArr[] = "{$member->first_name[0]}. {$member->last_name}";
		}

		// now ensure we only have unique member names (this will cancel out any
		// issues caused by a dual Scopus collaborator/membership combination)
		return array_unique($authorArr);
	}

	/**
	 * Generates a string representing the set of collaborators for the citation
	 * attached to this format. This method calls generateCollaboratorArray()
	 * first and then works on the array to construct the string.
	 *
	 * @return string
	 */
	protected function generateCollaboratorString() {
		$collaboratorArr = $this->generateCollaboratorArray();

		$collaborators = "";
		return $collaborators;
	}

	/**
	 * Documentation in parent class.
	 *
	 * @see App\Formatters\AbstractFormatter
	 */
	public function format() : string {
		$key = $this->citation->citation_type;
		if(array_key_exists($key, self::FORMATTERS)) {
			$class = self::FORMATTERS[$key];
			return (new $class($this->citation))->format();
		}

		return "";
	}
}