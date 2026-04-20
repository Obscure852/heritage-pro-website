<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class LeaderboardCache extends Model {
    protected $table = 'lms_leaderboard_cache';

    protected $fillable = [
        'student_id',
        'course_id',
        'period',
        'rank',
        'points',
        'badges_count',
        'courses_completed',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'rank' => 'integer',
        'points' => 'integer',
        'badges_count' => 'integer',
        'courses_completed' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public const PERIOD_DAILY = 'daily';
    public const PERIOD_WEEKLY = 'weekly';
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_ALL_TIME = 'all_time';

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public static function refreshLeaderboard(?int $courseId = null, string $period = 'all_time'): void {
        $periodDates = self::getPeriodDates($period);

        // Build the query for points
        $query = DB::table('lms_student_points as sp')
            ->join('students as s', 's.id', '=', 'sp.student_id')
            ->select([
                'sp.student_id',
                DB::raw($courseId ? $courseId : 'NULL as course_id'),
                DB::raw("'{$period}' as period"),
                'sp.total_points as points',
            ]);

        if ($courseId) {
            $query->where('sp.course_id', $courseId);
        } else {
            $query->whereNull('sp.course_id');
        }

        // Get ordered results
        $results = $query->orderByDesc('sp.total_points')->get();

        // Delete existing cache for this period
        $deleteQuery = self::where('period', $period);
        if ($courseId) {
            $deleteQuery->where('course_id', $courseId);
        } else {
            $deleteQuery->whereNull('course_id');
        }
        if ($periodDates['start']) {
            $deleteQuery->where('period_start', $periodDates['start']);
        }
        $deleteQuery->delete();

        // Insert new rankings
        $rank = 0;
        $prevPoints = null;
        $actualRank = 0;

        foreach ($results as $result) {
            $actualRank++;
            if ($result->points !== $prevPoints) {
                $rank = $actualRank;
            }
            $prevPoints = $result->points;

            // Get additional stats
            $badgesCount = StudentBadge::where('student_id', $result->student_id)
                ->when($courseId, fn($q) => $q->where('course_id', $courseId))
                ->count();

            $coursesCompleted = Enrollment::where('student_id', $result->student_id)
                ->where('status', 'completed')
                ->count();

            self::create([
                'student_id' => $result->student_id,
                'course_id' => $courseId,
                'period' => $period,
                'rank' => $rank,
                'points' => $result->points,
                'badges_count' => $badgesCount,
                'courses_completed' => $coursesCompleted,
                'period_start' => $periodDates['start'],
                'period_end' => $periodDates['end'],
            ]);
        }
    }

    protected static function getPeriodDates(string $period): array {
        return match ($period) {
            'daily' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'weekly' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            'monthly' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            default => [
                'start' => null,
                'end' => null,
            ],
        };
    }

    public static function getLeaderboard(?int $courseId = null, string $period = 'all_time', int $limit = 100) {
        return self::with('student')
            ->when($courseId, fn($q) => $q->where('course_id', $courseId), fn($q) => $q->whereNull('course_id'))
            ->where('period', $period)
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    public static function getStudentRank(int $studentId, ?int $courseId = null, string $period = 'all_time'): ?int {
        $entry = self::where('student_id', $studentId)
            ->when($courseId, fn($q) => $q->where('course_id', $courseId), fn($q) => $q->whereNull('course_id'))
            ->where('period', $period)
            ->first();

        return $entry?->rank;
    }
}
