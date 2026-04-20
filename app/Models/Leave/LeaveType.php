<?php

namespace App\Models\Leave;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Leave type model.
 *
 * Defines different types of leave available to staff (annual, sick, maternity, etc.).
 * Each type has configurable policies for entitlement, attachments, and restrictions.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property float $default_entitlement
 * @property bool $requires_attachment
 * @property int|null $attachment_required_after_days
 * @property string|null $gender_restriction
 * @property bool $is_paid
 * @property bool $allow_negative_balance
 * @property bool $allow_half_day
 * @property int|null $min_notice_days
 * @property int|null $max_consecutive_days
 * @property string|null $color
 * @property bool $is_active
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class LeaveType extends Model {
    use HasFactory;

    /**
     * Default attribute values.
     *
     * @var array
     */
    protected $attributes = [
        'requires_attachment' => false,
        'is_paid' => true,
        'allow_negative_balance' => false,
        'allow_half_day' => true,
        'min_notice_days' => 0,
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_entitlement',
        'requires_attachment',
        'attachment_required_after_days',
        'gender_restriction',
        'is_paid',
        'allow_negative_balance',
        'allow_half_day',
        'min_notice_days',
        'max_consecutive_days',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_entitlement' => 'decimal:2',
        'requires_attachment' => 'boolean',
        'is_paid' => 'boolean',
        'allow_negative_balance' => 'boolean',
        'allow_half_day' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Gender restriction constants
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    // ==================== RELATIONSHIPS ====================

    public function policies() {
        return $this->hasMany(LeavePolicy::class);
    }

    public function balances() {
        return $this->hasMany(LeaveBalance::class);
    }

    public function requests() {
        return $this->hasMany(LeaveRequest::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to only active leave types.
     */
    public function scopeActive(Builder $query): Builder {
        return $query->where('is_active', true);
    }

    /**
     * Scope to leave types available for a specific gender.
     * Returns types with no restriction OR matching the given gender.
     * Handles format differences (e.g., 'M'/'F' vs 'male'/'female').
     */
    public function scopeForGender(Builder $query, ?string $gender): Builder {
        // Normalize the gender to our constant format
        $normalizedGender = self::normalizeGender($gender);

        return $query->where(function ($q) use ($normalizedGender) {
            $q->whereNull('gender_restriction');
            if ($normalizedGender !== null) {
                $q->orWhere('gender_restriction', $normalizedGender);
            }
        });
    }

    /**
     * Scope to order by sort_order then name.
     */
    public function scopeOrdered(Builder $query): Builder {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if a user's gender matches this leave type's gender restriction.
     *
     * Handles format differences between User.gender ('M'/'F') and
     * LeaveType.gender_restriction ('male'/'female').
     *
     * @param string|null $userGender The user's gender value (e.g., 'M', 'F', 'Male', 'Female')
     * @return bool True if user is eligible (no restriction or gender matches)
     */
    public function isGenderEligible(?string $userGender): bool {
        // No restriction means everyone is eligible
        if ($this->gender_restriction === null) {
            return true;
        }

        // Normalize user gender to lowercase for comparison
        $normalizedUserGender = strtolower(trim($userGender ?? ''));

        // Map common gender formats to our constants
        $maleValues = ['m', 'male'];
        $femaleValues = ['f', 'female'];

        $userIsMale = in_array($normalizedUserGender, $maleValues);
        $userIsFemale = in_array($normalizedUserGender, $femaleValues);

        // Compare against restriction
        if ($this->gender_restriction === self::GENDER_MALE) {
            return $userIsMale;
        }

        if ($this->gender_restriction === self::GENDER_FEMALE) {
            return $userIsFemale;
        }

        // Unknown restriction format - fail safe (deny access)
        return false;
    }

    /**
     * Static method to normalize a gender value to the LeaveType format.
     *
     * @param string|null $gender The gender value to normalize
     * @return string|null Returns 'male', 'female', or null
     */
    public static function normalizeGender(?string $gender): ?string {
        if ($gender === null) {
            return null;
        }

        $normalized = strtolower(trim($gender));

        if (in_array($normalized, ['m', 'male'])) {
            return self::GENDER_MALE;
        }

        if (in_array($normalized, ['f', 'female'])) {
            return self::GENDER_FEMALE;
        }

        return null;
    }
}
