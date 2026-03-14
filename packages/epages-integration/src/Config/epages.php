<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ePages API Credentials
    |--------------------------------------------------------------------------
    |
    | Your ePages app credentials. Get these from your ePages developer account.
    |
    */
    'client_id' => env('EPAGES_CLIENT_ID'),
    'client_secret' => env('EPAGES_CLIENT_SECRET'),
    'redirect_uri' => env('EPAGES_REDIRECT_URI', env('APP_URL') . '/epages/callback'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api_base_url' => env('EPAGES_API_URL', 'https://api.epages.com'),

    /*
    |--------------------------------------------------------------------------
    | App Scopes
    |--------------------------------------------------------------------------
    |
    | Define the permissions your app needs. Available scopes:
    | - read_products, write_products
    | - read_orders, write_orders
    | - read_customers, write_customers
    | - read_carts, write_carts
    | - read_categories, write_categories
    |
    */
    'scopes' => env('EPAGES_SCOPES', 'read_products,write_products,read_orders'),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'verify_signature' => env('EPAGES_VERIFY_SIGNATURE', true),
    'encrypt_tokens' => env('EPAGES_ENCRYPT_TOKENS', true),

    /*
    |--------------------------------------------------------------------------
    | API Client Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => env('EPAGES_TIMEOUT', 30),
    'retry_attempts' => env('EPAGES_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('EPAGES_RETRY_DELAY', 1000), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'enabled' => env('EPAGES_WEBHOOK_ENABLED', true),
        'events' => [
            'product.created',
            'product.updated',
            'product.deleted',
            'order.created',
            'order.updated',
            'order.deleted',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'shops' => 'epages_shops',
        'webhooks' => 'epages_webhooks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => 'epages',
        'middleware' => ['web'],
    ],
];
