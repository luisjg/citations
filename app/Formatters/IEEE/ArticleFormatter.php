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
	 * @see App\Formatters\IEEEFormatter
	 */
	public function format() : string {
		$collaborators = $this->generateCollaboratorString();

		$formatted = "";
		return $formatted;
	}
}