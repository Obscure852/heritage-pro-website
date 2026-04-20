<?php

namespace App\Models\StaffAttendance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for biometric ID to user mappings.
 *
 * Stores the link between device employee_numbers and system users.
 * Supports both auto-matched and manual mappings with audit trail.
 *
 * @property int $id
 * @property string $employee_number
 * @property int $user_id
 * @property string $source
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read User|null $createdByUser
 */
class BiometricIdMapping extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_number',
        'user_id',
        'source',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'source' => 'string',
    ];

    // ==================== SOURCE CONSTANTS ====================

    /**
     * Auto-matched source (matched via id_number).
     */
    const SOURCE_AUTO = 'auto';

    /**
     * Manually mapped by admin.
     */
    const SOURCE_MANUAL = 'manual';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user this mapping belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created this mapping (for manual mappings).
     *
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter auto-mapped entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAutoMapped($query)
    {
        return $query->where('source', self::SOURCE_AUTO);
    }

    /**
     * Scope to filter manually-mapped entries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeManualMapped($query)
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

    /**
     * Scope to filter by employee number.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $employeeNumber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEmployeeNumber($query, string $employeeNumber)
    {
        return $query->where('employee_number', $employeeNumber);
    }
}
