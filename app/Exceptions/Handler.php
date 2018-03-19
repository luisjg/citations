<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Exceptions\InvalidPayloadTypeException;
use App\Exceptions\InvalidRequestException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if($e instanceof ModelNotFoundException) {
            return response(generateErrorResponse(
                "The specified resource could not be found"
            ), 404);
        }
        else if($e instanceof NotFoundHttpException) {
            return response(generateErrorResponse(
                "That URL does not exist"
            ), 404);
        }
        else if($e instanceof HttpException) {
            return response(generateErrorResponse(
                "An unknown error has occurred",
                500
            ), 500);
        }
        else if($e instanceof InvalidPayloadTypeException ||
            $e instanceof InvalidRequestException) {
            return response(generateErrorResponse(
                $e->getMessage(),
                400
            ), 400);
        }

        return parent::render($request, $e);
    }
}
