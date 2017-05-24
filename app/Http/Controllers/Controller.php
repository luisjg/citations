<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;


class Controller extends BaseController
{ /**
	 * Sends the JSON response using the specified data array as well as the
	 * desired HTTP response code.
	 *
	 * @param array $data An array of data to return
	 * @param integer $code The HTTP response code to return
	 * @param boolean $success Whether the request was successful
	 *
	 * @return Response
	 */
	/*public function sendResponse($data, $code=200, $success=true) {
		// log the request
		HandlerUtilities::logResponse($data, $code, $success);

		// return the response
		return response($data, $code);
	}*/
}