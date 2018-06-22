<?php

namespace App\Formatters\IEEE;

use App\Citation;
use App\Formatters\AbstractFormatter;

use Carbon\Carbon;

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
	protected function generateCollaboratorArray() : array {
		// authorship of articles becomes interesting when taking Scopus data
		// into account since our information isn't complete based upon our
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
	protected function generateCollaboratorString() : string {
		$collaboratorArr = $this->generateCollaboratorArray();

		// the "and" as well as the position of the final comma is going to
		// be defined based upon how many collaborators exist
		//
		// ex: A. Win
		// ex: A. Win and B. Twin
		// ex. A. Win, B. Twin, and C. Fin
		if(count($collaboratorArr) > 1) {
			$lastCollaborator = array_pop($collaboratorArr);
			$collaborators = implode(", ", $collaboratorArr);

			// if we still have more than one collaborator left, ensure that
			// the string ends with a comma before the "and" token
			if(count($collaboratorArr) > 1) {
				$collaborators .= ', ';
			}

			$collaborators .= " and {$lastCollaborator}";
		}
		else
		{
			$collaborators = array_shift($collaboratorArr);
		}

		return $collaborators;
	}

	/**
	 * Returns the formatted date that appears at the end of the citation string.
	 * This will result in either a single year or something like "Dec. 2017".
	 *
	 * @param bool $yearOnly Whether to return only the year regardless of format
	 * @return string
	 */
	protected function getFormattedDate($yearOnly=false) : string {
		// the date is typically represented as either a single year of with the
		// YYYY-MM-DD format, so we can split and check
		$parts = explode('-', $this->citation->publishedMetadata->date);
		if(count($parts) == 1) {
			// single year, so just return that
			return $parts[0];
		}

		// multiple parts, so let's run it through Carbon
		$date = Carbon::createFromFormat('Y-m-d', implode('-', $parts));
		if($yearOnly) {
			return $date->format('Y');
		}
		return $date->format('M. Y');
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