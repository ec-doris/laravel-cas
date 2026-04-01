<?php

/**
 * Example web.php inclusion for Laravel CAS routes
 * 
 * Add this to your routes/web.php file to include CAS routes
 */

// Include Laravel CAS routes
require __DIR__ . '/laravel-cas.php';

/*
|--------------------------------------------------------------------------
| Your Application Routes
|--------------------------------------------------------------------------
|
| Continue with your regular application routes below this line.
| The CAS routes are now available and will be picked up by frontend
| tools like Ziggy.
|
| Important: keep EcDoris\LaravelCas\Middleware\CasAuthenticator out of the
| global web middleware group. Use the named `cas.auth` middleware only on
| the routes that should require CAS, or redirect Laravel guests to
| route('laravel-cas-login').
|
*/
