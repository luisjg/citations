<?php

namespace App\Formatters\Generic;

use App\Models\Citation;
use App\Formatters\AbstractFormatter;
use App\Formatters\Generic\ThesisFormatter;

class GenericFormatter extends AbstractFormatter
{
	/**
	 * Array describing the set of citation types and their matching formatters
	 * that can be auto-selected.
	 *
	 * @var array
	 */
	protected $formatters = [
		'thesis' => 'App\Formatters\Generic\ThesisFormatter',
	];

	/**
	 * Constructs a new instance of GenericFormatter.
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
		$key = $this->citation->citation_type;
		if(array_key_exists($key, $this->formatters)) {
			$class = $this->formatters[$key];
			return new $class($this->citation)->format();
		}

		return "";
	}
}