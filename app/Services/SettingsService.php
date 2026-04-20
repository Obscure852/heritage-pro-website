<?php

namespace App\Services;

use App\Models\SMSApiSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingsService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Cache key prefix
     */
    protected const CACHE_PREFIX = 'system_setting:';

    /**
     * Cache key for all settings
     */
    protected const CACHE_ALL_KEY = 'system_settings:all';

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = SMSApiSetting::where('key', $key)->first();

            if (!$setting) {
                // Fallback to config file
                return config('notifications.' . $key, $default);
            }

            return $this->castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $userId User making the change (for audit trail)
     * @return bool
     * @throws ValidationException
     */
    public function set(string $key, $value, ?int $userId = null): bool
    {
        $setting = SMSApiSetting::where('key', $key)->first();

        if (!$setting) {
            throw ValidationException::withMessages([
                'key' => ["Setting '{$key}' does not exist."]
            ]);
        }

        if (!$setting->is_editable) {
            throw ValidationException::withMessages([
                'key' => ["Setting '{$key}' is not editable."]
            ]);
        }

        // Validate the value
        if ($setting->validation_rules) {
            $validator = Validator::make(
                ['value' => $value],
                ['value' => $setting->validation_rules]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }
        }

        // Convert value to string for storage
        $stringValue = $this->prepareValueForStorage($value, $setting->type);

        // Update the setting
        $setting->update(['value' => $stringValue]);

        // Clear cache
        $this->clearCache($key);

        return true;
    }

    /**
     * Get all settings, optionally filtered by category
     *
     * @param string|null $category
     * @return \Illuminate\Support\Collection
     */
    public function all(?string $category = null)
    {
        $cacheKey = $category
            ? self::CACHE_ALL_KEY . ':' . $category
            : self::CACHE_ALL_KEY;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category) {
            $query = SMSApiSetting::orderBy('category')
                ->orderBy('display_order');

            if ($category) {
                $query->where('category', $category);
            }

            return $query->get()->map(function ($setting) {
                $setting->value = $this->castValue($setting->value, $setting->type);
                return $setting;
            });
        });
    }

    /**
     * Get all settings grouped by category
     *
     * @return \Illuminate\Support\Collection
     */
    public function allGrouped()
    {
        return $this->all()->groupBy('category');
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return SMSApiSetting::where('key', $key)->exists();
    }

    /**
     * Refresh the settings cache
     *
     * @param string|null $key Specific key to refresh, or null for all
     * @return void
     */
    public function refresh(?string $key = null): void
    {
        if ($key) {
            $this->clearCache($key);
        } else {
            // Clear all settings cache
            Cache::forget(self::CACHE_ALL_KEY);

            // Clear individual setting caches
            $keys = SMSApiSetting::pluck('key');
            foreach ($keys as $settingKey) {
                Cache::forget(self::CACHE_PREFIX . $settingKey);
            }

            // Clear category-specific caches
            $categories = SMSApiSetting::distinct('category')->pluck('category');
            foreach ($categories as $category) {
                Cache::forget(self::CACHE_ALL_KEY . ':' . $category);
            }
        }
    }

    /**
     * Bulk update multiple settings
     *
     * @param array $settings Key-value pairs
     * @param int|null $userId User making the changes
     * @return array ['success' => int, 'failed' => array]
     */
    public function bulkUpdate(array $settings, ?int $userId = null): array
    {
        $success = 0;
        $failed = [];

        foreach ($settings as $key => $value) {
            try {
                $this->set($key, $value, $userId);
                $success++;
            } catch (ValidationException $e) {
                $failed[$key] = $e->errors();
            }
        }

        return [
            'success' => $success,
            'failed' => $failed
        ];
    }

    /**
     * Get settings by category as key-value pairs
     *
     * @param string $category
     * @return array
     */
    public function getByCategory(string $category): array
    {
        return $this->all($category)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Cast a value to the appropriate type
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'integer':
            case 'int':
                return (int) $value;

            case 'decimal':
            case 'float':
            case 'double':
                return (float) $value;

            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case 'json':
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;

            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Prepare a value for storage (convert to string)
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function prepareValueForStorage($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return $value ? '1' : '0';

            case 'json':
            case 'array':
                return is_string($value) ? $value : json_encode($value);

            case 'integer':
            case 'int':
            case 'decimal':
            case 'float':
            case 'double':
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Clear cache for a specific setting
     *
     * @param string $key
     * @return void
     */
    protected function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget(self::CACHE_ALL_KEY);

        // Also clear category cache if we can determine it
        $setting = SMSApiSetting::where('key', $key)->first();
        if ($setting && $setting->category) {
            Cache::forget(self::CACHE_ALL_KEY . ':' . $setting->category);
        }
    }

    /**
     * Get categories with setting counts
     *
     * @return array
     */
    public function getCategories(): array
    {
        return SMSApiSetting::selectRaw('category, COUNT(*) as count')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Reset a setting to its default value from config
     *
     * @param string $key
     * @return bool
     */
    public function reset(string $key): bool
    {
        $setting = SMSApiSetting::where('key', $key)->first();

        if (!$setting || !$setting->is_editable) {
            return false;
        }

        // Get default from config
        $default = config('notifications.' . $key);

        if ($default !== null) {
            $setting->update(['value' => $this->prepareValueForStorage($default, $setting->type)]);
            $this->clearCache($key);
            return true;
        }

        return false;
    }
}
