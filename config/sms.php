<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Package Rates Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for SMS package rates.
    | Rates can be configured via environment variables or will fall back
    | to the default values defined below.
    |
    */

    'package_rates' => [
        'Basic' => env('SMS_BASIC_RATE', 0.35),
        'Standard' => env('SMS_STANDARD_RATE', 0.30),
        'Premium' => env('SMS_PREMIUM_RATE', 0.25),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS API Configuration
    |--------------------------------------------------------------------------
    |
    | Additional SMS-related configuration can be added here.
    |
    */
];
