<?php

namespace App\Models\Library;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Library setting model.
 *
 * Key-value store for library module configuration.
 *
 * @property int $id
 * @property string $key
 * @property array|null $value
 * @property string|null $description
 * @property int|null $updated_by
 * @property \Carbon\Carbon|null $updated_at
 */
class LibrarySetting extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'array',
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

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== STATIC HELPERS ====================

    /**
     * Get a setting value by key.
     * Returns the full JSON-decoded value (array).
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
     * Value should be an array (will be JSON-encoded via cast).
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $userId
     * @return static
     */
    public static function set(string $key, $value, ?int $userId = null): self {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->updated_by = $userId;
        $setting->updated_at = now();
        $setting->save();

        return $setting;
    }
}
