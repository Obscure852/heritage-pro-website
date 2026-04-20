<?php

namespace App\Services;

use App\Models\Lms\Achievement;
use App\Models\Lms\Badge;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\LeaderboardCache;
use App\Models\Lms\PointRule;
use App\Models\Lms\PointsTransaction;
use App\Models\Lms\StudentAchievement;
use App\Models\Lms\StudentPoints;
use App\Models\Student;

class GamificationService {
    public function awardPoints(
        Student $student,
        string $action,
        ?Course $course = null,
        $pointable = null,
        ?string $description = null,
        bool $includeBonus = false
    ): ?PointsTransaction {
        $points = PointRule::getPointsFor($action);
        
        if ($includeBonus) {
            $points += PointRule::getBonusFor($action);
        }

        if ($points <= 0) {
            return null;
        }

        $studentPoints = StudentPoints::getOrCreate($student->id, $course?->id);

        $transaction = $studentPoints->addPoints(
            $points,
            $action,
            $description ?? PointsTransaction::$typeLabels[$action] ?? $action,
            $pointable
        );

        // Also add to global points if course-specific
        if ($course) {
            $globalPoints = StudentPoints::getOrCreate($student->id, null);
            $globalPoints->addPoints($points, $action, $description, $pointable);
        }

        // Check achievements
        $this->checkAchievements($student, $action, $course);

        // Check for streak badges
        $this->checkStreakBadges($student, $studentPoints);

        return $transaction;
    }

    public function onCourseCompleted(Enrollment $enrollment): void {
        $student = $enrollment->student;
        $course = $enrollment->course;

        // Award completion points
        $this->awardPoints(
            $student,
            PointsTransaction::TYPE_COURSE_COMPLETE,
            $course,
            $enrollment,
            "Completed course: {$course->title}"
        );

        // Award course completion badge
        $this->awardBadgeBySlug($student, 'course-completer', $course);

        // Check for course milestones
        $completedCount = Enrollment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        if ($completedCount === 1) {
            $this->awardBadgeBySlug($student, 'first-course');
        } elseif ($completedCount === 5) {
            $this->awardBadgeBySlug($student, 'dedicated-learner');
        } elseif ($completedCount === 10) {
            $this->awardBadgeBySlug($student, 'knowledge-seeker');
        }

        // Update achievements
        $this->incrementAchievement($student, 'courses-completed', null);
    }

    public function onModuleCompleted(Student $student, Course $course, $module): void {
        $this->awardPoints(
            $student,
            PointsTransaction::TYPE_MODULE_COMPLETE,
            $course,
            $module,
            "Completed module: {$module->title}"
        );

        $this->incrementAchievement($student, 'modules-completed', $course);
    }

    public function onContentCompleted(Student $student, Course $course, $contentItem): void {
        $this->awardPoints(
            $student,
            PointsTransaction::TYPE_CONTENT_COMPLETE,
            $course,
            $contentItem,
            "Completed: {$contentItem->title}"
        );

        $this->incrementAchievement($student, 'content-completed', $course);
    }

    public function onQuizPassed(Student $student, $quizAttempt, bool $isPerfect = false): void {
        $quiz = $quizAttempt->quiz;
        $course = $quiz->contentItem?->module?->course;

        $this->awardPoints(
            $student,
            PointsTransaction::TYPE_QUIZ_PASS,
            $course,
            $quizAttempt,
            "Passed quiz: {$quiz->title}"
        );

        if ($isPerfect) {
            $this->awardPoints(
                $student,
                PointsTransaction::TYPE_QUIZ_PERFECT,
                $course,
                $quizAttempt,
                "Perfect score on: {$quiz->title}"
            );

            $this->awardBadgeBySlug($student, 'perfectionist', $course);
        }

        $this->incrementAchievement($student, 'quizzes-passed', $course);
    }

    public function onAssignmentSubmitted(Student $student, $submission): void {
        $assignment = $submission->assignment;
        $course = $assignment->contentItem?->module?->course;

        $this->awardPoints(
            $student,
            PointsTransaction::TYPE_ASSIGNMENT_SUBMIT,
            $course,
            $submission,
            "Submitted: {$assignment->title}"
        );

        $this->incrementAchievement($student, 'assignments-submitted', $course);
    }

    public function onAssignmentGraded(Student $student, $submission): void {
        $assignment = $submission->assignment;
        $course = $assignment->contentItem?->module?->course;

        // Check for excellent grade (90%+)
        if ($submission->score >= 90) {
            $this->awardPoints(
                $student,
                PointsTransaction::TYPE_ASSIGNMENT_EXCELLENT,
                $course,
                $submission,
                "Excellent grade on: {$assignment->title}"
            );

            $this->awardBadgeBySlug($student, 'high-achiever', $course);
        }
    }

