<?php

namespace App\Models\StaffAttendance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Staff attendance setting model.
 *
 * Key-value store for staff attendance module configuration.
 * Follows the LeaveSetting pattern.
 *
 * @property int $id
 * @property string $key
 * @property array|null $value
 * @property string|null $description
 * @property int|null $updated_by
 * @property \Carbon\Carbon|null $updated_at
 * @property-read User|null $updatedBy
 */
class StaffAttendanceSetting extends Model
{
    /**
     * Disable Laravel's automatic timestamps.
     * We manage updated_at manually.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->updated_at = $model->updated_at ?? now();
        });

        static::updating(function (self $model) {
            $model->updated_at = now();
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who last updated this setting.
     *
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== STATIC HELPERS ====================

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $userId
     * @return static
     */
    public static function set(string $key, $value, ?int $userId = null): self
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->updated_by = $userId;
        $setting->updated_at = now();
        $setting->save();

        return $setting;
    }
}
