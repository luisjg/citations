<?php

namespace App\Formatters\Generic;

use App\Models\Citation;
use App\Formatters\AbstractFormatter;

class ThesisFormatter extends AbstractFormatter
{
	/**
	 * Constructs a new instance of ThesisFormatter.
	 *
	 * @param App\Models\Citation $citation Citation instance
	 */
	public function __construct(Citation $citation) {
		parent::__construct($citation);
	}

	/**
	 * Documentation in parent class.
	 */
	public function format() : string {

	}
}