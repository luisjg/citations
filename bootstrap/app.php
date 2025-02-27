<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
   CSUNMetaLab\LumenForceHttps\Http\Middleware\ForceHttps::class,
   App\Http\Middleware\APIVersioning::class,
   \Fruitcake\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'api_auth' => App\Http\Middleware\APIAuthorization::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->configure('proxypass');
$app->register(CSUNMetaLab\LumenProxyPass\Providers\ProxyPassServiceProvider::class);
$app->configure('forcehttps');
$app->register(CSUNMetaLab\LumenForceHttps\Providers\ForceHttpsServiceProvider::class);
$app->configure('guzzle');
$app->configure('scopus');
$app->configure('cors');
$app->register(Illuminate\Database\Eloquent\LegacyFactoryServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

// v1.0
$app->router->group([
    'namespace' => 'App\Http\Controllers',
    'prefix' => '1.0',
    'middleware' => ['api_auth', 'cors'],
], function ($router) {
    require __DIR__.'/../routes/1.0.php';
});

// v1.1
$app->router->group([
    'namespace' => 'App\Http\Controllers',
    'prefix' => '1.1',
    'middleware' => ['api_auth', 'cors'],
], function ($router) {
    require __DIR__.'/../routes/1.1.php';
});

return $app;
