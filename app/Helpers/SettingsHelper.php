<?php

use App\Services\SettingsService;

if (!function_exists('settings')) {
    /**
     * Get a system setting value
     *
     * @param string|null $key Setting key (e.g., 'sms.batch_size')
     * @param mixed $default Default value if setting not found
     * @return mixed|SettingsService
     */
    function settings(?string $key = null, $default = null)
    {
        $service = app(SettingsService::class);

        if ($key === null) {
            return $service;
        }

        return $service->get($key, $default);
    }
}

if (!function_exists('setting_set')) {
    /**
     * Set a system setting value
     *
     * @param string $key Setting key
     * @param mixed $value New value
     * @param int|null $userId User making the change
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    function setting_set(string $key, $value, ?int $userId = null): bool
    {
        return app(SettingsService::class)->set($key, $value, $userId);
    }
}

if (!function_exists('setting_has')) {
    /**
     * Check if a setting exists
     *
     * @param string $key Setting key
     * @return bool
     */
    function setting_has(string $key): bool
    {
        return app(SettingsService::class)->has($key);
    }
}

if (!function_exists('settings_by_category')) {
    /**
     * Get all settings for a specific category
     *
     * @param string $category Category name (e.g., 'sms', 'email', 'rate_limit')
     * @return array Key-value pairs of settings
     */
    function settings_by_category(string $category): array
    {
        return app(SettingsService::class)->getByCategory($category);
    }
}

if (!function_exists('settings_refresh')) {
    /**
     * Refresh settings cache
     *
     * @param string|null $key Specific setting to refresh, or null for all
     * @return void
     */
    function settings_refresh(?string $key = null): void
    {
        app(SettingsService::class)->refresh($key);
    }
}
