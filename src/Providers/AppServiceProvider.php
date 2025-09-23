<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

namespace EcDoris\LaravelCas\Providers;

use EcPhp\CasLib\Cas;
use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Configuration\PropertiesInterface;
use EcPhp\CasLib\Contract\Response\CasResponseBuilderInterface;
use EcPhp\CasLib\Contract\Response\Factory\AuthenticationFailureFactory as AuthenticationFailureFactoryInterface;
use EcPhp\CasLib\Contract\Response\Factory\ProxyFactory as ProxyFactoryInterface;
use EcPhp\CasLib\Contract\Response\Factory\ProxyFailureFactory as ProxyFailureFactoryInterface;
use EcPhp\CasLib\Contract\Response\Factory\ServiceValidateFactory as ServiceValidateFactoryInterface;
use EcPhp\CasLib\Response\CasResponseBuilder;
use EcPhp\CasLib\Response\Factory\AuthenticationFailureFactory;
use EcPhp\CasLib\Response\Factory\ProxyFactory;
use EcPhp\CasLib\Response\Factory\ProxyFailureFactory;
use EcPhp\CasLib\Response\Factory\ServiceValidateFactory;
use EcDoris\LaravelCas\Auth\CasGuard;
use EcDoris\LaravelCas\Auth\CasUserProvider;
use EcDoris\LaravelCas\Config\Laravel;
use EcDoris\LaravelCas\Console\InstallCasCommand;
use EcDoris\LaravelCas\Middleware\CasAuthenticator;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use loophp\psr17\Psr17;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

use function class_exists;
use function dirname;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish configuration files (optional for customization)
        $this->publishes(
            [dirname(__DIR__) . '/publishers/config' => config_path()],
            'laravel-cas-config'
        );

        // Publish routes files
        $this->publishes(
            [dirname(__DIR__) . '/publishers/routes' => base_path('routes')],
            'laravel-cas-routes'
        );

        // Publish everything at once
        $this->publishes(
            [
                dirname(__DIR__) . '/publishers/config' => config_path(),
                dirname(__DIR__) . '/publishers/routes' => base_path('routes'),
            ],
            'laravel-cas'
        );

        // Load routes (with fallback for when not published)
        $this->loadRoutes();

        // Auto-register auth provider and guard
        $this->registerAuthComponents();

        // Auto-register middleware
        $this->registerMiddleware();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCasCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Merge default configuration
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/publishers/config/laravel-cas.php',
            'laravel-cas'
        );

        // Auto-register PSR dependencies
        $this->registerPsrDependencies();

        // Register CAS-related services
        $this->registerCasServices();
    }

    /**
     * Load package routes with fallback support
     */
    protected function loadRoutes(): void
    {
        // Check if routes have been published to the app
        $publishedRoutesPath = base_path('routes/laravel-cas.php');
        
        if (file_exists($publishedRoutesPath)) {
            // Routes have been published - they should be included in web.php
            // We don't auto-load them here to avoid duplicate registration
            return;
        }

        // Fallback: Auto-load routes if not published (for backward compatibility)
        if (config('laravel-cas.auto_load_routes', true)) {
            $router = $this->app['router'];
            
            $router->group(
                ['namespace' => 'EcDoris\LaravelCas\Controllers'],
                static fn () => require dirname(__DIR__) . '/Config/routes.php'
            );
        }
    }

    /**
     * Auto-register auth components
     */
    protected function registerAuthComponents(): void
    {
        Auth::provider(
            'laravel-cas',
            static fn (): UserProvider => new CasUserProvider(app('session.store'))
        );
        
        Auth::extend(
            'laravel-cas',
            static fn (Application $app, string $name, array $config): Guard => new CasGuard(
                new CasUserProvider(app('session.store')), 
                $app->make('request'), 
                app('session.store')
            )
        );
    }

    /**
     * Auto-register middleware
     */
    protected function registerMiddleware(): void
    {
        // Skip auto-registration if user wants to manually control middleware
        if (config('laravel-cas.auto_register_middleware', true) === false) {
            return;
        }

        $router = $this->app['router'];
        
        // Register as named middleware for route-specific usage
        $router->aliasMiddleware('cas.auth', CasAuthenticator::class);
    }

    /**
     * Auto-register PSR dependencies with sensible defaults
     */
    protected function registerPsrDependencies(): void
    {
        // Only bind if not already bound
        if (!$this->app->bound(ClientInterface::class)) {
            $this->app->bind(
                ClientInterface::class,
                static fn (Application $app): ClientInterface => new Client()
            );
        }

        if (!$this->app->bound(Psr17Interface::class)) {
            $this->app->bind(
                Psr17Interface::class,
                static function (Application $app): Psr17Interface {
                    $psr17Factory = new Psr17Factory();

                    return new Psr17(
                        $psr17Factory,
                        $psr17Factory,
                        $psr17Factory,
                        $psr17Factory,
                        $psr17Factory,
                        $psr17Factory
                    );
                }
            );
        }
    }

    /**
     * Register CAS-related services
     */
    protected function registerCasServices(): void
    {
        $this->app->bind(
            PropertiesInterface::class,
            static fn (Application $app): PropertiesInterface => new Laravel(
                new ParameterBag((array) config('laravel-cas')),
                $app['router']
            )
        );
        
        $this->app->bind(
            CasResponseBuilderInterface::class,
            static fn (Application $app): CasResponseBuilder => $app->make(CasResponseBuilder::class)
        );
        
        $this->app->bind(
            CasInterface::class,
            static fn (Application $app): Cas => $app->make(Cas::class)
        );
        
        $this->app->bind(
            AuthenticationFailureFactoryInterface::class,
            static fn (Application $app): AuthenticationFailureFactory => $app->make(AuthenticationFailureFactory::class)
        );
        
        $this->app->bind(
            ProxyFactoryInterface::class,
            static fn (Application $app): ProxyFactory => $app->make(ProxyFactory::class)
        );
        
        $this->app->bind(
            ServiceValidateFactoryInterface::class,
            static fn (Application $app): ServiceValidateFactory => $app->make(ServiceValidateFactory::class)
        );
        
        $this->app->bind(
            ProxyFailureFactoryInterface::class,
            static fn (Application $app): ProxyFailureFactory => $app->make(ProxyFailureFactory::class)
        );
    }
}
