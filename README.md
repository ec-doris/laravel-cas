# Laravel CAS Bundle

A CAS bundle for Laravel with automatic configuration for EU Login and EC applications.

## Installation

```shell
composer require ec-doris/laravel-cas
```

That's it! The package will automatically:
- Register CAS authentication guards and providers
- Set up PSR HTTP client dependencies
- Register routes and middleware (with fallback)
- Configure EU Login defaults

## Route Publishing (Recommended)

For better control and frontend tool integration (like Ziggy), publish the CAS routes:

```shell
# Install everything (recommended for new installations)
php artisan cas:install --all

# Or install specific components
php artisan cas:install --routes
php artisan cas:install --config
```

This will:
- Publish `routes/laravel-cas.php` with all CAS routes
- Automatically include them in your `routes/web.php`
- Allow frontend tools like Ziggy to discover the routes
- Give you full control to customize the routes

### Manual Route Publishing

Alternatively, publish routes manually:

```shell
php artisan vendor:publish --tag=laravel-cas-routes
```

Then add this line to your `routes/web.php`:

```php
// Include Laravel CAS routes
require __DIR__ . '/laravel-cas.php';
```

## Basic Configuration

Create a `.env` file with your CAS settings:

```env
# Required
CAS_URL=https://webgate.ec.europa.eu/cas
CAS_REDIRECT_LOGIN_URL=https://your-app.com/homepage
CAS_REDIRECT_LOGOUT_URL=https://your-app.com/

# Optional - Development
CAS_MASQUERADE=your.email@example.com  # For development only!
CAS_DEBUG=false

# Optional - Package Behavior
CAS_INSTITUTION_CODE=EC
CAS_PROXY_CALLBACK_URL=https://your-app.com/proxy/callback
```

## Authentication Guard Setup

Add to your `config/auth.php`:

```php
'guards' => [
    'laravel-cas' => [
        'driver' => 'laravel-cas',
        'provider' => 'laravel-cas',
    ],
],

'providers' => [
    'laravel-cas' => [
        'driver' => 'laravel-cas',
    ],
],
```

## Middleware Usage

The package automatically registers the `cas.auth` middleware. Use it in your routes:

```php
Route::get('/protected', function () {
    return 'This route is protected by CAS';
})->middleware('cas.auth');

// Or in route groups
Route::middleware(['cas.auth'])->group(function () {
    Route::get('/dashboard', 'DashboardController@index');
    Route::get('/profile', 'ProfileController@index');
});
```

## Advanced Configuration (Optional)

To customize the configuration, publish the config files:

```shell
php artisan vendor:publish --tag=laravel-cas-config
# or use the install command
php artisan cas:install --config
```

This will publish:
- `config/laravel-cas.php` - Complete CAS configuration with documentation

### Route Behavior Control

You can control how routes are loaded:

```env
# Disable auto-loading of routes (when published)
CAS_AUTO_LOAD_ROUTES=false

# Disable auto-middleware registration
CAS_AUTO_REGISTER_MIDDLEWARE=false
```

## Available Routes

After publishing routes, you'll have access to:

- `/login` - CAS login endpoint
- `/logout` - CAS logout endpoint  
- `/homepage` - Post-login homepage
- `/proxy/callback` - CAS proxy callback

These routes are now:
- ✅ Discoverable by frontend tools (Ziggy, Laravel Echo, etc.)
- ✅ Customizable in your `routes/laravel-cas.php` file
- ✅ Fully integrated with your application routing

## Frontend Integration

Since routes are published to your routes directory, frontend tools will automatically detect them:

```javascript
// With Ziggy
route('laravel-cas-login')  // Available!
route('laravel-cas-logout') // Available!

// Routes are now part of your app's route cache
php artisan route:cache
```

## EU/EC Institution Presets

The package includes presets for common EU institutions:

```php
// In your .env file
CAS_URL=https://webgate.ec.europa.eu/cas  # Default for EU institutions
```

## Manual Configuration (For Advanced Users)

If you need to customize PSR dependencies or disable auto-registration:

```env
CAS_AUTO_REGISTER_MIDDLEWARE=false
```

Then manually configure in your `AppServiceProvider`:

```php
use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Client;
use loophp\psr17\Psr17Interface;
use Nyholm\Psr7\Factory\Psr17Factory;
use loophp\psr17\Psr17;

public function register(): void
{
    $this->app->bind(
        ClientInterface::class,
        function(Application $app): ClientInterface {
            return new Client();
        }
    );
    
    $this->app->bind(
        Psr17Interface::class,
        function(Application $app): Psr17Interface {
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
```

## User Model Customization

By default, the package uses `App\Models\User`. To use a different model:

```env
CAS_USER_MODEL=App\Models\CustomUser
```

Ensure your user model has these attributes:
- `email` (required)
- `name` 
- `organisation` (for EU/EC applications)

## Migration from Previous Versions

If upgrading from an older version:

1. Remove manual PSR bindings from your `AppServiceProvider`
2. Remove middleware registration from `Kernel.php` 
3. The package now handles these automatically
4. Update your `.env` file with the new variable names
5. Optionally publish and customize the new config files
