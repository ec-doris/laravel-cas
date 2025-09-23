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
    'user_model' => env('CAS_USER_MODEL', 'App\Models\User'),
    
    /*
    |--------------------------------------------------------------------------
    | Redirect Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where users are redirected after login/logout.
    |
    */
    'redirect_login_route' => env('CAS_REDIRECT_LOGIN_ROUTE', 'laravel-cas-homepage'),
    
    /*
    |--------------------------------------------------------------------------
    | Development & Debug Settings
    |--------------------------------------------------------------------------
    |
    | Settings for development and debugging. NEVER use masquerade in production!
    |
    */
    'masquerade' => env('CAS_MASQUERADE', null),
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
            'default_parameters' => [
                'service' => env('CAS_REDIRECT_LOGIN_URL', 'http://localhost/login'),
            ],
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
                'service' => env('CAS_REDIRECT_LOGIN_URL', 'http://localhost/login'),
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
