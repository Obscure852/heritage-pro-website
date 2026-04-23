<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\AttendanceClockRequest;
use App\Http\Requests\Crm\AttendanceCorrectionRequest;
use App\Http\Requests\Crm\AttendanceCorrectionReviewRequest;
use App\Models\CrmAttendanceCode;
use App\Models\CrmAttendanceCorrection;
use App\Models\CrmAttendanceRecord;
use App\Models\CrmUserDepartment;
use App\Services\Crm\AttendanceClockService;
use App\Services\Crm\AttendanceGridService;
use App\Services\Crm\AttendanceNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends CrmController
{
    public function __construct(
        private readonly AttendanceClockService $clockService,
        private readonly AttendanceGridService $gridService,
        private readonly AttendanceNotificationService $notificationService
    ) {
    }

    public function clock(AttendanceClockRequest $request): JsonResponse
    {
        $result = $this->clockService->toggle(
            $this->crmUser(),
            $request->validated('note')
        );

        if ($result['action'] === 'clocked_in' && $result['record']->is_late) {
            $this->notificationService->notifyLateArrival($result['record']->load('user'));
        }

        return response()->json([
            'status' => $result['action'],
            'message' => $result['message'],
            'clocked_in_at' => $result['record']->clocked_in_at?->format('H:i'),
            'clocked_out_at' => $result['record']->clocked_out_at?->format('H:i'),
            'total_minutes' => $result['record']->total_minutes,
            'elapsed_minutes' => $result['action'] === 'clocked_in'
                ? (int) $result['record']->clocked_in_at->diffInMinutes(now())
                : ($result['record']->total_minutes ?? 0),
            'code' => $result['record']->code?->code,
            'is_late' => $result['record']->is_late,
        ]);
    }

    public function clockStatus(): JsonResponse
    {
        $status = $this->clockService->currentStatus($this->crmUser());

        return response()->json([
            'state' => $status['state'],
            'clocked_in_at' => $status['clocked_in_at']?->format('H:i'),
            'elapsed_minutes' => $status['elapsed_minutes'],
        ]);
    }

    public function grid(Request $request): View
    {
        $this->authorizeModuleAccess('attendance', 'view');

        $crmUser = $this->crmUser();

        abort_if($crmUser->isRep(), 403, 'Sales representatives cannot access the team attendance grid.');
        $now = now();

        $weekOffset = (int) $request->query('week', 0);
        $gridStart = $now->copy()->startOfWeek()->addWeeks($weekOffset);
        $gridEnd = $gridStart->copy()->addDays(13);

        $filters = [
            'department_ids' => array_filter((array) $request->query('department_ids', [])),
            'search' => trim((string) $request->query('search', '')),
            'code_ids' => array_filter((array) $request->query('code_ids', [])),
            'show_weekends' => $request->query('show_weekends', '1') === '1',
        ];

        $teamGrid = $this->gridService->buildTeamGrid($crmUser, $gridStart, $gridEnd, $filters);

        $departments = CrmUserDepartment::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $codes = CrmAttendanceCode::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $canEdit = $crmUser->canAccessCrmModule('attendance', 'edit');

        return view('crm.attendance.grid', compact(
            'teamGrid',
            'departments',
            'codes',
            'filters',
            'gridStart',
            'gridEnd',
            'weekOffset',
            'canEdit',
            'now'
        ));
    }

    public function recordShow(CrmAttendanceRecord $record): JsonResponse
    {
        $this->authorizeModuleAccess('attendance', 'view');

        $crmUser = $this->crmUser();

        if ($crmUser->isManager() && ! $crmUser->isAdmin()) {
            abort_unless(
                $record->user && (int) $record->user->department_id === (int) $crmUser->department_id,
                403
            );
        }

        $record->load(['code', 'user', 'corrections.requester', 'corrections.proposedCode']);

        return response()->json([
            'id' => $record->id,
            'user_name' => $record->user?->name,
            'date' => $record->date->format('D, d M Y'),
            'code' => $record->code ? [
                'id' => $record->code->id,
                'code' => $record->code->code,
                'label' => $record->code->label,
                'color' => $record->code->color,
            ] : null,
            'clocked_in_at' => $record->clocked_in_at?->format('H:i'),
            'clocked_out_at' => $record->clocked_out_at?->format('H:i'),
            'total_minutes' => $record->total_minutes,
            'source' => $record->source,
            'clock_in_note' => $record->clock_in_note,
            'clock_out_note' => $record->clock_out_note,
            'is_late' => $record->is_late,
            'is_early_out' => $record->is_early_out,
            'auto_closed' => $record->auto_closed,
            'overtime_minutes' => $record->overtime_minutes,
            'status' => $record->status,
            'pending_corrections' => $record->corrections->where('status', 'pending')->count(),
        ]);
    }

    public function recordUpdate(Request $request, CrmAttendanceRecord $record): JsonResponse
    {
        $this->authorizeModuleAccess('attendance', 'edit');

        $crmUser = $this->crmUser();

        if ($crmUser->isManager() && ! $crmUser->isAdmin()) {
            abort_unless(
                $record->user && (int) $record->user->department_id === (int) $crmUser->department_id,
                403
            );
        }

        $validated = $request->validate([
            'attendance_code_id' => ['required', 'exists:crm_attendance_codes,id'],
            'clocked_in_at' => ['nullable', 'date'],
            'clocked_out_at' => ['nullable', 'date'],
            'clock_in_note' => ['nullable', 'string', 'max:500'],
            'clock_out_note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($record, $validated, $crmUser) {
            CrmAttendanceCorrection::create([
                'attendance_record_id' => $record->id,
                'requested_by' => $crmUser->id,
                'original_values' => [
                    'attendance_code_id' => $record->attendance_code_id,
                    'clocked_in_at' => $record->clocked_in_at?->toDateTimeString(),
                    'clocked_out_at' => $record->clocked_out_at?->toDateTimeString(),
                    'clock_in_note' => $record->clock_in_note,
                    'clock_out_note' => $record->clock_out_note,
                ],
                'proposed_code_id' => $validated['attendance_code_id'],
                'proposed_clock_in' => $validated['clocked_in_at'] ?? null,
                'proposed_clock_out' => $validated['clocked_out_at'] ?? null,
                'reason' => 'Manager override via grid',
                'status' => 'approved',
                'reviewed_by' => $crmUser->id,
                'reviewed_at' => now(),
            ]);

            $updateData = [
                'attendance_code_id' => $validated['attendance_code_id'],
                'status' => 'active',
            ];

            if (array_key_exists('clocked_in_at', $validated)) {
                $updateData['clocked_in_at'] = $validated['clocked_in_at'];
            }

            if (array_key_exists('clocked_out_at', $validated)) {
                $updateData['clocked_out_at'] = $validated['clocked_out_at'];
            }

            if (isset($validated['clock_in_note'])) {
                $updateData['clock_in_note'] = $validated['clock_in_note'];
            }

            if (isset($validated['clock_out_note'])) {
                $updateData['clock_out_note'] = $validated['clock_out_note'];
            }

            if (isset($updateData['clocked_in_at'], $updateData['clocked_out_at']) && $updateData['clocked_in_at'] && $updateData['clocked_out_at']) {
                $updateData['total_minutes'] = (int) Carbon::parse($updateData['clocked_in_at'])->diffInMinutes(Carbon::parse($updateData['clocked_out_at']));
            }

            $record->update($updateData);
        });

        return response()->json(['message' => 'Record updated.']);
    }

    public function submitCorrection(AttendanceCorrectionRequest $request, CrmAttendanceRecord $record): JsonResponse
    {
        $this->authorizeModuleAccess('attendance', 'view');

        $crmUser = $this->crmUser();

        abort_unless((int) $record->user_id === (int) $crmUser->id, 403, 'You can only submit corrections for your own records.');
        abort_if($record->status === 'pending_correction', 422, 'This record already has a pending correction.');

        $validated = $request->validated();

        DB::transaction(function () use ($record, $validated, $crmUser) {
            CrmAttendanceCorrection::create([
                'attendance_record_id' => $record->id,
                'requested_by' => $crmUser->id,
                'original_values' => [
                    'attendance_code_id' => $record->attendance_code_id,
                    'clocked_in_at' => $record->clocked_in_at?->toDateTimeString(),
                    'clocked_out_at' => $record->clocked_out_at?->toDateTimeString(),
                ],
                'proposed_clock_in' => $validated['proposed_clock_in'] ?? null,
                'proposed_clock_out' => $validated['proposed_clock_out'] ?? null,
                'proposed_code_id' => $validated['proposed_code_id'] ?? null,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            $record->update(['status' => 'pending_correction']);
        });

        $correction = CrmAttendanceCorrection::query()
            ->where('attendance_record_id', $record->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($correction) {
            $this->notificationService->notifyCorrectionSubmitted($correction);
        }

        return response()->json(['message' => 'Correction request submitted. Your manager will review it.']);
    }

    public function reviewCorrection(AttendanceCorrectionReviewRequest $request, CrmAttendanceCorrection $correction): JsonResponse
    {
        $this->authorizeModuleAccess('attendance', 'edit');

        $crmUser = $this->crmUser();

        abort_unless($correction->isPending(), 422, 'This correction has already been reviewed.');

        abort_unless(
            (int) $correction->requested_by !== (int) $crmUser->id,
            403,
            'You cannot review your own correction request.'
        );

        $record = $correction->record;

        if ($crmUser->isManager() && ! $crmUser->isAdmin()) {
            abort_unless(
                $record->user && (int) $record->user->department_id === (int) $crmUser->department_id,
                403
            );
        }

        $validated = $request->validated();

        if ($validated['action'] === 'approve') {
            DB::transaction(function () use ($correction, $record, $crmUser) {
                $correction->update([
                    'status' => 'approved',
                    'reviewed_by' => $crmUser->id,
                    'reviewed_at' => now(),
                ]);

                $updateData = ['status' => 'active'];

                if ($correction->proposed_code_id) {
                    $updateData['attendance_code_id'] = $correction->proposed_code_id;
                }

                if ($correction->proposed_clock_in) {
                    $updateData['clocked_in_at'] = $correction->proposed_clock_in;
                }

                if ($correction->proposed_clock_out) {
                    $updateData['clocked_out_at'] = $correction->proposed_clock_out;
                }

                if (isset($updateData['clocked_in_at'], $updateData['clocked_out_at'])) {
                    $updateData['total_minutes'] = (int) Carbon::parse($updateData['clocked_in_at'])
                        ->diffInMinutes(Carbon::parse($updateData['clocked_out_at']));
                } elseif (isset($updateData['clocked_in_at']) && $record->clocked_out_at) {
                    $updateData['total_minutes'] = (int) Carbon::parse($updateData['clocked_in_at'])
                        ->diffInMinutes($record->clocked_out_at);
                } elseif (isset($updateData['clocked_out_at']) && $record->clocked_in_at) {
                    $updateData['total_minutes'] = (int) $record->clocked_in_at
                        ->diffInMinutes(Carbon::parse($updateData['clocked_out_at']));
                }

                $record->update($updateData);
            });

            $this->notificationService->notifyCorrectionApproved($correction->fresh());

            return response()->json(['message' => 'Correction approved and record updated.']);
        }

        $correction->update([
            'status' => 'rejected',
            'reviewed_by' => $crmUser->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $record->update(['status' => 'active']);

        $this->notificationService->notifyCorrectionRejected($correction->fresh());

        return response()->json(['message' => 'Correction rejected.']);
    }

    public function pendingCorrections(): JsonResponse
    {
        $this->authorizeModuleAccess('attendance', 'edit');

        $crmUser = $this->crmUser();

        $query = CrmAttendanceCorrection::query()
            ->with(['record.user', 'record.code', 'requester', 'proposedCode'])
            ->where('status', 'pending');

        if ($crmUser->isManager() && ! $crmUser->isAdmin()) {
            $query->whereHas('record.user', function ($q) use ($crmUser) {
                $q->where('department_id', $crmUser->department_id);
            });
        }

        $corrections = $query->latest()->get();

        return response()->json([
            'count' => $corrections->count(),
            'corrections' => $corrections->map(fn (CrmAttendanceCorrection $c) => [
                'id' => $c->id,
                'record_id' => $c->attendance_record_id,
                'requester_name' => $c->requester?->name,
                'date' => $c->record?->date?->format('D, d M Y'),
                'current_code' => $c->record?->code?->code,
                'proposed_code' => $c->proposedCode?->code,
                'proposed_clock_in' => $c->proposed_clock_in?->format('H:i'),
                'proposed_clock_out' => $c->proposed_clock_out?->format('H:i'),
                'reason' => $c->reason,
                'created_at' => $c->created_at?->diffForHumans(),
            ]),
        ]);
    }
}
