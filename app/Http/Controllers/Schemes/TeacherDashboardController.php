<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Models\Schemes\LessonPlan;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TeacherDashboardController extends Controller {
    /**
     * Display the teacher dashboard with current-term schemes, current week, and upcoming plans.
     */
    public function index(): View {
        $user = auth()->user();
        $userId = $user->id;

        $currentTerm = Term::currentOrLastActiveTerm();
        $currentTermId = $currentTerm->id;

        // Current week calculation — guard against pre-term start
        $termStart = Carbon::parse($currentTerm->start_date);
        $today = Carbon::today();
        $currentWeek = $termStart->lte($today)
            ? (int) floor($termStart->diffInDays($today) / 7) + 1
            : 1;

        // Teacher's schemes for current term (cached 5 min, keyed by user+term)
        $cacheKey = "teacher_schemes_{$userId}_{$currentTermId}";
        $schemes = Cache::remember($cacheKey, 300, function () use ($userId, $currentTermId) {
            return SchemeOfWork::with([
                    'entries' => fn ($q) => $q->orderBy('week_number'),
                    'entries.lessonPlans:id,scheme_of_work_entry_id,date,status',
                    'klassSubject.gradeSubject.subject',
                    'klassSubject.klass:id,name',
                    'optionalSubject.gradeSubject.subject',
                    'term:id,term,year',
                ])
                ->where('teacher_id', $userId)
                ->where('term_id', $currentTermId)
                ->orderBy('created_at', 'desc')
                ->get();
        });

        // Upcoming lesson plans — not cached as they change frequently
        $upcomingLessonPlans = LessonPlan::with(['entry:id,week_number,topic'])
            ->where('teacher_id', $userId)
            ->where('date', '>=', today())
            ->where('status', 'planned')
            ->orderBy('date')
            ->limit(10)
            ->get();

        return view('schemes.teacher.dashboard', compact('schemes', 'currentTerm', 'currentWeek', 'upcomingLessonPlans'));
    }
}
