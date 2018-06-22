<?php

namespace App\Exceptions;
use Exception;

class MissingRouteNameException extends Exception
{
	/**
	 * Constructs a new MissingRouteNameException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="") {
		parent::__construct($message);
	}
}