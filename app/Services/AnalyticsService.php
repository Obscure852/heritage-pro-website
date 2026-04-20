<?php

namespace App\Services;

use App\Models\Lms\ActivityLog;
use App\Models\Lms\ContentAnalytics;
use App\Models\Lms\Course;
use App\Models\Lms\CourseAnalytics;
use App\Models\Lms\Enrollment;
use App\Models\Lms\EngagementSummary;
use App\Models\Lms\LearningPathAnalytics;
use App\Models\Lms\QuizAnalytics;
use App\Models\Lms\QuizAttempt;
use App\Models\Lms\StudentInsight;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService {
    public function aggregateDailyEngagement(int $studentId, ?int $courseId, Carbon $date): EngagementSummary {
        $query = ActivityLog::query()
            ->where('student_id', $studentId)
            ->whereDate('created_at', $date);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $activities = $query->get();

        $data = [
            'student_id' => $studentId,
            'course_id' => $courseId,
            'date' => $date->toDateString(),
            'total_time_seconds' => $activities->sum('duration_seconds') ?? 0,
            'content_views' => $activities->where('activity_type', 'content_view')->count(),
            'quiz_attempts' => $activities->where('activity_type', 'quiz_submit')->count(),
            'assignment_submissions' => $activities->where('activity_type', 'assignment_submit')->count(),
            'discussion_posts' => $activities->whereIn('activity_type', ['discussion_post', 'discussion_reply'])->count(),
            'videos_watched' => $activities->where('activity_type', 'video_complete')->count(),
            'login_count' => $activities->where('activity_type', 'login')->count(),
        ];

        return EngagementSummary::updateOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId, 'date' => $date->toDateString()],
            $data
        );
    }

    public function aggregateCourseAnalytics(int $courseId, Carbon $date, string $period = 'daily'): CourseAnalytics {
        $course = Course::with(['enrollments', 'modules.content'])->findOrFail($courseId);

        $dateRange = $this->getDateRange($date, $period);

        // Get activity data for the period
        $activityData = ActivityLog::where('course_id', $courseId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->get();

        // Active students (had activity in period)
        $activeStudents = $activityData->pluck('student_id')->unique()->count();

        // New enrollments
        $newEnrollments = Enrollment::where('course_id', $courseId)
            ->whereBetween('enrolled_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        // Completions
        $completions = Enrollment::where('course_id', $courseId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        // Average progress
        $avgProgress = Enrollment::where('course_id', $courseId)
            ->where('status', '!=', 'dropped')
            ->avg('progress') ?? 0;

        // Time tracking
        $totalTime = $activityData->sum('duration_seconds') ?? 0;
        $avgTimePerStudent = $activeStudents > 0 ? $totalTime / $activeStudents : 0;

        // Activity counts
        $contentViews = $activityData->where('activity_type', 'content_view')->count();
        $quizAttempts = $activityData->where('activity_type', 'quiz_submit')->count();
        $assignmentSubmissions = $activityData->where('activity_type', 'assignment_submit')->count();

        // Get actual quiz passes from QuizAttempt model
        $quizData = QuizAttempt::whereHas('quiz.contentItem.module', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })
            ->whereBetween('submitted_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as total_attempts,
                SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passes,
                AVG(percentage) as avg_grade
            ')
            ->first();

        $quizPasses = $quizData->passes ?? 0;
        $avgQuizGrade = $quizData->avg_grade ?? null;
        $discussionPosts = $activityData->whereIn('activity_type', ['discussion_post', 'discussion_reply'])->count();

        // Calculate engagement score
        $engagementScore = $this->calculateEngagementScore([
            'active_rate' => $course->enrollments->count() > 0 ? ($activeStudents / $course->enrollments->count()) * 100 : 0,
            'content_views' => $contentViews,
            'quiz_attempts' => $quizAttempts,
            'discussions' => $discussionPosts,
        ]);

        // Completion funnel
        $completionFunnel = $this->buildCompletionFunnel($courseId);

        return CourseAnalytics::updateOrCreate(
            ['course_id' => $courseId, 'date' => $date->toDateString(), 'period' => $period],
            [
                'total_enrollments' => $course->enrollments->count(),
                'active_students' => $activeStudents,
                'new_enrollments' => $newEnrollments,
                'completions' => $completions,
                'avg_progress' => round($avgProgress, 2),
                'total_time_spent' => $totalTime,
                'avg_time_per_student' => round($avgTimePerStudent, 2),
                'content_views' => $contentViews,
                'quiz_attempts' => $quizAttempts,
                'quiz_passes' => $quizPasses,
                'assignment_submissions' => $assignmentSubmissions,
                'discussion_posts' => $discussionPosts,
                'engagement_score' => round($engagementScore, 2),
                'completion_funnel' => $completionFunnel,
            ]
        );
    }

    public function aggregateContentAnalytics(int $contentId, Carbon $date): ContentAnalytics {
        $activities = ActivityLog::where('subject_id', $contentId)
            ->where('subject_type', 'App\\Models\\Lms\\ContentItem')
            ->whereDate('created_at', $date)
            ->get();

        $views = $activities->where('activity_type', 'content_view')->count();
        $uniqueViews = $activities->where('activity_type', 'content_view')->pluck('student_id')->unique()->count();
        $completions = $activities->where('activity_type', 'content_complete')->count();
        $totalTime = $activities->sum('duration_seconds') ?? 0;
        $avgTime = $views > 0 ? $totalTime / $views : 0;
        $dropOffs = $views - $completions;

        return ContentAnalytics::updateOrCreate(
            ['content_id' => $contentId, 'date' => $date->toDateString()],
            [
                'views' => $views,
                'unique_views' => $uniqueViews,
                'completions' => $completions,
                'total_time_seconds' => $totalTime,
                'avg_time_seconds' => round($avgTime, 2),
                'completion_rate' => $views > 0 ? round(($completions / $views) * 100, 2) : 0,
                'drop_off_count' => max(0, $dropOffs),
            ]
        );
    }

    public function generateStudentInsights(int $studentId, ?int $courseId = null): Collection {
        $insights = collect();

        // Get recent engagement
        $recentEngagement = EngagementSummary::where('student_id', $studentId)
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->where('date', '>=', now()->subDays(7))
            ->get();

        // Check for inactivity
        if ($recentEngagement->isEmpty()) {
            $insights->push($this->createInsight(
                $studentId,
                'inactive',
                $courseId,
                'warning',
                'No Recent Activity',
                'This student has not engaged with the course in the past 7 days.'
            ));
        }

        // Check for engagement drop
        $thisWeek = $recentEngagement->sum('total_time_seconds');
        $lastWeek = EngagementSummary::where('student_id', $studentId)
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->whereBetween('date', [now()->subDays(14), now()->subDays(7)])
            ->sum('total_time_seconds');

        if ($lastWeek > 0 && $thisWeek < ($lastWeek * 0.5)) {
            $insights->push($this->createInsight(
                $studentId,
                'engagement_drop',
                $courseId,
                'warning',
                'Engagement Declining',
                'Study time has dropped by more than 50% compared to last week.',
                ['this_week' => $thisWeek, 'last_week' => $lastWeek]
            ));
        }

        // Check enrollment progress for at-risk
        if ($courseId) {
            $enrollment = Enrollment::where('student_id', $studentId)
                ->where('course_id', $courseId)
                ->first();

            if ($enrollment) {
                // Calculate expected progress based on course duration
                $course = $enrollment->course;
                if ($course->start_date && $course->end_date) {
                    $totalDays = $course->start_date->diffInDays($course->end_date);
                    $elapsedDays = $course->start_date->diffInDays(now());
                    $expectedProgress = $totalDays > 0 ? min(100, ($elapsedDays / $totalDays) * 100) : 0;

                    if ($enrollment->progress < ($expectedProgress * 0.6)) {
                        $insights->push($this->createInsight(
                            $studentId,
                            'at_risk',
                            $courseId,
                            'critical',
                            'Behind Schedule',
                            'Student is significantly behind the expected progress.',
                            ['current_progress' => $enrollment->progress, 'expected_progress' => $expectedProgress]
                        ));
                    }
                }

                // Check for near completion
                if ($enrollment->progress >= 90 && $enrollment->status !== 'completed') {
                    $insights->push($this->createInsight(
                        $studentId,
                        'completion_near',
                        $courseId,
                        'info',
                        'Almost Complete',
                        'This student is close to completing the course.',
                        ['progress' => $enrollment->progress]
                    ));
                }
            }
        }

        return $insights;
    }

    protected function createInsight(
        int $studentId,
        string $type,
        ?int $courseId,
        string $severity,
        string $title,
        string $description,
        array $data = []
    ): StudentInsight {
        return StudentInsight::updateOrCreate(
            [
                'student_id' => $studentId,
                'insight_type' => $type,
                'course_id' => $courseId,
                'is_dismissed' => false,
            ],
            [
                'severity' => $severity,
                'title' => $title,
                'description' => $description,
                'data' => $data ?: null,
                'generated_at' => now(),
            ]
        );
    }

    protected function getDateRange(Carbon $date, string $period): array {
        return match($period) {
            'weekly' => [
                'start' => $date->copy()->startOfWeek(),
                'end' => $date->copy()->endOfWeek(),
            ],
            'monthly' => [
                'start' => $date->copy()->startOfMonth(),
                'end' => $date->copy()->endOfMonth(),
            ],
            default => [
                'start' => $date->copy()->startOfDay(),
                'end' => $date->copy()->endOfDay(),
            ],
        };
    }

    protected function calculateEngagementScore(array $metrics): float {
        $score = 0;
        $score += min($metrics['active_rate'] ?? 0, 100) * 0.4; // 40% weight
        $score += min(($metrics['content_views'] ?? 0) / 10, 1) * 20; // 20% weight
        $score += min(($metrics['quiz_attempts'] ?? 0) / 5, 1) * 25; // 25% weight
        $score += min(($metrics['discussions'] ?? 0) / 10, 1) * 15; // 15% weight

        return min($score, 100);
    }

    protected function buildCompletionFunnel(int $courseId): array {
        $course = Course::with(['modules' => fn($q) => $q->orderBy('order')])->find($courseId);

        if (!$course || $course->modules->isEmpty()) {
            return [];
        }

        $funnel = [];
        $totalEnrollments = $course->enrollments()->count();

        foreach ($course->modules as $index => $module) {
            // This would need proper progress tracking per module
            // Simplified version based on overall progress
            $completedCount = Enrollment::where('course_id', $courseId)
                ->where('progress', '>=', (($index + 1) / $course->modules->count()) * 100)
                ->count();

            $funnel[] = [
                'module' => $module->title,
                'completed' => $completedCount,
                'percentage' => $totalEnrollments > 0 ? round(($completedCount / $totalEnrollments) * 100, 1) : 0,
            ];
        }

        return $funnel;
    }

    public function getCourseOverview(int $courseId): array {
        $course = Course::with(['enrollments', 'modules.content'])->findOrFail($courseId);

        $recentAnalytics = CourseAnalytics::where('course_id', $courseId)
            ->where('period', 'daily')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        return [
            'course' => $course,
            'total_enrollments' => $course->enrollments->count(),
            'active_enrollments' => $course->enrollments->where('status', 'active')->count(),
            'completed' => $course->enrollments->where('status', 'completed')->count(),
            'average_progress' => round($course->enrollments->avg('progress') ?? 0, 1),
            'trends' => [
                'enrollments' => $recentAnalytics->pluck('new_enrollments', 'date'),
                'active_students' => $recentAnalytics->pluck('active_students', 'date'),
                'completions' => $recentAnalytics->pluck('completions', 'date'),
                'engagement' => $recentAnalytics->pluck('engagement_score', 'date'),
            ],
            'recent_analytics' => $recentAnalytics->first(),
        ];
    }

    public function getStudentOverview(int $studentId): array {
        $enrollments = Enrollment::with('course')
            ->where('student_id', $studentId)
            ->get();

        $recentActivity = ActivityLog::where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $engagementTrend = EngagementSummary::where('student_id', $studentId)
            ->whereNull('course_id')
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        $insights = StudentInsight::where('student_id', $studentId)
            ->active()
            ->orderByDesc('generated_at')
            ->get();

        return [
            'total_courses' => $enrollments->count(),
            'active_courses' => $enrollments->where('status', 'active')->count(),
            'completed_courses' => $enrollments->where('status', 'completed')->count(),
            'average_progress' => round($enrollments->avg('progress') ?? 0, 1),
            'total_time_this_month' => $engagementTrend->sum('total_time_seconds'),
            'recent_activity' => $recentActivity,
            'engagement_trend' => $engagementTrend,
            'insights' => $insights,
            'enrollments' => $enrollments,
        ];
    }
}
