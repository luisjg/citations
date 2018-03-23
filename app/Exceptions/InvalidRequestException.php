<?php

namespace App\Exceptions;
use Exception;

class InvalidRequestException extends Exception
{
	/**
	 * Constructs a new InvalidRequestException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="") {
		parent::__construct($message);
	}
}