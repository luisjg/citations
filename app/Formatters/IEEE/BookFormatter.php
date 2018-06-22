<?php

namespace App\Formatters\IEEE;

use App\Citation;
use App\Formatters\IEEE\IEEEFormatter;

class BookFormatter extends IEEEFormatter
{
	/**
	 * Constructs a new instance of BookFormatter.
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
		$collaborators = $this->generateCollaboratorString();

		// grab the relevant metadata
		$title = trim($this->citation->metadata->title);
		$date = $this->getFormattedDate(true); // we only want the year

		// we may not always have collection information based upon the
		// source of the citation data
		if(!empty($this->citation->publisher)) {
			$publisher = $this->citation->publisher->publisher;
		}
		else
		{
			$publisher = "";
		}
		if(!empty($this->citation->collection)) {
			$pages = $this->citation->collection->pages;
		}
		else
		{
			$pages = "";
		}

		$formatted = "{$collaborators}, <em>{$title}</em>. ";

		// we may not have all data so we have to do the collection data
		// conditionally
		if(!empty($publisher)) {
			$formatted .= "{$publisher}, ";
		}

		// we may have to mess with the format of both the date and the pages
		// referenced if we have them
		$formatted .= "{$date}";
		if(!empty($pages)) {
			// we have pages, so add them (but don't end the citation)
			$formatted .= ", p. {$pages}";
		}

		// end the formatted citation text
		$formatted .= ".";

		return $formatted;
	}
}