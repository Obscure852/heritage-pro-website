<?php

namespace App\Models\Timetable;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timetable setting model.
 *
 * Key-value store for timetable module configuration.
 *
 * @property int $id
 * @property string $key
 * @property array|null $value
 * @property string|null $description
 * @property int|null $updated_by
 * @property \Carbon\Carbon|null $updated_at
 */
class TimetableSetting extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function (self $model) {
            $model->updated_at = $model->updated_at ?? now();
        });

        static::updating(function (self $model) {
            $model->updated_at = now();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function updatedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== STATIC HELPERS ====================

    /**
     * Get a setting value by key.
     * Returns the full JSON-decoded value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set a setting value by key.
     * Value will be JSON-encoded via cast.
     *
     * Uses updateOrCreate for atomic upsert — safe under concurrent writes.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $userId
     * @return static
     */
    public static function set(string $key, $value, ?int $userId = null): self {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'updated_by' => $userId,
            ]
        );
    }

    /**
     * Delete a setting by key.
     */
    public static function forget(string $key): void {
        static::where('key', $key)->delete();
    }
}
