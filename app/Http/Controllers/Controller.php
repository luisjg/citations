<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Handlers\HandlerUtilities;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
	 * Sends the JSON response using the specified data array as well as the
	 * desired HTTP response code.
	 *
	 * @param array $data An array of data to return
	 * @param integer $code The HTTP response code to return
	 * @param boolean $success Whether the request was successful
	 *
	 * @return Response
	 */
	public function sendResponse($data, $code=200, $success=true) {
		// log the request
		HandlerUtilities::logResponse($data, $code, $success);

		// return the response
		return response($data, $code);
	}
}