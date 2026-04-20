<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScale extends Model {
    protected $table = 'lms_grade_scales';

    protected $fillable = [
        'name',
        'type',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPE_LETTER = 'letter';
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_POINTS = 'points';
    public const TYPE_PASS_FAIL = 'pass_fail';
    public const TYPE_CUSTOM = 'custom';

    public static array $types = [
        self::TYPE_LETTER => 'Letter Grades (A, B, C...)',
        self::TYPE_PERCENTAGE => 'Percentage',
        self::TYPE_POINTS => 'Points',
        self::TYPE_PASS_FAIL => 'Pass/Fail',
        self::TYPE_CUSTOM => 'Custom',
    ];

    // Relationships
    public function items(): HasMany {
        return $this->hasMany(GradeScaleItem::class, 'grade_scale_id')->orderByDesc('min_percentage');
    }

    public function gradebookSettings(): HasMany {
        return $this->hasMany(GradebookSettings::class, 'grade_scale_id');
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query) {
        return $query->where('is_default', true);
    }

    // Methods
    public static function getDefault(): ?self {
        return self::default()->first();
    }

    public function getGradeForPercentage(float $percentage): ?GradeScaleItem {
        return $this->items()
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();
    }

    public function getLetterGrade(float $percentage): ?string {
        $item = $this->getGradeForPercentage($percentage);
        return $item?->grade;
    }

    public function getGpaPoints(float $percentage): ?float {
        $item = $this->getGradeForPercentage($percentage);
        return $item?->grade_points;
    }

    public function setAsDefault(): void {
        self::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    public static function createLetterScale(): self {
        $scale = self::create([
            'name' => 'Standard Letter Grades',
            'type' => self::TYPE_LETTER,
            'is_default' => true,
        ]);

        $grades = [
            ['A+', 'Exceptional', 97, 100, 4.0, '#10b981'],
            ['A', 'Excellent', 93, 96.99, 4.0, '#10b981'],
            ['A-', 'Very Good', 90, 92.99, 3.7, '#22c55e'],
            ['B+', 'Good', 87, 89.99, 3.3, '#84cc16'],
            ['B', 'Above Average', 83, 86.99, 3.0, '#84cc16'],
            ['B-', 'Satisfactory', 80, 82.99, 2.7, '#eab308'],
            ['C+', 'Fair', 77, 79.99, 2.3, '#eab308'],
            ['C', 'Average', 73, 76.99, 2.0, '#f97316'],
            ['C-', 'Below Average', 70, 72.99, 1.7, '#f97316'],
            ['D+', 'Poor', 67, 69.99, 1.3, '#ef4444'],
            ['D', 'Very Poor', 63, 66.99, 1.0, '#ef4444'],
            ['D-', 'Minimal Pass', 60, 62.99, 0.7, '#ef4444'],
            ['F', 'Fail', 0, 59.99, 0.0, '#dc2626'],
        ];

        foreach ($grades as $pos => $grade) {
            GradeScaleItem::create([
                'grade_scale_id' => $scale->id,
                'grade' => $grade[0],
                'label' => $grade[1],
                'min_percentage' => $grade[2],
                'max_percentage' => $grade[3],
                'grade_points' => $grade[4],
                'color' => $grade[5],
                'position' => $pos,
            ]);
        }

        return $scale;
    }
}
