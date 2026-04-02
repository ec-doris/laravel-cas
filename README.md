# Laravel CAS Bundle

A Laravel CAS authentication bundle for EU Login and EC applications.

The package provides:
- package login, logout, callback, and proxy callback routes
- a `laravel-cas` auth guard driver and provider driver
- a `cas.auth` middleware alias for route protection
- automatic user creation on first successful CAS login
- optional `CAS_MASQUERADE` and `CAS_DEMO_MODE` flows for non-production environments

## Installation

### Install from GitHub

If you are consuming the current branch directly from this repository:

```shell
composer config repositories.laravel-cas vcs https://github.com/ec-doris/laravel-cas
composer require ec-doris/laravel-cas:dev-main
```

### What the Package Wires Up

After installation, Laravel package discovery will:
- register the package service provider
- register the `laravel-cas` auth guard and provider drivers
- register default PSR-18 / PSR-17 bindings when those interfaces are not already bound
- register the `cas.auth` middleware alias unless disabled
- load the built-in CAS routes as a fallback when you have not published them

You still need to:
- configure your CAS environment variables
- add a `laravel-cas` guard and provider entry to `config/auth.php`
- protect your application routes with `cas.auth` or redirect guests to `route('laravel-cas-login')`

## Quick Setup Guide

After installing the package, follow these steps:

1. **Publish the package files** (recommended):
   ```shell
   php artisan cas:install --all
   ```

   The package can run with its fallback internal routes if you do not publish them, but publishing is better when you want route discovery, route caching visibility, or customization.

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
           'model' => App\Models\User::class,
       ],
   ],
   ```

4. **Protect your routes** with the named middleware:
   ```php
   Route::middleware(['web', 'cas.auth'])->group(function () {
       Route::get('/dashboard', function () {
           $user = auth('laravel-cas')->user();

           return view('dashboard', compact('user'));
       })->name('dashboard');
   });
   ```

   Do **not** add `EcDoris\LaravelCas\Middleware\CasAuthenticator` to the global `web` middleware group. That intercepts `/login` before the package login controller runs and causes `/login` to redirect back to itself in a loop.

5. **Ensure your CAS server allows the callback URL**:
   - `https://your-app.com/cas/callback`

6. **Test the flow**:
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

This package is designed to work with a small configuration surface.

A minimal `.env` looks like:

```env
CAS_URL=https://webgate.ec.europa.eu/cas

CAS_REDIRECT_LOGIN_ROUTE=dashboard

CAS_REDIRECT_LOGOUT_URL=https://your-app.com/
```

Notes:
- `CAS_URL` defaults to `https://webgate.ec.europa.eu/cas`
- `CAS_REDIRECT_LOGIN_ROUTE` defaults to `dashboard`; if that route does not exist, post-login redirection falls back to `/`
- `CAS_REDIRECT_LOGOUT_URL` should usually be set explicitly so CAS logout returns to a valid application URL
- `CAS_MASQUERADE`, `CAS_DEMO_MODE`, and `CAS_DEMO_LOGIN_URL` are development-only settings and should not be enabled in production

### How it Works

The package uses a fixed internal callback route, `/cas/callback`, to handle ticket validation. You do not need to configure a separate callback route name.

1.  When a user accesses a protected route, they are redirected to the CAS server.
2.  The package tells the CAS server to send the user back to `https://your-app.com/cas/callback`.
3.  The `cas.auth` middleware on `/cas/callback` validates the returned CAS ticket.
4.  After successful validation, the user is redirected to the route you specified in `CAS_REDIRECT_LOGIN_ROUTE`.

## Authentication Guard Setup

The package registers the `laravel-cas` guard and provider drivers, but your application still needs to define a guard and provider entry in `config/auth.php`:

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
        'model' => App\Models\User::class,
    ],
],
```

If `auth.providers.users.model` already points at the correct model, the explicit `model` key can be omitted.

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

Do not add `EcDoris\LaravelCas\Middleware\CasAuthenticator` to the global `web` middleware group. If you do, `/login` is intercepted before `LoginController` runs, and guests are redirected back to `/login`, causing a self-redirect loop.

Use one of these patterns instead:
- Apply the named `cas.auth` middleware only to the routes or route groups that should require CAS authentication.
- Keep Laravel's normal `auth` middleware and redirect guests to `route('laravel-cas-login')`.

For Laravel 11+ apps, guest redirection can be configured in `bootstrap/app.php`:

```php
use Illuminate\Foundation\Configuration\Middleware;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->redirectGuestsTo(fn () => route('laravel-cas-login'));
})
```

## Advanced Configuration (Optional)

To customize the configuration, publish the config files:

```shell
php artisan vendor:publish --tag=laravel-cas-config
php artisan cas:install --config
```

This will publish:
- `config/laravel-cas.php` - Complete CAS configuration with documentation

### Route Behavior Control

You can control how routes are loaded:

```env
CAS_AUTO_LOAD_ROUTES=false
CAS_AUTO_REGISTER_MIDDLEWARE=false
```

Use these when:
- `CAS_AUTO_LOAD_ROUTES=false`: you want to disable the fallback internal route loading and rely only on your published `routes/laravel-cas.php`
- `CAS_AUTO_REGISTER_MIDDLEWARE=false`: you want to register the `cas.auth` alias yourself instead of letting the package do it

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

On the published route file, `/cas/callback` is registered with `cas.auth`. The controller itself is intentionally blank; the middleware performs the ticket validation and redirect.

## Frontend Integration

Since routes are published to your routes directory, frontend tools will automatically detect them:

```javascript
// With Ziggy
route('laravel-cas-login')  // Available!
route('laravel-cas-logout') // Available!

