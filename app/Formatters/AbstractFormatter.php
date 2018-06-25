<?php

namespace App\Formatters;

use App\Citation;

abstract class AbstractFormatter
{
	/**
	 * Instance of a citation that will be formatted.
	 *
	 * @var App\Citation
	 */
	protected $citation;

	/**
	 * Constructs a new instance of AbstractFormatter.
	 *
	 * @param App\Models\Citation $citation The citation to format
	 */
	protected function __construct(Citation $citation) {
		$this->citation = $citation;
	}

	/**
	 * Returns a string representing the formatted citation entry. This can
	 * then be added to the returned JSON for easy consumption.
	 *
	 * @return string
	 */
	public abstract function format() : string;
}