    public function onDailyLogin(Student $student): void {
        $studentPoints = StudentPoints::getOrCreate($student->id, null);
        $today = now()->toDateString();

        // Only award once per day
        if ($studentPoints->last_activity_date?->toDateString() === $today) {
            return;
        }

        // First login ever
        $isFirstLogin = $studentPoints->last_activity_date === null;

        if ($isFirstLogin) {
            $this->awardPoints($student, PointsTransaction::TYPE_FIRST_LOGIN);
            $this->awardBadgeBySlug($student, 'newcomer');
        } else {
            $this->awardPoints($student, PointsTransaction::TYPE_DAILY_LOGIN);
        }

        $studentPoints->updateStreak();
        $studentPoints->save();

        $this->checkStreakBadges($student, $studentPoints);
    }

    public function awardBadge(Student $student, Badge $badge, ?Course $course = null, array $metadata = []): bool {
        if ($badge->isEarnedBy($student, $course)) {
            return false;
        }

        $badge->awardTo($student, $course, $metadata);

        // Award points for badge
        if ($badge->points_value > 0) {
            $studentPoints = StudentPoints::getOrCreate($student->id, $course?->id);
            $studentPoints->addPoints(
                $badge->points_value,
                PointsTransaction::TYPE_BADGE_EARNED,
                "Earned badge: {$badge->name}",
                $badge
            );
        }

        return true;
    }

    public function awardBadgeBySlug(Student $student, string $slug, ?Course $course = null): bool {
        $badge = Badge::where('slug', $slug)->where('is_active', true)->first();

        if (!$badge) {
            return false;
        }

        return $this->awardBadge($student, $badge, $course);
    }

    public function incrementAchievement(Student $student, string $slug, ?Course $course = null, int $amount = 1): void {
        $achievement = Achievement::where('slug', $slug)->where('is_active', true)->first();

        if (!$achievement) {
            return;
        }

        $studentAchievement = $achievement->checkProgress($student, $course);
        $studentAchievement->incrementProgress($amount);
    }

    protected function checkAchievements(Student $student, string $action, ?Course $course = null): void {
        // Map actions to achievement slugs
        $achievementMap = [
            PointsTransaction::TYPE_COURSE_COMPLETE => 'courses-completed',
            PointsTransaction::TYPE_MODULE_COMPLETE => 'modules-completed',
            PointsTransaction::TYPE_QUIZ_PASS => 'quizzes-passed',
            PointsTransaction::TYPE_CONTENT_COMPLETE => 'content-completed',
        ];

        if (isset($achievementMap[$action])) {
            $this->incrementAchievement($student, $achievementMap[$action], $course);
        }
    }

    protected function checkStreakBadges(Student $student, StudentPoints $studentPoints): void {
        $streak = $studentPoints->current_streak;

        $streakBadges = [
            7 => 'week-warrior',
            14 => 'fortnight-fighter',
            30 => 'monthly-master',
            100 => 'century-champion',
            365 => 'year-legend',
        ];

        foreach ($streakBadges as $days => $slug) {
            if ($streak >= $days) {
                $this->awardBadgeBySlug($student, $slug);
            }
        }

        // Award streak bonus points
        if ($streak > 0 && $streak % 7 === 0) {
            $bonus = min($streak, 100); // Cap at 100 bonus points
            $studentPoints->addPoints(
                $bonus,
                PointsTransaction::TYPE_STREAK_BONUS,
                "{$streak}-day streak bonus!"
            );
        }
    }

    public function getStudentStats(Student $student, ?Course $course = null): array {
        $points = StudentPoints::getOrCreate($student->id, $course?->id);

        $badgeQuery = $student->badges ?? collect();
        if ($course) {
            $badgeQuery = $badgeQuery->where('pivot.course_id', $course->id);
        }

        return [
            'total_points' => $points->total_points,
            'level' => $points->level,
            'level_title' => $points->level_title,
            'progress_to_next_level' => $points->progress_to_next_level,
            'xp_to_next_level' => $points->xp_to_next_level,
            'current_streak' => $points->current_streak,
            'longest_streak' => $points->longest_streak,
            'badges_count' => $badgeQuery->count(),
            'rank' => LeaderboardCache::getStudentRank($student->id, $course?->id),
        ];
    }

    public function refreshLeaderboards(): void {
        // Refresh global leaderboard
        LeaderboardCache::refreshLeaderboard(null, 'all_time');
        LeaderboardCache::refreshLeaderboard(null, 'weekly');
        LeaderboardCache::refreshLeaderboard(null, 'monthly');

        // Refresh per-course leaderboards
        $courseIds = Course::where('status', 'published')->pluck('id');
        foreach ($courseIds as $courseId) {
            LeaderboardCache::refreshLeaderboard($courseId, 'all_time');
        }
    }
}
