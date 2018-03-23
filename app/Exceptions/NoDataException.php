<?php

namespace App\Exceptions;
use Exception;

class NoDataException extends Exception
{
	/**
	 * Constructs a new NoDataException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="") {
		if(!empty($message)) {
			// add a leading space if there is something in the parameter
			$message = " {$message}";
		}
		parent::__construct("No data resolved." . $message);
	}
}