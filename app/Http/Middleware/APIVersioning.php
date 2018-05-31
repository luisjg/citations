<?php

namespace App\Http\Middleware;

use Closure;

class APIVersioning
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // attempt to figure out the version of the API based upon the structure
        // of the request path
        $first_part = $request->segment(1);
        $version = (is_numeric($first_part) ? $first_part : '1.0');

        // apply the X-API-VERSION header that can then be checked later on
        // in the helper functions in helpers.php
        $request->headers->add([
            'X-API-VERSION' => $version,
        ]);

        return $next($request);
    }
}