// Routes are now part of your app's route cache
php artisan route:cache
```

## EU Login Defaults

The default `CAS_URL` already points to the European Commission CAS endpoint:

```php
// In your .env file
CAS_URL=https://webgate.ec.europa.eu/cas  # Default for EU institutions
```

If you publish `config/laravel-cas.php`, you will also see a `presets` array with reference values for common EU installations. That array is documentation/reference data; the package does not dynamically switch presets based on `CAS_INSTITUTION_CODE` or other env flags.

## Custom HTTP / PSR Bindings

If you want to override the default PSR HTTP client or PSR-17 factory bindings, bind your own implementations in your application container:

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
            return new Client(['timeout' => 30]);
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

The package only supplies its default bindings when these interfaces are not already bound.

## User Model

The package uses the model configured on `auth.providers.laravel-cas.model`, or falls back to `auth.providers.users.model`.

On first successful CAS login:
- the user is looked up by email, case-insensitively
- if no matching user exists, a new one is created
- `name` is built from CAS `firstName` and `lastName`
- if `departmentNumber`, `department_number`, or `organisation` are fillable on the model, CAS `departmentNumber` is copied into them

Current behavior:
- `email` is required in the CAS attributes for authentication to succeed
- separate `firstName` and `lastName` columns are not persisted by the package
- existing users are reused by email and are not resynchronized from CAS attributes on every login

## Development Modes

### Masquerade Mode

For local development without CAS server access, use masquerade mode to bypass authentication:

```env
CAS_MASQUERADE=your.email@example.com
```

When enabled in non-production, visiting `/login` will automatically create or authenticate the user with the specified email and redirect to the configured post-login route. Visiting `/logout` clears the local session and redirects without calling the upstream CAS logout endpoint.

### Demo Mode

For demonstrations and testing with a custom login form, use demo mode:

```env
CAS_DEMO_MODE=true
CAS_DEMO_LOGIN_URL=https://demo-eulogin.cnect.eu
```

When enabled in non-production:
1. Visiting `/login` redirects to the demo login form with a `returnto` parameter
2. The demo form collects user information and redirects back with a `ticket=DEMO_{json}` parameter
3. The middleware decodes the demo ticket and creates or authenticates the user

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

## Local Development

For development or contribution work in this repository:

```shell
git clone https://github.com/ec-doris/laravel-cas.git
cd laravel-cas
composer install
```

## Test Commands

Use these commands during package development:

```shell
composer verify
vendor/bin/phpunit
npm run e2e
npm run e2e:debug
npm run e2e:headed
```

Command summary:
- `composer verify`: full local verification loop; installs npm dependencies and the local Playwright Chromium browser if they are missing, then runs PHPUnit and Playwright
- `vendor/bin/phpunit`: PHPUnit suite only
- `npm run e2e`: Playwright suite only
- `npm run e2e:debug` / `npm run e2e:headed`: browser debugging variants

## Local E2E Harness

The repository includes a local Workbench app, a deterministic CAS protocol stub, and Playwright browser tests so package changes can be exercised without pulling the branch into a separate Laravel application.

Run the full local verification loop:

```shell
composer verify
```

`composer verify` is the one-command local validation loop. It will:

1. Install npm dependencies if `node_modules` is missing
2. Install the local Playwright Chromium browser if it is missing
3. Run the PHPUnit suite
4. Run the Playwright end-to-end suite

What this covers:

1. Guest access to a protected Workbench route redirects through `/login`
2. `/login` redirects to a local CAS server
3. The CAS server issues a real service ticket and redirects to `/cas/callback`
4. The package validates the ticket, creates the user if needed, authenticates the session, persists mapped attributes, and redirects to the dashboard
5. `/logout` clears the session and round-trips through CAS logout back to the Workbench app

Manual local loop:

```shell
npm install
npm run e2e:install
npm run cas:stub
./scripts/e2e/serve-workbench.sh
```

Then, in another terminal:

```shell
npm run e2e
```

Or open:

```text
http://127.0.0.1:8001/dashboard
```

This harness uses a local CAS stub, not a real Apereo CAS or EU Login server. It is intended to exercise the package end to end inside this repository.

Useful local commands:

```shell
composer verify
npm run cas:stub
./scripts/e2e/serve-workbench.sh
curl -i http://127.0.0.1:8001/login
curl -i http://127.0.0.1:9800/cas/__health
```

Local CAS stub credentials:

```text
username: casuser
password: Mellon
```

The Workbench dashboard exposes `name`, `email`, `departmentNumber`, `department_number`, and `organisation` so user creation and attribute-mapping behavior are visible during development.

The GitHub Actions workflow runs the same verification flow on pushes and pull requests, so local and CI coverage stay aligned.
