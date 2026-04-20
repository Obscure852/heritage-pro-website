<?php

namespace App\Http\Controllers\Invigilation;

use App\Http\Controllers\Controller;
use App\Models\Invigilation\InvigilationSeries;
use App\Services\Invigilation\InvigilationRosterService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class InvigilationReportController extends Controller
{
    public function __construct(protected InvigilationRosterService $invigilationRosterService)
    {
        $this->middleware('auth');
    }

    public function dailyIndex(Request $request)
    {
        return $this->renderDaily($request, $this->resolveSelectedSeries($request));
    }

    public function daily(Request $request, InvigilationSeries $series)
    {
        return $this->renderDaily($request, $series);
    }

    public function teacherIndex(Request $request)
    {
        return $this->renderTeacher($request, $this->resolveSelectedSeries($request));
    }

    public function teacher(Request $request, InvigilationSeries $series)
    {
        return $this->renderTeacher($request, $series);
    }

    public function publishedTeacherRoster(Request $request)
    {
        $seriesId = (int) $request->query('series_id', 0);

        if ($seriesId > 0) {
            $series = $this->orderedPublishedSeriesQuery()->find($seriesId);

            if (!$series) {
                $message = InvigilationSeries::query()->whereKey($seriesId)->exists()
                    ? 'Only published invigilation series are available on this staff page.'
                    : 'Select a valid published invigilation series.';

                return redirect()->route('invigilation.view.teacher-roster')->with('error', $message);
            }
        } else {
            $series = $this->orderedPublishedSeriesQuery()->first();
        }

        return $this->renderPublishedTeacher($request, $series);
    }

    public function roomIndex(Request $request)
    {
        return $this->renderRoom($request, $this->resolveSelectedSeries($request));
    }

    public function room(Request $request, InvigilationSeries $series)
    {
        return $this->renderRoom($request, $series);
    }

    public function conflictsIndex(Request $request)
    {
        return $this->renderConflicts($request, $this->resolveSelectedSeries($request));
    }

    public function conflicts(Request $request, InvigilationSeries $series)
    {
        return $this->renderConflicts($request, $series);
    }

    protected function renderDaily(Request $request, ?InvigilationSeries $series)
    {
        $layout = $this->resolveReportLayout($request);
        $rows = collect();
        $timetable = [
            'dates' => collect(),
            'time_slots' => collect(),
            'cells' => [],
        ];

        if ($series) {
            if ($request->query('format') === 'csv' || $layout === 'table') {
                $rows = $this->invigilationRosterService->buildDailyReport($series);
            }

            if ($layout === 'timetable') {
                $timetable = $this->invigilationRosterService->buildDailyTimetableMatrix($series);
            }
        }

        if ($request->query('format') === 'csv') {
            if (!$series) {
                return redirect()->route('invigilation.reports.daily.index')->with('error', 'Select an invigilation series first.');
            }

            return $this->csvResponse(
                'invigilation-daily-' . $series->id . '.csv',
                ['Date', 'Start', 'End', 'Subject', 'Grade', 'Venue', 'Group', 'Required', 'Assigned', 'Invigilators'],
                $rows->flatMap(function ($items) {
                    return collect($items)->map(fn ($row) => [
                        $row['date'],
                        $row['start_time'],
                        $row['end_time'],
                        $row['subject'],
                        $row['grade'],
                        $row['venue'],
                        $row['group'],
                        $row['required'],
                        $row['assigned'],
                        implode('; ', $row['invigilators']),
                    ]);
                })->all()
            );
        }

        return view('invigilation.reports.daily', [
            'series' => $series,
            'rows' => $rows,
            'layout' => $layout,
            'timetable' => $timetable,
            'seriesOptions' => $this->reportSeriesOptions(),
        ]);
    }

    protected function renderTeacher(Request $request, ?InvigilationSeries $series)
    {
        $layout = $this->resolveReportLayout($request);
        $rows = collect();
        $timetable = [
            'dates' => collect(),
            'time_slots' => collect(),
            'cells' => [],
        ];

        if ($series) {
            if ($request->query('format') === 'csv' || $layout === 'table') {
                $rows = $this->invigilationRosterService->buildTeacherReport($series);
            }

            if ($layout === 'timetable') {
                $timetable = $this->invigilationRosterService->buildTeacherTimetableMatrix($series);
            }
        }

        if ($request->query('format') === 'csv') {
            if (!$series) {
                return redirect()->route('invigilation.reports.teacher.index')->with('error', 'Select an invigilation series first.');
            }

            return $this->csvResponse(
                'invigilation-teacher-' . $series->id . '.csv',
                ['Teacher', 'Date', 'Start', 'End', 'Subject', 'Grade', 'Venue', 'Group', 'Locked', 'Source'],
                $rows->flatMap(function ($items, $teacher) {
                    return collect($items)->map(fn ($row) => [
                        $teacher,
                        $row['date'],
                        $row['start_time'],
                        $row['end_time'],
                        $row['subject'],
                        $row['grade'],
                        $row['venue'],
                        $row['group'],
                        $row['locked'] ? 'Yes' : 'No',
                        $row['source'],
                    ]);
                })->all()
            );
        }

        return view('invigilation.reports.teacher', [
            'series' => $series,
            'rows' => $rows,
            'layout' => $layout,
            'timetable' => $timetable,
            'seriesOptions' => $this->reportSeriesOptions(),
        ]);
    }

    protected function renderPublishedTeacher(Request $request, ?InvigilationSeries $series)
    {
        $layout = $this->resolveReportLayout($request);
        $rows = collect();
        $timetable = [
            'dates' => collect(),
            'time_slots' => collect(),
            'cells' => [],
        ];

        if ($series) {
            if ($layout === 'table') {
                $rows = $this->invigilationRosterService->buildTeacherReport($series);
            }

            if ($layout === 'timetable') {
                $timetable = $this->invigilationRosterService->buildTeacherTimetableMatrix($series);
            }
        }

        return view('invigilation.reports.teacher-published', [
            'series' => $series,
            'rows' => $rows,
            'layout' => $layout,
            'timetable' => $timetable,
            'seriesOptions' => $this->publishedReportSeriesOptions(),
        ]);
    }

    protected function renderRoom(Request $request, ?InvigilationSeries $series)
    {
        $layout = $this->resolveReportLayout($request);
        $rows = collect();
        $timetable = [
            'dates' => collect(),
            'time_slots' => collect(),
            'cells' => [],
        ];

        if ($series) {
            if ($request->query('format') === 'csv' || $layout === 'table') {
                $rows = $this->invigilationRosterService->buildRoomReport($series);
            }

            if ($layout === 'timetable') {
                $timetable = $this->invigilationRosterService->buildRoomTimetableMatrix($series);
            }
        }

        if ($request->query('format') === 'csv') {
            if (!$series) {
                return redirect()->route('invigilation.reports.room.index')->with('error', 'Select an invigilation series first.');
            }

            return $this->csvResponse(
                'invigilation-room-' . $series->id . '.csv',
                ['Venue', 'Date', 'Start', 'End', 'Subject', 'Grade', 'Group', 'Candidates', 'Required', 'Assigned', 'Invigilators'],
                $rows->flatMap(function ($items, $venue) {
                    return collect($items)->map(fn ($row) => [
                        $venue,
                        $row['date'],
                        $row['start_time'],
                        $row['end_time'],
                        $row['subject'],
                        $row['grade'],
                        $row['group'],
                        $row['candidate_count'],
                        $row['required'],
                        $row['assigned'],
                        implode('; ', $row['invigilators']),
                    ]);
                })->all()
            );
        }

        return view('invigilation.reports.room', [
            'series' => $series,
            'rows' => $rows,
            'layout' => $layout,
            'timetable' => $timetable,
            'seriesOptions' => $this->reportSeriesOptions(),
        ]);
    }

    protected function renderConflicts(Request $request, ?InvigilationSeries $series)
    {
        $rows = $series ? $this->invigilationRosterService->buildConflictReport($series) : collect();

        if ($request->query('format') === 'csv') {
            if (!$series) {
                return redirect()->route('invigilation.reports.conflicts.index')->with('error', 'Select an invigilation series first.');
            }

            return $this->csvResponse(
                'invigilation-conflicts-' . $series->id . '.csv',
                ['Category', 'Title', 'Detail'],
                $rows->map(fn ($row) => [$row['category'], $row['title'], $row['detail']])->all()
            );
        }

        return view('invigilation.reports.conflicts', [
            'series' => $series,
            'rows' => $rows,
            'seriesOptions' => $this->reportSeriesOptions(),
        ]);
    }

    protected function resolveSelectedSeries(Request $request): ?InvigilationSeries
    {
        $seriesId = (int) $request->query('series_id', 0);

        if ($seriesId > 0) {
            return InvigilationSeries::query()->with('term')->find($seriesId);
        }

        return $this->orderedSeriesQuery()->first();
    }

    protected function reportSeriesOptions(): Collection
    {
        return $this->orderedSeriesQuery()->get();
    }

    protected function publishedReportSeriesOptions(): Collection
    {
        return $this->orderedPublishedSeriesQuery()->get();
    }

    protected function orderedSeriesQuery()
    {
        return InvigilationSeries::query()
            ->select('invigilation_series.*')
            ->leftJoin('terms', 'terms.id', '=', 'invigilation_series.term_id')
            ->with('term')
            ->orderByRaw("CASE WHEN invigilation_series.status = 'published' THEN 0 WHEN invigilation_series.status = 'draft' THEN 1 ELSE 2 END")
            ->orderByDesc('terms.year')
            ->orderByDesc('terms.term')
            ->orderByDesc('invigilation_series.created_at');
    }

    protected function orderedPublishedSeriesQuery()
    {
        return InvigilationSeries::query()
            ->select('invigilation_series.*')
            ->leftJoin('terms', 'terms.id', '=', 'invigilation_series.term_id')
            ->with('term')
            ->where('invigilation_series.status', InvigilationSeries::STATUS_PUBLISHED)
            ->orderByDesc('terms.year')
            ->orderByDesc('terms.term')
            ->orderByDesc('invigilation_series.created_at');
    }

    protected function resolveReportLayout(Request $request): string
    {
        return in_array($request->query('layout'), ['timetable', 'table'], true)
            ? (string) $request->query('layout')
            : 'timetable';
    }

    protected function csvResponse(string $filename, array $header, array $rows): Response
    {
        $csvRows = collect([$header])
            ->merge(collect($rows))
            ->map(function (array $row): string {
                return collect($row)
                    ->map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"')
                    ->implode(',');
            })
            ->implode("\n");

        return response($csvRows, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
