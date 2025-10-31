# Laravel CAS Development Guide

## Commands

```bash
# Run all tests
composer phpunit
./vendor/bin/phpunit

# Run single test file
./vendor/bin/phpunit tests/Unit/LoginControllerTest.php

# Run single test method
./vendor/bin/phpunit --filter testIfRedirectUri

# Install in Laravel app
php artisan cas:install --all
```

## Code Style

- **Strict types**: Always use `declare(strict_types=1);` at the top of every PHP file
- **File headers**: Include BSD-3-Clause copyright header with link to GitHub repo
- **Type hints**: Full type hints for all parameters, return types, and properties (PHP 8.1+ syntax)
- **Constructor promotion**: Use promoted properties in constructors (e.g., `private Request $request`)
- **Imports**: Use fully qualified names in docblocks, import classes at top, import functions explicitly with `use function`
- **Static analysis**: No static analysis tools configured (consider adding PHPStan/Psalm)
- **Naming**: 
  - PSR-4 autoloading: `EcDoris\LaravelCas` namespace
  - Laravel conventions: PascalCase for classes, camelCase for methods/properties
  - Config keys: snake_case (e.g., `laravel-cas.masquerade`)
- **Error handling**: Throw exceptions for invalid states, use null returns for missing data
- **Laravel integration**: Use facades (`Auth`, `Route`), service container bindings, and configuration
- **Documentation**: PHPDoc annotations for complex methods, inline comments for business logic only

## Architecture

- **Package type**: Composer package for Laravel apps (library, not standalone app)
- **Purpose**: EU Login CAS authentication integration for Laravel projects
- **Installation**: Add via `composer config repositories` then `composer require ec-doris/laravel-cas`
- **Core dependency**: Uses `ecphp/cas-lib` for CAS protocol implementation
- **HTTP layer**: PSR-7/PSR-17 abstraction with GuzzleHTTP and Nyholm PSR-7
- **Laravel integration**: Auto-registered via package discovery, extends Auth Guard/UserProvider
- **Target users**: European Commission/EU institution Laravel developers
- **User model**: Always uses `App\Models\User` (not configurable)
- **Dev modes**: Masquerade mode (bypass auth) and Demo mode (custom login form) - both blocked in production
