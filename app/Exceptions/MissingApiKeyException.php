<?php

namespace App\Exceptions;
use Exception;

class MissingApiKeyException extends Exception
{
	/**
	 * Constructs a new MissingApiKeyException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="") {
		parent::__construct($message);
	}
}