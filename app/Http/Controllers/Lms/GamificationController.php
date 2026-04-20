<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Achievement;
use App\Models\Lms\Badge;
use App\Models\Lms\Course;
use App\Models\Lms\LeaderboardCache;
use App\Models\Lms\PointsTransaction;
use App\Models\Lms\StudentBadge;
use App\Models\Lms\StudentPoints;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class GamificationController extends Controller {
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService) {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Student dashboard showing their gamification stats
     */
    public function dashboard(Request $request) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $courseId = $request->query('course_id');
        $course = $courseId ? Course::find($courseId) : null;

        $stats = $this->gamificationService->getStudentStats($student, $course);

        $badges = StudentBadge::with('badge')
            ->where('student_id', $student->id)
            ->when($course, fn($q) => $q->where('course_id', $course->id))
            ->orderByDesc('earned_at')
            ->get();

        $recentPoints = PointsTransaction::where('student_id', $student->id)
            ->when($course, fn($q) => $q->where('course_id', $course->id))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $achievements = Achievement::active()
            ->with(['studentAchievements' => fn($q) => $q->where('student_id', $student->id)])
            ->get();

        return view('lms.gamification.dashboard', compact(
            'student', 'course', 'stats', 'badges', 'recentPoints', 'achievements'
        ));
    }

    /**
     * View all badges
     */
    public function badges(Request $request) {
        $student = Auth::guard('student')->user();

        $earnedBadgeIds = $student 
            ? StudentBadge::where('student_id', $student->id)->pluck('badge_id')->toArray()
            : [];

        $badges = Badge::active()
            ->where(function ($q) use ($earnedBadgeIds) {
                $q->where('is_secret', false)
                    ->orWhereIn('id', $earnedBadgeIds);
            })
            ->orderBy('rarity')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($badge) use ($earnedBadgeIds) {
                $badge->is_earned = in_array($badge->id, $earnedBadgeIds);
                return $badge;
            });

        $badgesByCategory = $badges->groupBy('category');

        return view('lms.gamification.badges', compact('badges', 'badgesByCategory', 'earnedBadgeIds'));
    }

    /**
     * View single badge details
     */
    public function showBadge(Badge $badge) {
        $student = Auth::guard('student')->user();

        $earnedBy = StudentBadge::with('student')
            ->where('badge_id', $badge->id)
            ->orderByDesc('earned_at')
            ->limit(50)
            ->get();

        $isEarned = $student 
            ? StudentBadge::where('student_id', $student->id)->where('badge_id', $badge->id)->exists()
            : false;

        return view('lms.gamification.badge-detail', compact('badge', 'earnedBy', 'isEarned'));
    }

    /**
     * View leaderboard
     */
    public function leaderboard(Request $request) {
        $courseId = $request->query('course_id');
        $period = $request->query('period', 'all_time');

        $course = $courseId ? Course::find($courseId) : null;

        $leaderboard = LeaderboardCache::getLeaderboard($courseId, $period, 100);

        $student = Auth::guard('student')->user();
        $studentRank = $student 
            ? LeaderboardCache::getStudentRank($student->id, $courseId, $period)
            : null;

        $courses = Course::where('status', 'published')->orderBy('title')->get();

        return view('lms.gamification.leaderboard', compact(
            'leaderboard', 'course', 'period', 'studentRank', 'courses', 'student'
        ));
    }

    /**
     * View achievements list
     */
    public function achievements(Request $request) {
        $student = Auth::guard('student')->user();
        $courseId = $request->query('course_id');
        $course = $courseId ? Course::find($courseId) : null;

        $achievements = Achievement::active()
            ->orderBy('sort_order')
            ->get();

        if ($student) {
            $achievements = $achievements->map(function ($achievement) use ($student, $course) {
                $progress = $achievement->studentAchievements()
                    ->where('student_id', $student->id)
                    ->when($course, fn($q) => $q->where('course_id', $course->id))
                    ->first();

                $achievement->student_progress = $progress;
                return $achievement;
            });
        }

        $achievementsByType = $achievements->groupBy('type');

        return view('lms.gamification.achievements', compact(
            'achievements', 'achievementsByType', 'course', 'student'
        ));
    }

    /**
     * View points history
     */
    public function pointsHistory(Request $request) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        $courseId = $request->query('course_id');
        $type = $request->query('type');

        $transactions = PointsTransaction::where('student_id', $student->id)
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate(25);

        $stats = StudentPoints::getOrCreate($student->id, $courseId);

        return view('lms.gamification.points-history', compact('transactions', 'stats', 'courseId', 'type'));
    }

    // ===== Admin Methods =====

    /**
     * Admin: Manage badges
     */
    public function manageBadges() {
        Gate::authorize('manage-lms-content');

        $badges = Badge::orderBy('category')->orderBy('sort_order')->get();

        return view('lms.gamification.admin.badges', compact('badges'));
    }

    /**
     * Admin: Create badge
     */
    public function createBadge() {
        Gate::authorize('manage-lms-content');

        return view('lms.gamification.admin.badge-form');
    }

    /**
     * Admin: Store badge
     */
    public function storeBadge(Request $request) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:lms_badges,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'required|string|max:7',
            'category' => 'required|in:completion,achievement,streak,social,special',
            'rarity' => 'required|in:common,uncommon,rare,epic,legendary',
            'points_value' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_secret' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_secret'] = $request->boolean('is_secret');

        Badge::create($validated);

        return redirect()->route('lms.gamification.admin.badges')
            ->with('success', 'Badge created successfully.');
    }

    /**
     * Admin: Manually award badge to student
     */
    public function awardBadge(Request $request) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'badge_id' => 'required|exists:lms_badges,id',
            'student_id' => 'required|exists:students,id',
            'course_id' => 'nullable|exists:lms_courses,id',
        ]);

        $badge = Badge::findOrFail($validated['badge_id']);
        $student = \App\Models\Student::findOrFail($validated['student_id']);
        $course = $validated['course_id'] ? Course::find($validated['course_id']) : null;

        $awarded = $this->gamificationService->awardBadge($student, $badge, $course, [
            'source' => 'admin',
            'awarded_by' => Auth::id(),
        ]);

        if ($awarded) {
            return back()->with('success', "Badge '{$badge->name}' awarded to {$student->name}.");
        }

        return back()->with('info', 'Student already has this badge.');
    }

    /**
     * Admin: Adjust student points
     */
    public function adjustPoints(Request $request) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'nullable|exists:lms_courses,id',
            'points' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $student = \App\Models\Student::findOrFail($validated['student_id']);
        $course = $validated['course_id'] ? Course::find($validated['course_id']) : null;

        $studentPoints = StudentPoints::getOrCreate($student->id, $course?->id);
        $studentPoints->addPoints(
            $validated['points'],
            PointsTransaction::TYPE_ADMIN_ADJUSTMENT,
            $validated['reason']
        );

        return back()->with('success', "Points adjusted for {$student->name}.");
    }

    /**
     * Admin: Refresh leaderboards
     */
    public function refreshLeaderboards() {
        Gate::authorize('manage-lms-content');

        $this->gamificationService->refreshLeaderboards();

        return back()->with('success', 'Leaderboards refreshed.');
    }
}
