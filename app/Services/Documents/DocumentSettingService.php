<?php

namespace App\Services\Documents;

use App\Models\DocumentSetting;

class DocumentSettingService {
    /**
     * Type map for proper casting when reading from DB.
     * Keys not listed default to string.
     */
    private const TYPE_MAP = [
        'quotas.default_bytes' => 'integer',
        'quotas.admin_bytes' => 'integer',
        'quotas.warning_threshold_percent' => 'integer',
        'retention.default_days' => 'integer',
        'retention.grace_period_days' => 'integer',
        'retention.trash_retention_days' => 'integer',
        'storage.max_file_size_mb' => 'integer',
        'approval.require_approval' => 'boolean',
        'approval.review_deadline_days' => 'integer',
        'allowed_extensions' => 'json',
    ];

    /**
     * Get a setting value. DB overrides config/documents.php.
     *
     * @param string $key The setting key (e.g., 'quotas.default_bytes')
     * @param mixed $default Fallback if neither DB nor config has a value
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $dbValue = DocumentSetting::where('key', $key)->value('value');
        if ($dbValue !== null) {
            return $this->castValue($key, $dbValue);
        }
        return config("documents.{$key}", $default);
    }

    /**
     * Set a value in the database, overriding the config default.
     *
     * @param string $key The setting key
     * @param mixed $value The value to store
     */
    public function set(string $key, $value): void {
        $storeValue = is_array($value) ? json_encode($value) : (string) $value;
        $group = explode('.', $key)[0] ?? 'general';
        DocumentSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $storeValue, 'group' => $group]
        );
    }

    /**
     * Get all settings for a section, merging DB overrides with config defaults.
     *
     * @param string $section Section name: quotas, retention, uploads, approval
     * @return array
     */
    public function getSection(string $section): array {
        // Uploads section has mixed key structure: storage.max_file_size_mb and top-level allowed_extensions
        if ($section === 'uploads') {
            $merged = [];
            // max_file_size_mb
            $dbMaxSize = DocumentSetting::where('key', 'storage.max_file_size_mb')->value('value');
            $merged['max_file_size_mb'] = $dbMaxSize !== null
                ? (int) $dbMaxSize
                : config('documents.storage.max_file_size_mb', 50);
            // allowed_extensions
            $dbExtensions = DocumentSetting::where('key', 'allowed_extensions')->value('value');
            $merged['allowed_extensions'] = $dbExtensions !== null
                ? (json_decode($dbExtensions, true) ?? [])
                : config('documents.allowed_extensions', []);
            return $merged;
        }

        $configValues = $this->getConfigDefaults($section);
        $dbOverrides = DocumentSetting::where('group', $section)
            ->pluck('value', 'key')
            ->toArray();

        $merged = [];
        foreach ($configValues as $key => $default) {
            $fullKey = "{$section}.{$key}";
            if (isset($dbOverrides[$fullKey])) {
                $merged[$key] = $this->castValue($fullKey, $dbOverrides[$fullKey]);
            } else {
                $merged[$key] = $default;
            }
        }
        return $merged;
    }

    /**
     * Get config defaults for a section.
     *
     * @param string $section
     * @return array
     */
    private function getConfigDefaults(string $section): array {
        $map = [
            'quotas' => config('documents.quotas', []),
            'retention' => config('documents.retention', []),
            'uploads' => [
                'max_file_size_mb' => config('documents.storage.max_file_size_mb', 50),
                'allowed_extensions' => config('documents.allowed_extensions', []),
            ],
            'approval' => config('documents.approval', []),
        ];
        return $map[$section] ?? [];
    }

    /**
     * Cast a DB string value to its proper type.
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    private function castValue(string $key, string $value) {
        $type = self::TYPE_MAP[$key] ?? 'string';
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
