<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateReportJob;
use App\Models\Lms\ActivityLog;
use App\Models\Lms\ContentAnalytics;
use App\Models\Lms\Course;
use App\Models\Lms\CourseAnalytics;
use App\Models\Lms\EngagementSummary;
use App\Models\Lms\GeneratedReport;
use App\Models\Lms\QuizAnalytics;
use App\Models\Lms\ReportDefinition;
use App\Models\Lms\StudentInsight;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class AnalyticsController extends Controller {
    public function __construct(protected AnalyticsService $analyticsService) {}

    // Course Analytics Dashboard
    public function courseDashboard(Course $course) {
        Gate::authorize('view-lms-analytics');

        $overview = $this->analyticsService->getCourseOverview($course->id);

        $dateRange = request('range', '30');
        $startDate = now()->subDays((int) $dateRange);

        // Get daily analytics for charts
        $dailyAnalytics = CourseAnalytics::where('course_id', $course->id)
            ->where('period', 'daily')
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        // Get content performance
        $contentAnalytics = ContentAnalytics::whereHas('content', function ($q) use ($course) {
            $q->whereHas('module', fn($m) => $m->where('course_id', $course->id));
        })
            ->where('date', '>=', $startDate)
            ->selectRaw('content_id, SUM(views) as total_views, SUM(completions) as total_completions, AVG(avg_time_seconds) as avg_time')
            ->groupBy('content_id')
            ->with('content')
            ->orderByDesc('total_views')
            ->take(10)
            ->get();

        // Get at-risk students
        $atRiskStudents = StudentInsight::where('course_id', $course->id)
            ->whereIn('insight_type', ['at_risk', 'inactive', 'struggling'])
            ->active()
            ->with('student')
            ->orderByDesc('generated_at')
            ->take(10)
            ->get();

        // Activity breakdown
        $activityBreakdown = ActivityLog::where('course_id', $course->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->orderByDesc('count')
            ->get();

        return view('lms.analytics.course-dashboard', compact(
            'course',
            'overview',
            'dailyAnalytics',
            'contentAnalytics',
            'atRiskStudents',
            'activityBreakdown',
            'dateRange'
        ));
    }

    // Student Analytics (Instructor View)
    public function studentAnalytics(Course $course) {
        Gate::authorize('view-lms-analytics');

        $enrollments = $course->enrollments()
            ->with(['student', 'progress'])
            ->get();

        // Get engagement data for all students
        $studentEngagement = EngagementSummary::where('course_id', $course->id)
            ->where('date', '>=', now()->subDays(30))
            ->get()
            ->groupBy('student_id');

        // Get insights for course students
        $insights = StudentInsight::where('course_id', $course->id)
            ->active()
            ->with('student')
            ->get()
            ->groupBy('student_id');

        return view('lms.analytics.student-analytics', compact(
            'course',
            'enrollments',
            'studentEngagement',
            'insights'
        ));
    }

    // Individual Student Detail
    public function studentDetail(Course $course, int $studentId) {
        Gate::authorize('view-lms-analytics');

        $enrollment = $course->enrollments()
            ->where('student_id', $studentId)
            ->with(['student', 'progress'])
            ->firstOrFail();

        $overview = $this->analyticsService->getStudentOverview($studentId);

        // Filter to this course
        $courseEngagement = EngagementSummary::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        $recentActivity = ActivityLog::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        $insights = StudentInsight::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->active()
            ->get();

        return view('lms.analytics.student-detail', compact(
            'course',
            'enrollment',
            'overview',
            'courseEngagement',
            'recentActivity',
            'insights'
        ));
    }

    // Content Analytics
    public function contentAnalytics(Course $course) {
        Gate::authorize('view-lms-analytics');

        $modules = $course->modules()->with(['content' => function ($q) {
            $q->withCount(['progress as views_count' => function ($q2) {
                $q2->where('is_completed', false);
            }, 'progress as completions_count' => function ($q2) {
                $q2->where('is_completed', true);
            }]);
        }])->orderBy('order')->get();

        // Get detailed analytics for each content item
        $contentStats = ContentAnalytics::whereHas('content', function ($q) use ($course) {
            $q->whereHas('module', fn($m) => $m->where('course_id', $course->id));
        })
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('content_id, SUM(views) as views, SUM(completions) as completions, AVG(avg_time_seconds) as avg_time, AVG(completion_rate) as completion_rate')
            ->groupBy('content_id')
            ->get()
            ->keyBy('content_id');

        return view('lms.analytics.content-analytics', compact('course', 'modules', 'contentStats'));
    }

    // Quiz Analytics
    public function quizAnalytics(Course $course) {
        Gate::authorize('view-lms-analytics');

        $quizzes = $course->quizzes()->with('module')->get();

        $quizStats = QuizAnalytics::whereIn('quiz_id', $quizzes->pluck('id'))
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('quiz_id, SUM(attempts) as attempts, SUM(completions) as completions, SUM(passes) as passes, AVG(avg_score) as avg_score')
            ->groupBy('quiz_id')
            ->get()
            ->keyBy('quiz_id');

        return view('lms.analytics.quiz-analytics', compact('course', 'quizzes', 'quizStats'));
    }

    // Engagement Analytics
    public function engagementAnalytics(Course $course) {
        Gate::authorize('view-lms-analytics');

        $dailyEngagement = EngagementSummary::where('course_id', $course->id)
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('date, SUM(total_time_seconds) as total_time, COUNT(DISTINCT student_id) as active_students, SUM(content_views) as content_views, SUM(quiz_attempts) as quiz_attempts')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Peak activity times
        $activityByHour = ActivityLog::where('course_id', $course->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Device breakdown
        $deviceBreakdown = ActivityLog::where('course_id', $course->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        return view('lms.analytics.engagement-analytics', compact(
            'course',
            'dailyEngagement',
            'activityByHour',
            'deviceBreakdown'
        ));
    }

    // Reports Index
    public function reports() {
        Gate::authorize('view-lms-analytics');

        $definitions = ReportDefinition::where('created_by', Auth::id())
            ->orWhere('is_public', true)
            ->with('creator')
            ->orderByDesc('created_at')
            ->get();

        $generatedReports = GeneratedReport::where('generated_by', Auth::id())
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return view('lms.analytics.reports', compact('definitions', 'generatedReports'));
    }

    // Create Report Definition
    public function createReport(Request $request) {
        Gate::authorize('view-lms-analytics');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(ReportDefinition::$reportTypes)),
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'schedule' => 'nullable|in:' . implode(',', array_keys(ReportDefinition::$schedules)),
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $definition = ReportDefinition::create($validated);
        return redirect()->route('lms.analytics.reports')->with('success', 'Report definition created.');
    }

    // Generate Report
    public function generateReport(Request $request) {
        Gate::authorize('view-lms-analytics');

        $validated = $request->validate([
            'definition_id' => 'nullable|exists:lms_report_definitions,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(ReportDefinition::$reportTypes)),
            'format' => 'required|in:pdf,csv,xlsx',
            'parameters' => 'nullable|array',
        ]);

        $report = GeneratedReport::create([
            'definition_id' => $validated['definition_id'] ?? null,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'format' => $validated['format'],
            'parameters' => $validated['parameters'] ?? null,
            'generated_by' => Auth::id(),
            'status' => 'pending',
        ]);

        // Dispatch the job to generate the report
        GenerateReportJob::dispatch($report);

        return redirect()->route('lms.analytics.reports')
            ->with('success', 'Report generation started. You will be notified when complete.');
    }

    // Download Report
    public function downloadReport(GeneratedReport $report) {
        Gate::authorize('view-lms-analytics');

        // Also verify the user owns this report
        if ($report->generated_by !== Auth::id()) {
            abort(403, 'You can only download your own reports.');
        }

        if ($report->status !== 'completed' || !$report->file_path) {
            return back()->with('error', 'Report is not ready for download.');
        }

        return response()->download(storage_path('app/' . $report->file_path));
    }

    // Delete Report
    public function deleteReport(GeneratedReport $report) {
        if ($report->generated_by !== Auth::id()) {
            return back()->with('error', 'You can only delete your own reports.');
        }

        // Delete the file if it exists
        if ($report->file_path && \Storage::exists($report->file_path)) {
            \Storage::delete($report->file_path);
        }

        $report->delete();

        return back()->with('success', 'Report deleted successfully.');
    }

    // Retry Report Generation
    public function retryReport(GeneratedReport $report) {
        if ($report->generated_by !== Auth::id()) {
            return back()->with('error', 'You can only retry your own reports.');
        }

        if (!in_array($report->status, ['pending', 'failed'])) {
            return back()->with('error', 'Only pending or failed reports can be retried.');
        }

        // Reset status to pending
        $report->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        // Dispatch the job to regenerate the report
        GenerateReportJob::dispatch($report);

        return back()->with('success', 'Report queued for regeneration.');
    }

    // Student Dashboard (Student's own view)
    public function myAnalytics() {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('lms.courses.index')
                ->with('error', 'Student profile not found.');
        }

        $overview = $this->analyticsService->getStudentOverview($student->id);

        // Monthly engagement summary
        $monthlyEngagement = EngagementSummary::where('student_id', $student->id)
            ->whereNull('course_id')
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        // Per-course breakdown
        $courseEngagement = EngagementSummary::where('student_id', $student->id)
            ->whereNotNull('course_id')
            ->where('date', '>=', now()->subDays(30))
            ->selectRaw('course_id, SUM(total_time_seconds) as total_time, SUM(content_views) as content_views')
            ->groupBy('course_id')
            ->with('course')
            ->get();

        return view('lms.analytics.my-analytics', compact(
            'overview',
            'monthlyEngagement',
            'courseEngagement'
        ));
    }

    // Dismiss insight
    public function dismissInsight(StudentInsight $insight) {
        $insight->dismiss();
        return back()->with('success', 'Insight dismissed.');
    }

    // Refresh analytics (manual trigger)
    public function refreshCourseAnalytics(Course $course) {
        Gate::authorize('view-lms-analytics');

        $this->analyticsService->aggregateCourseAnalytics($course->id, now(), 'daily');

        return back()->with('success', 'Analytics refreshed.');
    }
}
