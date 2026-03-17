<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Tiers
    |--------------------------------------------------------------------------
    |
    | Define the available subscription tiers and their limits.
    | Each tier has limits for shops, webhooks per month, log retention,
    | and polling interval.
    |
    */

    'tiers' => [
        'trial' => [
            'name' => 'Trial',
            'shops_limit' => 1,
            'webhooks_limit' => 100,
            'log_retention_days' => 7,
            'polling_interval_minutes' => 5,
            'trial_days' => 14,
            'visible' => true,
            'prices' => [
                'monthly' => 0,
                'yearly' => 0,
            ],
        ],

        'starter' => [
            'name' => 'Starter',
            'shops_limit' => 1,
            'webhooks_limit' => 1000,
            'log_retention_days' => 7,
            'polling_interval_minutes' => 5,
            'trial_days' => 0,
            'visible' => true,
            'prices' => [
                'monthly' => 500, // €5.00 in cents
                'yearly' => 5500, // €55.00 in cents
            ],
            'stripe_prices' => [
                'monthly' => env('STRIPE_PRICE_STARTER_MONTHLY'),
                'yearly' => env('STRIPE_PRICE_STARTER_YEARLY'),
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'shops_limit' => 5,
            'webhooks_limit' => 10000,
            'log_retention_days' => 30,
            'polling_interval_minutes' => 1,
            'trial_days' => 0,
            'visible' => true,
            'prices' => [
                'monthly' => 1900, // €19.00 in cents
                'yearly' => 20900, // €209.00 in cents
            ],
            'stripe_prices' => [
                'monthly' => env('STRIPE_PRICE_PRO_MONTHLY'),
                'yearly' => env('STRIPE_PRICE_PRO_YEARLY'),
            ],
        ],

        'business' => [
            'name' => 'Business',
            'shops_limit' => null, // Unlimited
            'webhooks_limit' => null, // Unlimited
            'log_retention_days' => 90,
            'polling_interval_minutes' => 1,
            'trial_days' => 0,
            'visible' => true,
            'prices' => [
                'monthly' => 4900, // €49.00 in cents
                'yearly' => 53900, // €539.00 in cents
            ],
            'stripe_prices' => [
                'monthly' => env('STRIPE_PRICE_BUSINESS_MONTHLY'),
                'yearly' => env('STRIPE_PRICE_BUSINESS_YEARLY'),
            ],
        ],

        'dev' => [
            'name' => 'Dev',
            'shops_limit' => null, // Unlimited
            'webhooks_limit' => null, // Unlimited
            'log_retention_days' => 90,
            'polling_interval_minutes' => 1,
            'trial_days' => 0,
            'visible' => false, // Hidden tier for internal use
            'prices' => [
                'monthly' => 0,
                'yearly' => 0,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Tier
    |--------------------------------------------------------------------------
    |
    | The default tier for new users. Usually 'trial'.
    |
    */

    'default_tier' => 'trial',

    /*
    |--------------------------------------------------------------------------
    | Usage Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Percentages at which to send usage notifications.
    |
    */

    'usage_alert_thresholds' => [50, 75, 90, 100],

    /*
    |--------------------------------------------------------------------------
    | Trial Reminder Days
    |--------------------------------------------------------------------------
    |
    | Days before trial expiration to send reminder emails.
    |
    */

    'trial_reminder_days' => [3, 1, 0],

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | Stripe-specific settings for product creation.
    |
    */

    'stripe' => [
        'product_name' => 'ePages Webhook Notifications',
        'product_description' => 'Receive real-time webhook notifications for your ePages shop orders.',
    ],
];
