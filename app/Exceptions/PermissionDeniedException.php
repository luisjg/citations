<?php

namespace App\Exceptions;
use Exception;

class PermissionDeniedException extends Exception
{
	/**
	 * Constructs a new PermissionDeniedException instance.
	 *
	 * @param string $message Optional message for the exception
	 */
	public function __construct($message="") {
		parent::__construct($message);
	}
}