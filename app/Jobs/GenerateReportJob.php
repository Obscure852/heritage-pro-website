<?php

namespace App\Jobs;

use App\Exports\Lms\LmsReportExport;
use App\Models\Lms\GeneratedReport;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\ActivityLog;
use App\Models\Lms\EngagementSummary;
use App\Models\Lms\QuizAttempt;
use App\Models\Lms\ContentAnalytics;
use App\Models\Lms\QuizAnalytics;
use App\Models\Lms\StudentInsight;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class GenerateReportJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    protected const REPORT_METHODS = [
        'course_progress' => 'getCourseProgressData',
        'engagement' => 'getEngagementData',
        'grades' => 'getGradesData',
        'completion' => 'getCompletionData',
        'quiz_performance' => 'getQuizPerformanceData',
        'content_usage' => 'getContentUsageData',
        'time_tracking' => 'getTimeTrackingData',
        'at_risk' => 'getAtRiskData',
        'custom' => 'getCustomData',
    ];

    public function __construct(
        protected GeneratedReport $report
    ) {}

    public function handle(): void {
        try {
            $this->report->markProcessing();

            Log::info('Starting report generation', [
                'report_id' => $this->report->id,
                'type' => $this->report->type,
                'format' => $this->report->format,
            ]);

            $reportType = $this->report->type;
            $format = $this->report->format;
            $parameters = $this->report->parameters ?? [];

            // Gather data based on report type
            $data = $this->gatherReportData($reportType, $parameters);

            if (empty($data)) {
                $data = [];
            }

            // Generate file in requested format
            $filePath = $this->generateFile($data, $reportType, $format);

            // Mark report as completed
            $this->report->markCompleted($filePath);

            Log::info('Report generated successfully', [
                'report_id' => $this->report->id,
                'type' => $reportType,
                'format' => $format,
                'file_path' => $filePath,
                'record_count' => count($data),
            ]);
        } catch (\Exception $e) {
            Log::error('Report generation failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->report->markFailed($e->getMessage());

            throw $e;
        }
    }

    protected function gatherReportData(string $reportType, array $parameters): array {
        if (!isset(self::REPORT_METHODS[$reportType])) {
            throw new \InvalidArgumentException("Unknown report type: {$reportType}");
        }

        $method = self::REPORT_METHODS[$reportType];
        return $this->$method($parameters);
    }

    protected function getCourseProgressData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;

        $query = Enrollment::with(['student:id,first_name,last_name,email', 'course:id,title']);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->get()->map(function ($enrollment) {
            return [
                'student_name' => $enrollment->student ? $enrollment->student->first_name . ' ' . $enrollment->student->last_name : 'Unknown',
                'email' => $enrollment->student->email ?? 'N/A',
                'course' => $enrollment->course->title ?? 'N/A',
                'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d') ?? 'N/A',
                'progress' => round($enrollment->progress_percentage ?? 0) . '%',
                'status' => ucfirst($enrollment->status ?? 'pending'),
                'completed_at' => $enrollment->completed_at?->format('Y-m-d') ?? 'N/A',
            ];
        })->toArray();
    }

    protected function getEngagementData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;
        $days = $parameters['days'] ?? 30;
        $startDate = now()->subDays($days);

        $query = EngagementSummary::with(['student:id,first_name,last_name,email'])
            ->where('date', '>=', $startDate)
            ->selectRaw('student_id, SUM(total_time_seconds) as total_time, SUM(content_views) as views, SUM(quiz_attempts) as quizzes, MAX(date) as last_activity')
            ->groupBy('student_id');

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->get()->map(function ($summary) {
            $student = $summary->student;
            return [
                'student_name' => $student ? $student->first_name . ' ' . $student->last_name : 'Unknown',
                'email' => $student->email ?? 'N/A',
                'content_views' => $summary->views ?? 0,
                'quiz_attempts' => $summary->quizzes ?? 0,
                'total_time' => $this->formatDuration($summary->total_time ?? 0),
                'last_activity' => $summary->last_activity ?? 'Never',
            ];
        })->toArray();
    }

    protected function getGradesData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;

        $query = QuizAttempt::with(['student:id,first_name,last_name,email', 'quiz:id,title,passing_score']);

        if ($courseId) {
            $query->whereHas('quiz.contentItem.module', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        return $query->latest('submitted_at')->get()->map(function ($attempt) {
            $percentage = $attempt->max_score > 0
                ? round(($attempt->score / $attempt->max_score) * 100, 1)
                : 0;

            return [
                'student_name' => $attempt->student ? $attempt->student->first_name . ' ' . $attempt->student->last_name : 'Unknown',
                'email' => $attempt->student->email ?? 'N/A',
                'quiz_title' => $attempt->quiz->title ?? 'N/A',
                'score' => $attempt->score ?? 0,
                'max_score' => $attempt->max_score ?? 0,
                'percentage' => $percentage . '%',
                'grade_letter' => $this->getGradeLetter($percentage),
                'passed' => $attempt->passed ? 'Yes' : 'No',
                'submitted_at' => $attempt->submitted_at?->format('Y-m-d H:i') ?? 'N/A',
            ];
        })->toArray();
    }

    protected function getCompletionData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;

        $query = Enrollment::with(['student:id,first_name,last_name,email', 'course:id,title']);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->get()->map(function ($enrollment) {
            // Count completed content items for this enrollment
            $modulesCompleted = $enrollment->contentProgress()->where('status', 'completed')->count();
            $totalModules = $enrollment->course?->contentItems()?->count() ?? 0;

            return [
                'student_name' => $enrollment->student ? $enrollment->student->first_name . ' ' . $enrollment->student->last_name : 'Unknown',
                'email' => $enrollment->student->email ?? 'N/A',
                'course' => $enrollment->course->title ?? 'N/A',
                'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d') ?? 'N/A',
                'progress' => round($enrollment->progress_percentage ?? 0) . '%',
                'modules_completed' => $modulesCompleted,
                'total_modules' => $totalModules,
                'status' => ucfirst($enrollment->status ?? 'pending'),
                'completed_at' => $enrollment->completed_at?->format('Y-m-d') ?? 'N/A',
            ];
        })->toArray();
    }

    protected function getQuizPerformanceData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;
        $days = $parameters['days'] ?? 30;

        $query = QuizAnalytics::with('quiz:id,title')
            ->where('date', '>=', now()->subDays($days))
            ->selectRaw('quiz_id, SUM(attempts) as total_attempts, SUM(completions) as completions, SUM(passes) as passes, AVG(avg_score) as avg_score')
            ->groupBy('quiz_id');

        if ($courseId) {
            $query->whereHas('quiz.contentItem.module', fn($q) => $q->where('course_id', $courseId));
        }

        return $query->get()->map(function ($analytics) {
            $passRate = $analytics->total_attempts > 0
                ? round(($analytics->passes / $analytics->total_attempts) * 100, 1)
                : 0;

            return [
                'quiz_title' => $analytics->quiz->title ?? 'Unknown Quiz',
                'total_attempts' => $analytics->total_attempts ?? 0,
                'completions' => $analytics->completions ?? 0,
                'passes' => $analytics->passes ?? 0,
                'pass_rate' => $passRate . '%',
                'avg_score' => round($analytics->avg_score ?? 0, 1) . '%',
            ];
        })->toArray();
    }

    protected function getContentUsageData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;
        $days = $parameters['days'] ?? 30;

        $query = ContentAnalytics::with(['content:id,title,type', 'content.module:id,title'])
            ->where('date', '>=', now()->subDays($days))
            ->selectRaw('content_id, SUM(views) as total_views, SUM(unique_views) as unique_views, SUM(completions) as completions, AVG(avg_time_seconds) as avg_time, AVG(completion_rate) as completion_rate')
            ->groupBy('content_id');

        if ($courseId) {
            $query->whereHas('content.module', fn($q) => $q->where('course_id', $courseId));
        }

        return $query->get()->map(function ($analytics) {
            return [
                'module' => $analytics->content->module->title ?? 'Unknown',
                'content_title' => $analytics->content->title ?? 'Unknown',
                'content_type' => ucfirst($analytics->content->type ?? 'unknown'),
                'total_views' => $analytics->total_views ?? 0,
                'unique_viewers' => $analytics->unique_views ?? 0,
                'avg_time' => $this->formatDuration($analytics->avg_time ?? 0),
                'completion_rate' => round($analytics->completion_rate ?? 0, 1) . '%',
            ];
        })->toArray();
    }

    protected function getTimeTrackingData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;
        $days = $parameters['days'] ?? 30;

        $query = EngagementSummary::with(['student:id,first_name,last_name,email'])
            ->where('date', '>=', now()->subDays($days))
            ->selectRaw('student_id, SUM(total_time_seconds) as total_time, COUNT(DISTINCT date) as active_days, MAX(date) as last_access')
            ->groupBy('student_id');

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->get()->map(function ($summary) {
            $avgSessionTime = $summary->active_days > 0
                ? $summary->total_time / $summary->active_days
                : 0;

            return [
                'student_name' => $summary->student ? $summary->student->first_name . ' ' . $summary->student->last_name : 'Unknown',
                'email' => $summary->student->email ?? 'N/A',
                'total_time' => $this->formatDuration($summary->total_time ?? 0),
                'avg_session' => $this->formatDuration($avgSessionTime),
                'active_days' => $summary->active_days ?? 0,
                'last_access' => $summary->last_access ?? 'Never',
            ];
        })->toArray();
    }

    protected function getAtRiskData(array $parameters): array {
        $courseId = $parameters['course_id'] ?? null;

        $query = StudentInsight::with(['student:id,first_name,last_name,email', 'course:id,title'])
            ->whereIn('insight_type', ['at_risk', 'inactive', 'struggling'])
            ->where('is_dismissed', false);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->get()->map(function ($insight) {
            return [
                'student_name' => $insight->student ? $insight->student->first_name . ' ' . $insight->student->last_name : 'Unknown',
                'email' => $insight->student->email ?? 'N/A',
                'course' => $insight->course->title ?? 'All Courses',
                'risk_type' => ucfirst(str_replace('_', ' ', $insight->insight_type ?? 'unknown')),
                'severity' => ucfirst($insight->severity ?? 'medium'),
                'description' => $insight->description ?? 'N/A',
                'generated_at' => $insight->generated_at?->format('Y-m-d') ?? 'N/A',
            ];
        })->toArray();
    }

    protected function getCustomData(array $parameters): array {
        // Custom reports use dynamic columns from parameters
        $columns = $parameters['columns'] ?? ['student_name', 'email', 'progress'];
        $courseId = $parameters['course_id'] ?? null;

        $query = Enrollment::with(['student:id,first_name,last_name,email', 'course:id,title']);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->get()->map(function ($enrollment) use ($columns) {
            $row = [];
            foreach ($columns as $column) {
                $row[$column] = match($column) {
                    'student_name' => $enrollment->student ? $enrollment->student->first_name . ' ' . $enrollment->student->last_name : 'Unknown',
                    'email' => $enrollment->student->email ?? 'N/A',
                    'course' => $enrollment->course->title ?? 'N/A',
                    'progress' => round($enrollment->progress_percentage ?? 0) . '%',
                    'status' => ucfirst($enrollment->status ?? 'pending'),
                    'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d') ?? 'N/A',
                    'completed_at' => $enrollment->completed_at?->format('Y-m-d') ?? 'N/A',
                    default => 'N/A',
                };
            }
            return $row;
        })->toArray();
    }

    protected function generateFile(array $data, string $reportType, string $format): string {
        $filename = $this->generateFilename($reportType, $format);
        $relativePath = "reports/{$filename}";

        // Ensure directory exists
        Storage::disk('local')->makeDirectory('reports');

        return match ($format) {
            'pdf' => $this->generatePdf($data, $reportType, $relativePath),
            'csv' => $this->generateCsv($data, $reportType, $relativePath),
            'xlsx' => $this->generateXlsx($data, $reportType, $relativePath),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    protected function generateFilename(string $reportType, string $format): string {
        $timestamp = now()->format('Y-m-d_His');
        $uniqueId = substr(md5(uniqid()), 0, 8);
        return "{$reportType}_report_{$timestamp}_{$uniqueId}.{$format}";
    }

    protected function generatePdf(array $data, string $reportType, string $relativePath): string {
        $pdf = Pdf::loadView('exports.lms.report-pdf', [
            'data' => $data,
            'reportType' => $reportType,
            'title' => $this->report->name ?? $this->getReportTitle($reportType),
            'generatedAt' => now()->format('F j, Y \a\t g:i A'),
            'school' => config('app.name', 'Learning Management System'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        Storage::disk('local')->put($relativePath, $pdf->output());

        return $relativePath;
    }

    protected function generateCsv(array $data, string $reportType, string $relativePath): string {
        $export = new LmsReportExport(
            $data,
            $reportType,
            null,
            $this->report->name ?? $this->getReportTitle($reportType)
        );

        Excel::store($export, $relativePath, 'local', \Maatwebsite\Excel\Excel::CSV);

        return $relativePath;
    }

    protected function generateXlsx(array $data, string $reportType, string $relativePath): string {
        $export = new LmsReportExport(
            $data,
            $reportType,
            null,
            $this->report->name ?? $this->getReportTitle($reportType)
        );

        Excel::store($export, $relativePath, 'local', \Maatwebsite\Excel\Excel::XLSX);

        return $relativePath;
    }

    protected function getReportTitle(string $reportType): string {
        return match ($reportType) {
            'course_progress' => 'Course Progress Report',
            'engagement' => 'Engagement Metrics Report',
            'grades' => 'Grades Report',
            'completion' => 'Completion Status Report',
            'quiz_performance' => 'Quiz Performance Report',
            'content_usage' => 'Content Usage Report',
            'time_tracking' => 'Time Tracking Report',
            'at_risk' => 'At-Risk Students Report',
            'custom' => 'Custom Report',
            default => 'LMS Report',
        };
    }

    protected function getGradeLetter(float $percentage): string {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F',
        };
    }

    protected function formatDuration(int $seconds): string {
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours . 'h ' . $mins . 'm';
    }

    public function failed(\Throwable $exception): void {
        Log::error('GenerateReportJob failed permanently', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);

        $this->report->markFailed($exception->getMessage());
    }
}
