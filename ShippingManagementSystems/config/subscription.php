<?php

return [
    'plans' => [
        'basic' => [
            'name' => 'Basic Plan',
            'price_id' => env('STRIPE_BASIC_PRICE_ID'),
            'price' => 9.99,
            'currency' => 'usd',
            'shipment_limit' => 10,
            'features' => [
                '10 shipments per month',
                'Basic support',
                'Tracking updates',
                'Standard delivery',
            ],
        ],
        'pro' => [
            'name' => 'Pro Plan',
            'price_id' => env('STRIPE_PRO_PRICE_ID'),
            'price' => 29.99,
            'currency' => 'usd',
            'shipment_limit' => PHP_INT_MAX,
            'features' => [
                'Unlimited shipments',
                'Priority support',
                'Express delivery included',
                'Advanced analytics',
                'Bulk shipping discount',
            ],
        ],
    ],

    'basic_shipment_limit' => env('BASIC_SHIPMENT_LIMIT', 10),
    'trial_days' => 0,
];
