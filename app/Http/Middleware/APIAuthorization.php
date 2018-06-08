<?php

namespace App\Http\Middleware;

use Closure;
use App\ApiKey;

use App\Exceptions\InvalidApiKeyException;
use App\Exceptions\MissingApiKeyException;
use App\Exceptions\MissingRouteNameException;
use App\Exceptions\PermissionDeniedException;

class APIAuthorization
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
        // resolve the current route and its name since we will be using that
        // for authorization and permission checks
        $route = $request->route();
        $routeConf = (!empty($route[1]) ? $route[1] : null);
        $routeName = '';
        if(!empty($routeConf)) {
            $routeName = (!empty($routeConf['as']) ? $routeConf['as'] : '');
        }

        // if we have a route name, we can check permissions based upon the
        // given X-API-Key request header
        if(!empty($routeName)) {
            $keyValue = $request->headers->get('X-API-Key');

            // an API key is NOT required for public-facing (*.index, *.show)
            // routes since they're public information; everything else needs
            // to be checked
            if(!empty($keyValue)) {
                // load up the key and ensure it exists
                $key = ApiKey::with('scopes.permissions')
                    ->whereKey($keyValue)
                    ->whereActive()
                    ->first();

                // if we have a valid key, then check the scopes/permissions;
                // otherwise, render a JSON error response
                if(!empty($key)) {
                    // if we have a scope with the proper permission, we are
                    // good; otherwise, throw an exception
                    if($key->hasPermission($routeName)) {
                        return $next($request);
                    }

                    // API key not authorized to perform that action
                    throw new PermissionDeniedException();
                }

                // the supplied header value does not match a valid API key
                throw new InvalidApiKeyException();
            }
            else
            {
                // if the route name ends with .index or .show then we can still
                // proceed without an API key; otherwise we NEED an API key
                if(ends_with($routeName, '.index') || ends_with($routeName, '.show')) {
                    return $next($request);
                }

                // the API key was missing from the request
                throw new MissingApiKeyException();
            }
        }

        // the request is NOT authorized to proceed (unnamed route)
        throw new MissingRouteNameException();
    }
}
