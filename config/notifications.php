<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */

    'sms' => [
        // Default SMS API provider
        'default_provider' => env('SMS_DEFAULT_PROVIDER', 'mascom'),

        // Batch size for SMS processing
        'batch_size' => env('SMS_BATCH_SIZE', 50),

        // Delay between batches (in seconds)
        'batch_delay' => env('SMS_BATCH_DELAY', 1),

        // SMS pricing per package type (in BWP)
        'pricing' => [
            'basic' => 0.35,
            'standard' => 0.30,
            'premium' => 0.25,
        ],

        // Characters per SMS unit
        'characters_per_unit' => 160,

        // Phone number validation
        'country_code' => env('SMS_COUNTRY_CODE', '267'), // Botswana
        'phone_regex' => '/^7\d{7}$/', // Botswana mobile format
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    */

    'email' => [
        // Maximum attachment size (in bytes)
        'max_attachment_size' => 10 * 1024 * 1024, // 10MB

        // Allowed attachment MIME types
        'allowed_attachment_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
        ],

        // Attachment storage path
        'attachment_path' => 'email_attachments',

        // Attachment cleanup after days
        'attachment_cleanup_days' => 30,

        // Maximum email body length (characters)
        'max_body_length' => 50000,

        // Default fallback values (used when school data is not configured)
        'defaults' => [
            'school_name' => env('DEFAULT_SCHOOL_NAME', 'Heritage Pro'),
            'address' => env('DEFAULT_SCHOOL_ADDRESS', 'Gaborone, Botswana'),
            'support_email' => env('DEFAULT_SUPPORT_EMAIL', 'support@heritagepro.co'),
            'logo_url' => env('DEFAULT_LOGO_URL', 'https://bw-syllabus.s3.us-east-1.amazonaws.com/heritage-pro-logo.jpg'),
            'sms_signature' => env('DEFAULT_SMS_SIGNATURE', ' :From Heritage Pro EMS'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        // Queue name for email jobs
        'email_queue' => env('EMAIL_QUEUE', 'default'),

        // Queue name for SMS jobs
        'sms_queue' => env('SMS_QUEUE', 'default'),

        // Job timeout (in seconds)
        'job_timeout' => env('NOTIFICATION_JOB_TIMEOUT', 300), // 5 minutes

        // Number of retry attempts
        'job_retries' => env('NOTIFICATION_JOB_RETRIES', 3),

        // Retry delay (in seconds)
        'retry_delay' => env('NOTIFICATION_RETRY_DELAY', 60), // 1 minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress Tracking Configuration
    |--------------------------------------------------------------------------
    */

    'progress' => [
        // Cache TTL for progress data (in seconds)
        'cache_ttl' => env('PROGRESS_CACHE_TTL', 7200), // 2 hours

        // Update frequency (every N messages)
        'update_frequency' => 5,

        // Maximum errors to keep in cache
        'max_errors_stored' => 10,

        // Progress polling interval (milliseconds) - for frontend
        'polling_interval' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        // Per-user daily SMS limit
        'sms_per_user_daily' => env('SMS_RATE_LIMIT_DAILY', 1000),

        // Per-user hourly SMS limit
        'sms_per_user_hourly' => env('SMS_RATE_LIMIT_HOURLY', 200),

        // Per-user daily email limit
        'email_per_user_daily' => env('EMAIL_RATE_LIMIT_DAILY', 500),

        // Per-user hourly email limit
        'email_per_user_hourly' => env('EMAIL_RATE_LIMIT_HOURLY', 100),

        // Rate limit cache prefix
        'cache_prefix' => 'notification_rate_limit:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    'logging' => [
        // Log level for notifications
        'level' => env('NOTIFICATION_LOG_LEVEL', 'info'),

        // Log sensitive data (emails, phone numbers) - GDPR consideration
        'log_sensitive_data' => env('NOTIFICATION_LOG_SENSITIVE', false),

        // Log successful sends (can be verbose)
        'log_successful_sends' => env('NOTIFICATION_LOG_SUCCESS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        // Enable SMS sending
        'sms_enabled' => env('SMS_ENABLED', true),

        // Enable email sending
        'email_enabled' => env('EMAIL_ENABLED', true),

        // Enable staff in-app direct messaging
        'staff_direct_messages_enabled' => env('STAFF_DIRECT_MESSAGES_ENABLED', true),

        // Enable the ambient online-staff launcher
        'staff_presence_launcher_enabled' => env('STAFF_PRESENCE_LAUNCHER_ENABLED', true),

        // Enable job cancellation
        'allow_job_cancellation' => env('ALLOW_JOB_CANCELLATION', true),

        // Enable delivery webhooks
        'enable_webhooks' => env('NOTIFICATION_WEBHOOKS_ENABLED', false),

        // Enable notification attachments
        'allow_attachments' => env('NOTIFICATION_ATTACHMENTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Internal Messaging Configuration
    |--------------------------------------------------------------------------
    */

    'internal_messaging' => [
        'online_window_minutes' => env('INTERNAL_MESSAGING_ONLINE_WINDOW', 2),
        'launcher_poll_seconds' => env('INTERNAL_MESSAGING_LAUNCHER_POLL', 45),
    ],

];
