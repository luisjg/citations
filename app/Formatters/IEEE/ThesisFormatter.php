<?php

namespace App\Formatters\IEEE;

use App\Citation;
use App\Formatters\IEEE\IEEEFormatter;

/**
 * This class formats thesis-specific citation instances.
 *
 * @see http://www.citethisforme.com/guides/ieee/how-to-cite-a-dissertation
 */
class ThesisFormatter extends IEEEFormatter
{
	/**
	 * Constructs a new instance of ThesisFormatter.
	 *
	 * @param App\Citation $citation Citation instance
	 */
	public function __construct(Citation $citation) {
		parent::__construct($citation);
	}

	/**
	 * Documentation in parent class.
	 *
	 * @see App\Formatters\IEEE\IEEEFormatter
	 */
	public function format() : string {
		$author = $this->citation->members->filter(function($member) {
			return $member->pivot->role_position == 'author';
		})->first();

		// if we do not have any author information, just skip the formatting
		// in order to prevent a null issue
		if(empty($author)) {
			return "";
		}

		// resolve the relevant strings for the formatted citation
		$title = $this->citation->metadata->title;
		$date = $this->getFormattedDate();
		$authorName = "{$author->first_name[0]}. {$author->last_name}";

		$formatted = "{$authorName}, \"{$title}\", {$date}.";
		return $formatted;
	}
}