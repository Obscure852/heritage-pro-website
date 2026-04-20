<?php

namespace App\Models\Activities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityEligibilityTarget extends Model
{
    use HasFactory;

    public const TARGET_GRADE = 'grade';
    public const TARGET_CLASS = 'class';
    public const TARGET_HOUSE = 'house';
    public const TARGET_STUDENT_FILTER = 'student_filter';

    protected $fillable = [
        'activity_id',
        'target_type',
        'target_id',
    ];

    public static function targetTypes(): array
    {
        return [
            self::TARGET_GRADE => 'Grade',
            self::TARGET_CLASS => 'Class',
            self::TARGET_HOUSE => 'House',
            self::TARGET_STUDENT_FILTER => 'Student Filter',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
