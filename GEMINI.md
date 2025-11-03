# Project Overview

This project is a Laravel package that provides CAS (Central Authentication Service) authentication. It is specifically designed for use with EU Login and other European Commission applications. The package automates the configuration of CAS authentication guards, providers, and routes within a Laravel application.

## Main Technologies

*   **PHP**: The primary programming language.
*   **Laravel**: The web application framework for which this package is an extension.
*   **CAS (Central Authentication Service)**: The authentication protocol used for single sign-on.
*   **Composer**: The dependency manager for PHP.

## Architecture

The package integrates with Laravel's authentication system by providing a custom `CasGuard` and `CasUserProvider`. It automatically registers its own routes, middleware, and service provider. The configuration is handled through a published `laravel-cas.php` file and environment variables. The package also includes a console command (`cas:install`) to facilitate the installation and publication of assets.

# Building and Running

## Building

This is a library package, so there is no "build" process in the traditional sense. The package is intended to be included as a dependency in a Laravel application.

## Running Tests

To run the test suite, use the following command:

```bash
composer test
```

This command executes the `phpunit` script defined in `composer.json`.

# Development Conventions

## Coding Style

The codebase follows the PSR-12 coding style guide. This is a common convention in the Laravel community.

## Testing

The project uses PHPUnit for unit and feature testing. Tests are located in the `tests` directory. The `README.md` file provides instructions on how to run the tests.

## Contribution

The `README.md` file does not provide specific contribution guidelines, but it does link to the project's GitHub repository, where contribution guidelines may be found.
