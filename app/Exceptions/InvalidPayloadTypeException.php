<?php

namespace App\Exceptions;
use Exception;

class InvalidPayloadTypeException extends Exception
{
	/**
	 * Constructs a new InvalidPayloadTypeException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="This only accepts payloads as JSON.") {
		parent::__construct($message);
	}
}