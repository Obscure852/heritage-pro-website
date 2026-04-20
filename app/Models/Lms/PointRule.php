<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model {
    protected $table = 'lms_point_rules';

    protected $fillable = [
        'action',
        'name',
        'description',
        'points',
        'bonus_points',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'bonus_points' => 'integer',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    // Default point values
    public static array $defaults = [
        'course_complete' => ['name' => 'Course Completion', 'points' => 500, 'bonus' => 100],
        'module_complete' => ['name' => 'Module Completion', 'points' => 50, 'bonus' => 0],
        'content_complete' => ['name' => 'Content Item Completion', 'points' => 10, 'bonus' => 0],
        'quiz_pass' => ['name' => 'Quiz Passed', 'points' => 25, 'bonus' => 25],
        'quiz_perfect' => ['name' => 'Perfect Quiz Score', 'points' => 50, 'bonus' => 0],
        'assignment_submit' => ['name' => 'Assignment Submitted', 'points' => 20, 'bonus' => 0],
        'assignment_excellent' => ['name' => 'Excellent Assignment Grade', 'points' => 50, 'bonus' => 0],
        'first_login' => ['name' => 'First Login', 'points' => 10, 'bonus' => 0],
        'daily_login' => ['name' => 'Daily Login', 'points' => 5, 'bonus' => 0],
        'streak_7' => ['name' => '7-Day Streak', 'points' => 50, 'bonus' => 0],
        'streak_30' => ['name' => '30-Day Streak', 'points' => 200, 'bonus' => 0],
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public static function getPointsFor(string $action): int {
        $rule = self::where('action', $action)->where('is_active', true)->first();
        
        if ($rule) {
            return $rule->points;
        }

        return self::$defaults[$action]['points'] ?? 0;
    }

    public static function getBonusFor(string $action): int {
        $rule = self::where('action', $action)->where('is_active', true)->first();
        
        if ($rule) {
            return $rule->bonus_points;
        }

        return self::$defaults[$action]['bonus'] ?? 0;
    }

    public static function seedDefaults(): void {
        foreach (self::$defaults as $action => $config) {
            self::firstOrCreate(
                ['action' => $action],
                [
                    'name' => $config['name'],
                    'points' => $config['points'],
                    'bonus_points' => $config['bonus'],
                    'is_active' => true,
                ]
            );
        }
    }
}
