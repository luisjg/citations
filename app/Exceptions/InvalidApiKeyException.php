<?php

namespace App\Exceptions;
use Exception;

class InvalidApiKeyException extends Exception
{
	/**
	 * Constructs a new InvalidApiKeyException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="") {
		parent::__construct($message);
	}
}