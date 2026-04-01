<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $appUrl = rtrim((string) env('APP_URL', 'http://127.0.0.1:8001'), '/');
        $databasePath = (string) env(
            'DB_DATABASE',
            base_path('vendor/orchestra/testbench-core/laravel/database/database.sqlite')
        );

        config([
            'app.name' => 'Laravel CAS Workbench',
            'database.default' => env('DB_CONNECTION', 'sqlite'),
            'database.connections.sqlite.database' => $databasePath,
            'auth.guards.laravel-cas' => [
                'driver' => 'laravel-cas',
                'provider' => 'laravel-cas',
            ],
            'auth.providers.users.model' => User::class,
            'auth.providers.laravel-cas' => [
                'driver' => 'laravel-cas',
                'model' => User::class,
            ],
            'laravel-cas.base_url' => env('CAS_URL', 'http://127.0.0.1:9800/cas'),
            'laravel-cas.redirect_login_route' => env('CAS_REDIRECT_LOGIN_ROUTE', 'dashboard'),
            'laravel-cas.redirect_logout_url' => env('CAS_REDIRECT_LOGOUT_URL', $appUrl . '/'),
            'laravel-cas.demo_mode' => false,
            'laravel-cas.masquerade' => null,
            'laravel-cas.auto_load_routes' => true,
            'laravel-cas.auto_register_middleware' => true,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
