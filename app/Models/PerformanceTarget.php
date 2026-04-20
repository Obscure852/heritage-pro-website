<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceTarget extends Model{
    use HasFactory;

    protected $fillable = [
        'academic_year',
        'exam_type',
        'high_achievement_target',
        'high_achievement_label',
        'pass_rate_target',
        'pass_rate_label',
        'non_failure_target',
        'non_failure_label',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'high_achievement_target' => 'decimal:2',
        'pass_rate_target' => 'decimal:2',
        'non_failure_target' => 'decimal:2',
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getGradingSystem($examType){
        $systems = [
            'JCE' => [
                'grades' => ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'],
                'pass_grades' => ['Merit', 'A', 'B', 'C'],
                'high_achievement' => ['Merit', 'A', 'B'],
                'categories' => [
                    'high_achievement' => ['label' => 'MAB %', 'grades' => ['Merit', 'A', 'B']],
                    'pass_rate' => ['label' => 'MABC %', 'grades' => ['Merit', 'A', 'B', 'C']],
                    'non_failure' => ['label' => 'MABCD %', 'grades' => ['Merit', 'A', 'B', 'C', 'D']]
                ]
            ],
            'BGCSE' => [
                'grades' => ['A', 'B', 'C', 'D', 'E', 'U'],
                'pass_grades' => ['A', 'B', 'C'],
                'high_achievement' => ['A', 'B'],
                'categories' => [
                    'high_achievement' => ['label' => 'AB %', 'grades' => ['A', 'B']],
                    'pass_rate' => ['label' => 'ABC %', 'grades' => ['A', 'B', 'C']],
                    'non_failure' => ['label' => 'ABCD %', 'grades' => ['A', 'B', 'C', 'D']]
                ]
            ],
            'PSLE' => [
                'grades' => ['A', 'B', 'C', 'D', 'E'],
                'pass_grades' => ['A', 'B', 'C'],
                'high_achievement' => ['A', 'B'],
                'categories' => [
                    'high_achievement' => ['label' => 'AB %', 'grades' => ['A', 'B']],
                    'pass_rate' => ['label' => 'ABC %', 'grades' => ['A', 'B', 'C']],
                    'non_failure' => ['label' => 'ABCD %', 'grades' => ['A', 'B', 'C', 'D']]
                ]
            ]
        ];

        return $systems[$examType] ?? $systems['JCE'];
    }

    public static function getTargetsForYear($year, $examType = 'JCE'){
        $targets = self::where('academic_year', $year)
                      ->where('exam_type', $examType)
                      ->first();

        $gradingSystem = self::getGradingSystem($examType);

        if ($targets) {
            return [
                'high_achievement' => [
                    'target' => (float) $targets->high_achievement_target,
                    'label' => $targets->high_achievement_label,
                    'grades' => $gradingSystem['categories']['high_achievement']['grades']
                ],
                'pass_rate' => [
                    'target' => (float) $targets->pass_rate_target,
                    'label' => $targets->pass_rate_label,
                    'grades' => $gradingSystem['categories']['pass_rate']['grades']
                ],
                'non_failure' => [
                    'target' => (float) $targets->non_failure_target,
                    'label' => $targets->non_failure_label,
                    'grades' => $gradingSystem['categories']['non_failure']['grades']
                ]
            ];
        }
        return self::getDefaultTargets($examType);
    }

    public static function getDefaultTargets($examType = 'JCE'){
        $gradingSystem = self::getGradingSystem($examType);
        
        $defaults = [
            'JCE' => [
                'high_achievement' => ['target' => 25.0, 'label' => 'MAB %'],
                'pass_rate' => ['target' => 65.0, 'label' => 'MABC %'],
                'non_failure' => ['target' => 85.0, 'label' => 'MABCD %'],
            ],
            'BGCSE' => [
                'high_achievement' => ['target' => 30.0, 'label' => 'AB %'],
                'pass_rate' => ['target' => 70.0, 'label' => 'ABC %'],
                'non_failure' => ['target' => 90.0, 'label' => 'ABCD %'],
            ],
            'PSLE' => [
                'high_achievement' => ['target' => 35.0, 'label' => 'AB %'],
                'pass_rate' => ['target' => 75.0, 'label' => 'ABC %'],
                'non_failure' => ['target' => 95.0, 'label' => 'ABCD %'],
            ]
        ];

        $examDefaults = $defaults[$examType] ?? $defaults['JCE'];
        foreach ($examDefaults as $category => &$data) {
            $data['grades'] = $gradingSystem['categories'][$category]['grades'];
        }

        return $examDefaults;
    }


    public static function setTargetsForYear($year, $examType, $highAchievement, $passRate, $nonFailure, $notes = null, $userId = null){
        $gradingSystem = self::getGradingSystem($examType);
        
        return self::updateOrCreate(
            [
                'academic_year' => $year,
                'exam_type' => $examType
            ],
            [
                'high_achievement_target' => $highAchievement,
                'high_achievement_label' => $gradingSystem['categories']['high_achievement']['label'],
                'pass_rate_target' => $passRate,
                'pass_rate_label' => $gradingSystem['categories']['pass_rate']['label'],
                'non_failure_target' => $nonFailure,
                'non_failure_label' => $gradingSystem['categories']['non_failure']['label'],
                'notes' => $notes,
                'updated_by' => $userId,
                'created_by' => $userId,
            ]
        );
    }


    public static function calculateActualPerformance($examResults, $examType){
        $totalStudents = $examResults->count();
        
        if ($totalStudents === 0) {
            return null;
        }

        $gradingSystem = self::getGradingSystem($examType);
        $gradeCounts = [];
        foreach ($gradingSystem['grades'] as $grade) {
            $gradeCounts[$grade] = $examResults->where('overall_grade', $grade)->count();
        }

        $actual = [];
        foreach ($gradingSystem['categories'] as $category => $config) {
            $count = 0;
            foreach ($config['grades'] as $grade) {
                $count += $gradeCounts[$grade] ?? 0;
            }
            
            $actual[$category] = [
                'percentage' => round(($count / $totalStudents) * 100, 1),
                'count' => $count,
                'label' => $config['label'],
                'grades' => $config['grades']
            ];
        }

        return $actual;
    }

    public function scopeForAcademicYear($query, $year){
        return $query->where('academic_year', $year);
    }

    public function scopeForExamType($query, $examType){
        return $query->where('exam_type', $examType);
    }

    public function scopeJCE($query){
        return $query->where('exam_type', 'JCE');
    }

    public function scopeBGCSE($query){
        return $query->where('exam_type', 'BGCSE');
    }

    public function scopePSLE($query){
        return $query->where('exam_type', 'PSLE');
    }
}