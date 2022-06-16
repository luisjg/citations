<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\InvalidPayloadTypeException;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\MissingApiKeyException;
use App\Exceptions\MissingRouteNameException;
use App\Exceptions\NoDataException;
use App\Exceptions\PermissionDeniedException;

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
        InvalidPayloadTypeException::class,
        InvalidRequestException::class,
        NoDataException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if($e instanceof ModelNotFoundException) {
            return response(generateErrorResponse(
                $request,
                "The specified resource could not be found"
            ), 404);
        }
        else if($e instanceof NotFoundHttpException) {
            return response(generateErrorResponse(
                $request,
                "That URL does not exist"
            ), 404);
        }
        else if($e instanceof HttpException) {
            return response(generateErrorResponse(
                $request,
                "An unknown error has occurred",
                500
            ), 500);
        }
        else if($e instanceof InvalidPayloadTypeException ||
            $e instanceof InvalidRequestException) {
            return response(generateErrorResponse(
                $request,
                $e->getMessage(),
                400
            ), 400);
        }
        else if($e instanceof NoDataException) {
            return response(generateErrorResponse(
                $request,
                $e->getMessage(),
                404
            ), 404);
        }
        else if($e instanceof InvalidApiKeyException) {
            return response(generateErrorResponse(
                $request,
                'You must supply a valid active API key for that action',
                400
            ), 400);
        }
        else if($e instanceof MissingApiKeyException) {
            return response(generateErrorResponse(
                $request,
                'The X-API-Key header was missing from your request',
                400
            ), 400);
        }
        else if($e instanceof MissingRouteNameException) {
            return response(generateErrorResponse(
                $request,
                'Unable to resolve the permission associated with that action',
                500
            ), 500);
        }
        else if($e instanceof PermissionDeniedException) {
            return response(generateErrorResponse(
                $request,
                'You are not authorized to perform that action',
                403
            ), 403);
        }

        return parent::render($request, $e);
    }
}
