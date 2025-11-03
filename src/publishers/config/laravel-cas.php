<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ec-doris/laravel-cas
 */

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | CAS Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your CAS server settings. For EU institutions, the default
    | base_url should work out of the box.
    |
    */
    'base_url' => env('CAS_URL', 'https://webgate.ec.europa.eu/cas'),
    
    /*
    |--------------------------------------------------------------------------
    | Package Behavior Configuration
    |--------------------------------------------------------------------------
    |
    | Control how the Laravel CAS package behaves in your application.
    |
    */
    'auto_register_middleware' => env('CAS_AUTO_REGISTER_MIDDLEWARE', true),
    'auto_load_routes' => env('CAS_AUTO_LOAD_ROUTES', true),
    
    /*
    |--------------------------------------------------------------------------
    | CAS Redirect Route
    |--------------------------------------------------------------------------
    |
    | The name of the Laravel route to redirect to after a successful login.
    | This is typically a dashboard or user profile page.
    |
    */
    'redirect_login_route' => env('CAS_REDIRECT_LOGIN_ROUTE', 'dashboard'),

    /*
    |--------------------------------------------------------------------------
    | CAS Redirect Logout URL
    |--------------------------------------------------------------------------
    |
    | The URL to redirect to after the user logs out.
    |
    */
    'redirect_logout_url' => env('CAS_REDIRECT_LOGOUT_URL'),
    
    /*
    |--------------------------------------------------------------------------
    | Development & Debug Settings
    |--------------------------------------------------------------------------
    |
    | Settings for development and debugging. NEVER use in production!
    |
    */
    'masquerade' => env('CAS_MASQUERADE', null),
    'demo_mode' => env('CAS_DEMO_MODE', false),
    'demo_login_url' => env('CAS_DEMO_LOGIN_URL', 'https://demo-eulogin.cnect.eu'),
    'debug' => env('CAS_DEBUG', false),
    'verbose_errors' => env('CAS_VERBOSE_ERRORS', false),
    
    /*
    |--------------------------------------------------------------------------
    | EU/EC Institution Settings
    |--------------------------------------------------------------------------
    |
    | Configuration specific to EU and EC applications.
    |
    */
    'eu_login_enabled' => env('CAS_EU_LOGIN_ENABLED', true),
    'institution_code' => env('CAS_INSTITUTION_CODE', ''),
    
    /*
    |--------------------------------------------------------------------------
    | CAS Protocol Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the CAS protocol endpoints and parameters. These defaults
    | are optimized for EU Login but can be customized as needed.
    |
    */
    'protocol' => [
        'login' => [
            'path' => '/login',
            'allowed_parameters' => [
                'service',
                'renew',
                'gateway',
            ],
            'default_parameters' => [],
        ],
        'serviceValidate' => [
            'path' => '/p3/serviceValidate',
            'allowed_parameters' => [
                'format',
                'pgtUrl',
                'service',
                'ticket',
            ],
            'default_parameters' => [
                'format' => 'JSON',
                'groups' => true,
                // 'pgtUrl' => env('CAS_PROXY_CALLBACK_URL', url('/proxy/callback')),
            ],
        ],
        'logout' => [
            'path' => '/logout',
            'allowed_parameters' => [
                'service',
            ],
            'default_parameters' => [
                'service' => env('CAS_REDIRECT_LOGOUT_URL', 'http://localhost'),
            ],
        ],
        'proxy' => [
            'path' => '/proxy',
            'allowed_parameters' => [
                'targetService',
                'pgt',
            ],
        ],
        'proxyValidate' => [
            'path' => '/proxyValidate',
            'allowed_parameters' => [
                'format',
                'pgtUrl',
                'service',
                'ticket',
            ],
            'default_parameters' => [
                'format' => 'JSON',
                'pgtUrl' => env('CAS_PROXY_CALLBACK_URL', 'http://localhost/proxy/callback'),
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | EU/EC Institution Presets
    |--------------------------------------------------------------------------
    |
    | Pre-configured settings for common EU institutions. You can reference
    | these in your environment configuration.
    |
    */
    'presets' => [
        'ec' => [
            'base_url' => 'https://webgate.ec.europa.eu/cas',
            'name' => 'European Commission',
        ],
        'eu_institutions' => [
            'base_url' => 'https://webgate.ec.europa.eu/cas',
            'name' => 'EU Institutions',
        ],
        'euipo' => [
            'base_url' => 'https://webgate.ec.europa.eu/cas',
            'name' => 'European Union Intellectual Property Office',
        ],
    ],
];
