<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the filesystem disk and maximum file size for document uploads.
    | The disk must be defined in config/filesystems.php.
    |
    */

    'storage' => [
        'disk' => env('DOCUMENTS_DISK', 'documents'),
        'max_file_size_mb' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Storage Quotas
    |--------------------------------------------------------------------------
    |
    | Default and admin quota values in bytes. Users receive a warning when
    | their usage exceeds the warning_threshold_percent of their quota.
    |
    */

    'quotas' => [
        'default_bytes' => 524288000, // 500MB
        'admin_bytes' => 2097152000,  // 2GB
        'warning_threshold_percent' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Extensions (DOC-03)
    |--------------------------------------------------------------------------
    |
    | Only files with these extensions may be uploaded. Both extension and
    | MIME type are validated during upload to prevent spoofed files.
    |
    */

    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx',
        'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    |
    | Corresponding MIME types for allowed extensions. Both extension and
    | MIME type must match for an upload to be accepted.
    |
    */

    'allowed_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'image/jpeg',
        'image/png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy Defaults
    |--------------------------------------------------------------------------
    |
    | Default retention period and grace period for document lifecycle.
    | trash_retention_days controls how long soft-deleted documents are kept
    | before permanent deletion.
    |
    */

    'retention' => [
        'default_days' => 2555, // 7 years
        'grace_period_days' => 30,
        'trash_retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Workflow
    |--------------------------------------------------------------------------
    |
    | Controls whether documents require approval before publication
    | and the default deadline for reviewers.
    |
    */

    'approval' => [
        'require_approval' => true,
        'review_deadline_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Access & Link Sharing
    |--------------------------------------------------------------------------
    |
    | Configuration for public document links including expiry limits,
    | rate limiting, and password attempt thresholds.
    |
    */

    'public' => [
        'enabled' => true,
        'default_link_expiry_days' => 30,
        'max_link_expiry_days' => 365,
        'max_links_per_document' => 5,
        'rate_limit_per_minute' => 100,
        'max_password_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Version Control
    |--------------------------------------------------------------------------
    |
    | Initial version number assigned to newly uploaded documents.
    |
    */

    'versioning' => [
        'initial_version' => '1.0',
    ],

];
