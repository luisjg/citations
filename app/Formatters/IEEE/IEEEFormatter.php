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
	 * Documentation in parent class.
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