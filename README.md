# Laravel CAS Bundle

A CAS bundle for Laravel with automatic configuration for EU Login and EC applications.

## Installation

### One-Command Installation

You can add the repository and install the package in two commands:

```shell
composer config repositories.laravel-cas vcs https://github.com/ec-doris/laravel-cas
composer require ec-doris/laravel-cas:dev-main
```

### Post-Installation

That's it! The package will automatically:
- Register CAS authentication guards and providers
- Set up PSR HTTP client dependencies (GuzzleHTTP, Nyholm PSR-7, Symfony PSR Bridge)
- Register routes and middleware (with fallback)
- Configure EU Login defaults

No additional dependencies or manual configuration required!

## Quick Setup Guide

After installing the package, follow these steps for immediate functionality:

1. **Publish CAS routes**:
   ```shell
   php artisan cas:install --all
   ```

2. **Update your `.env` file**:
   ```env
   CAS_URL=https://webgate.ec.europa.eu/cas
   CAS_REDIRECT_LOGIN_ROUTE=dashboard
   CAS_REDIRECT_LOGOUT_URL=https://your-app.com/
   ```

3. **Add auth guards to `config/auth.php`**:
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

4. **Protect your routes** by adding CAS middleware:
   ```php
   Route::get('/dashboard', function () {
       $user = auth('laravel-cas')->user();
       return view('dashboard', compact('user'));
   })->middleware('cas.auth');
   ```

5. **Test the flow**:
   - Visit `/login` to start CAS authentication.
   - After successful CAS authentication, you will be redirected to the route named in your `CAS_REDIRECT_LOGIN_ROUTE` variable (e.g., `/dashboard`).

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

This package is designed to work with minimal configuration.

Your `.env` file only needs a few variables to get started:

```env
# Required - The base URL of your CAS Server
CAS_URL=https://webgate.ec.europa.eu/cas

# Required - The name of the Laravel route to redirect to after a successful login.
# This is typically a dashboard or user profile page.
CAS_REDIRECT_LOGIN_ROUTE=dashboard

# Required - The URL to redirect to after the user logs out.
CAS_REDIRECT_LOGOUT_URL=https://your-app.com/

# Optional - For development only! Bypasses CAS and logs in the specified user.
CAS_MASQUERADE=your.email@example.com
```

### How it Works

The package now uses a hardcoded internal callback route (`/cas/callback`) to handle the communication with the CAS server. You no longer need to configure a callback URL.

1.  When a user accesses a protected route, they are redirected to the CAS server.
2.  The package tells the CAS server to send the user back to `https://your-app.com/cas/callback`.
3.  The middleware validates the ticket at this callback URL.
4.  After successful validation, the user is redirected to the route you specified in `CAS_REDIRECT_LOGIN_ROUTE`.

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

- `/login` - CAS login endpoint (named: `laravel-cas-login`)
- `/logout` - CAS logout endpoint (name: `laravel-cas-logout`)
- `/cas/callback` - Internal CAS callback route (name: `laravel-cas-callback`)
- `/proxy/callback` - CAS proxy callback (name: `laravel-cas-proxy-callback`)

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

If you need to disable auto-registration and provide your own PSR implementations:

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
    // Only needed if you want to use a different HTTP client
    $this->app->bind(
        ClientInterface::class,
        function(Application $app): ClientInterface {
            return new Client(['timeout' => 30]); // Custom configuration
        }
    );
    
    // Only needed if you want to use a different PSR-7 implementation
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

> **Note**: The package now automatically includes and configures GuzzleHTTP and Nyholm PSR-7 dependencies, so manual configuration is only needed for customization.

## User Model

The package uses `App\Models\User` for authentication. Ensure your user model has these attributes:
- `email` (required)
- `name` 
- `organisation` (optional, for EU/EC applications)
- `departmentNumber` (optional, for EU/EC applications)
- `department_number` (optional, for EU/EC applications)

## Development Modes

### Masquerade Mode

For local development without CAS server access, use masquerade mode to bypass authentication:

```env
CAS_MASQUERADE=your.email@example.com
```

When enabled (non-production only), visiting `/login` will automatically create/login the user with the specified email.

### Demo Mode

For demonstrations and testing with a custom login form, use demo mode:

```env
CAS_DEMO_MODE=true
CAS_DEMO_LOGIN_URL=https://demo-eulogin.cnect.eu
```

When enabled (non-production only):
1. Visiting `/login` redirects to the demo login form with a `returnto` parameter
2. The demo form collects user information and redirects back with a `ticket=DEMO_{json}` parameter
3. The middleware decodes the ticket and creates/logs in the user

The demo ticket JSON payload should contain:
- `email` (required)
- `firstName` (optional)
- `lastName` (optional)
- `departmentNumber` (optional)

**Important**: Neither masquerade nor demo mode can be used in production environments. The package will throw an exception if `APP_ENV=production` with these modes enabled.

## Migration from Previous Versions

If upgrading from an older version:

1. Remove manual PSR bindings from your `AppServiceProvider`
2. Remove middleware registration from `Kernel.php` 
3. The package now handles these automatically
4. Update your `.env` file with the new variable names
5. Optionally publish and customize the new config files

### Development Installation

For development or contributing to this package:

```shell
git clone https://github.com/ec-doris/laravel-cas.git
cd laravel-cas
composer install
./vendor/bin/phpunit
```
