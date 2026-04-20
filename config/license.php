<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days after license expiration allowed.
    | During this period, administrators will see warnings but the system
    | will continue to function normally.
    |
    */
    'grace_period_days' => env('LICENSE_GRACE_PERIOD', 14),

    /*
    |--------------------------------------------------------------------------
    | Warning Period
    |--------------------------------------------------------------------------
    |
    | Number of days before license expiration to start showing warnings
    | to administrators.
    |
    */
    'warning_days' => env('LICENSE_WARNING_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Admin IPs
    |--------------------------------------------------------------------------
    |
    | IP addresses that are considered administrative. Users from these IPs
    | will see license warnings even for public routes.
    |
    */
    'admin_ips' => explode(',', env('ADMIN_IPS', '127.0.0.1')),
];
