# Laravel CAS Bundle

A CAS bundle for Laravel with automatic configuration for EU Login and EC applications.

## Installation

### From GitHub Repository

This package is hosted on GitHub and requires adding the repository to your `composer.json` file.

#### Step 1: Add Repository to composer.json

Add the following repository configuration to your project's `composer.json` file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ec-doris/laravel-cas"
        }
    ]
}
```

#### Step 2: Install the Package

```shell
composer require ec-doris/laravel-cas:dev-main
```

> **Note**: Since this package is hosted on GitHub, you need to specify `dev-main` to install from the main branch. For production use, it's recommended to specify a specific commit or create tagged releases.

#### For Production (Recommended)

To lock to a specific commit for production stability:

```shell
composer require ec-doris/laravel-cas:dev-main#abc1234
```

Replace `abc1234` with the specific commit hash you want to use.

#### Complete composer.json Example

Here's a complete example showing how your `composer.json` should look:

```json
{
    "name": "your-org/your-project",
    "type": "project",
    "description": "Your Laravel application",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ec-doris/laravel-cas"
        }
    ],
    "require": {
        "php": "^8.1",
        "laravel/framework": "^9.0|^10.0|^11.0",
        "ec-doris/laravel-cas": "dev-main"
    }
}
```

#### Alternative: One-Command Installation

You can also add the repository and install the package in one command:

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
   CAS_REDIRECT_LOGIN_URL=https://your-app.com/login
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
   - Visit `/login` to start CAS authentication
   - After authentication, you'll be redirected to `/homepage`
   - Access protected routes with `/dashboard` etc.

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
# Required - CAS Server URL
CAS_URL=https://webgate.ec.europa.eu/cas

# Required - Where CAS should redirect after authentication
# This should be a route in your app that has the cas.auth middleware
CAS_REDIRECT_LOGIN_URL=https://your-app.com/login

# Required - Where to redirect after logout
CAS_REDIRECT_LOGOUT_URL=https://your-app.com/

# Optional - Development
CAS_MASQUERADE=your.email@example.com  # For development only!
CAS_DEBUG=false

# Optional - Package Behavior
CAS_INSTITUTION_CODE=EC
CAS_PROXY_CALLBACK_URL=https://your-app.com/proxy/callback
```

> **Important**: Set `CAS_REDIRECT_LOGIN_URL` to point to your app's `/login` route (which has CAS middleware), not to `/dashboard` or other protected routes. The CAS server will redirect back to this URL with the service ticket.

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

### Development Installation

For development or contributing to this package:

```shell
git clone https://github.com/ec-doris/laravel-cas.git
cd laravel-cas
composer install
./vendor/bin/phpunit
```
