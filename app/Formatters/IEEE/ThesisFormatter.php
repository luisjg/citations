<?php

namespace App\Formatters\IEEE;

use App\Citation;
use App\Formatters\AbstractFormatter;

class ThesisFormatter extends AbstractFormatter
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
	 */
	public function format() : string {
		$author = $this->citation->members->filter(function($member) {
			return $member->pivot->role_position == 'author';
		})->first();

		$chair = $this->citation->members->filter(function($member) {
			return $member->pivot->role_position == 'chair';
		})->first();

		// if we do not have any author/chair information, just skip the formatting
		// in order to prevent a null issue
		if(empty($author) || empty($chair)) {
			return "";
		}

		// resolve the relevant strings for the formatted citation
		$title = $this->citation->metadata->title;
		$date = $this->citation->publishedMetadata->date;
		$authorName = "{$author->first_name[0]}. {$author->last_name}";
		$chairName = "{$chair->last_name}, {$chair->first_name}";

		$formatted = "{$authorName}, \"{$title}\", {$date}.";
		return $formatted;
	}
}