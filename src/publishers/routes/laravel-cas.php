<?php

/**
 * Laravel CAS Authentication Routes
 * 
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

use EcDoris\LaravelCas\Controllers\HomepageController;
use EcDoris\LaravelCas\Controllers\LoginController;
use EcDoris\LaravelCas\Controllers\LogoutController;
use EcDoris\LaravelCas\Controllers\ProxyCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laravel CAS Routes
|--------------------------------------------------------------------------
|
| These routes handle CAS authentication for your application. You can
| customize these routes as needed. The routes are automatically 
| registered with the 'web' middleware group.
|
*/

Route::middleware(['web'])->group(function () {
    // CAS Homepage - Default redirect after successful login
    Route::get('/homepage', HomepageController::class)
        ->name('laravel-cas-homepage');

    // CAS Login - Initiates CAS authentication
    Route::get('/login', LoginController::class)
        ->name('laravel-cas-login');

    // CAS Logout - Handles CAS logout
    Route::get('/logout', LogoutController::class)
        ->name('laravel-cas-logout');

    // CAS Proxy Callback - For proxy ticket validation
    Route::get('/proxy/callback', ProxyCallbackController::class)
        ->name('laravel-cas-proxy-callback');
});

/*
|--------------------------------------------------------------------------
| Protected Routes Example
|--------------------------------------------------------------------------
|
| You can use the 'cas.auth' middleware to protect routes that require
| CAS authentication. Uncomment and modify as needed:
|
| Route::middleware(['web', 'cas.auth'])->group(function () {
|     Route::get('/dashboard', function () {
|         return view('dashboard');
|     })->name('dashboard');
|     
|     Route::get('/profile', function () {
|         $user = auth('laravel-cas')->user();
|         return view('profile', compact('user'));
|     })->name('profile');
| });
|
*/