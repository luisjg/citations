<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    $email = 'adele.gottfried@csun.edu';
    if (env('APP_ENV') !== 'production') {
        $email = 'nr_'.$email;
    }
    return view('home', compact('email'));
});

$router->get('/about/version-history', function () {
    return view('pages.about.version-history');
});