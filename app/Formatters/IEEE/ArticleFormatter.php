<?php

namespace App\Formatters\IEEE;

use App\Citation;
use App\Formatters\IEEE\IEEEFormatter;

class ArticleFormatter extends IEEEFormatter
{
	/**
	 * Constructs a new instance of ArticleFormatter.
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
		$publication = $this->citation->metadata->journal;
		$date = $this->getFormattedDate();

		// we may not always have collection information based upon the
		// source of the citation data
		if(!empty($this->citation->collection)) {
			$volume = $this->citation->collection->volume;
			$number = $this->citation->collection->number;
			$pages = $this->citation->collection->pages;
		}
		else
		{
			$volume = "";
			$number = "";
			$pages = "";
		}

		$formatted = "{$collaborators}, \"{$title}\", ";

		// we may not have all data so we have to do the collection data
		// conditionally
		if(!empty($publication)) {
			$formatted .= "<em>{$publication}</em>, ";
		}
		if(!empty($volume)) {
			$formatted .= "v. {$volume}, ";
		}
		if(!empty($number)) {
			$formatted .= "no. {$number}, ";
		}
		if(!empty($pages)) {
			$formatted .= "pp. {$pages}, ";
		}

		// the date should always be there so we can reliably end the citation
		// at that point
		$formatted .= "{$date}.";

		return $formatted;
	}
}