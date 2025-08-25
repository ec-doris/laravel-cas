<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

use EcDoris\LaravelCas\Controllers\HomepageController as Homepage;
use EcDoris\LaravelCas\Controllers\LoginController as Login;
use EcDoris\LaravelCas\Controllers\LogoutController as Logout;
use EcDoris\LaravelCas\Controllers\ProxyCallbackController as ProxyCallback;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], static function () {
    Route::get('/homepage', Homepage::class)->name('laravel-cas-homepage');
    Route::get('/login', Login::class)->name('laravel-cas-login');
    Route::get('/logout', Logout::class)->name('laravel-cas-logout');
    Route::get('/proxy/callback', ProxyCallback::class)->name('laravel-cas-proxy-callback');
});
