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
		if(!empty($message)) {
			// add a leading space if there is something in the parameter
			$message = " {$message}";
		}
		parent::__construct("Request incorrectly formed." . $message);
	}
}