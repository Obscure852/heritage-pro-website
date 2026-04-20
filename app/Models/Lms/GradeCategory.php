<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeCategory extends Model {
    protected $table = 'lms_grade_categories';

    protected $fillable = [
        'course_id',
        'name',
        'weight',
        'position',
        'drop_lowest',
        'drop_lowest_count',
        'color',
        'is_extra_credit',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'drop_lowest' => 'boolean',
        'is_extra_credit' => 'boolean',
    ];

    // Relationships
    public function course(): BelongsTo {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function items(): HasMany {
        return $this->hasMany(GradeItem::class, 'category_id')->orderBy('position');
    }

    // Scopes
    public function scopeOrdered($query) {
        return $query->orderBy('position');
    }

    // Methods
    public function getTotalMaxPoints(): float {
        return $this->items->sum('max_points');
    }

    public function calculateStudentGrade($studentId): array {
        $grades = Grade::whereIn('grade_item_id', $this->items->pluck('id'))
            ->where('student_id', $studentId)
            ->whereIn('status', ['graded', 'excused'])
            ->get();

        $totalEarned = 0;
        $totalPossible = 0;
        $scores = [];

        foreach ($grades as $grade) {
            if ($grade->status === 'excused') {
                continue;
            }

            $scores[] = [
                'score' => $grade->score,
                'max' => $grade->max_score ?? $grade->gradeItem->max_points,
            ];

            $totalEarned += $grade->score;
            $totalPossible += $grade->max_score ?? $grade->gradeItem->max_points;
        }

        // Handle drop lowest
        if ($this->drop_lowest && count($scores) > $this->drop_lowest_count) {
            usort($scores, fn($a, $b) => ($a['score'] / $a['max']) <=> ($b['score'] / $b['max']));
            $toDrop = array_slice($scores, 0, $this->drop_lowest_count);

            foreach ($toDrop as $dropped) {
                $totalEarned -= $dropped['score'];
                $totalPossible -= $dropped['max'];
            }
        }

        $percentage = $totalPossible > 0 ? ($totalEarned / $totalPossible) * 100 : 0;

        return [
            'earned' => $totalEarned,
            'possible' => $totalPossible,
            'percentage' => round($percentage, 2),
            'weighted' => round($percentage * ($this->weight / 100), 2),
        ];
    }
}
