<?php

namespace App\Http\Controllers\Leave;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\UpdateLeaveSettingsRequest;
use App\Models\Leave\LeaveSetting;
use App\Services\Leave\LeaveBalanceService;
use App\Services\Leave\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for leave module settings management.
 */
class LeaveSettingsController extends Controller {
    /**
     * The leave type service instance.
     *
     * @var LeaveTypeService
     */
    protected LeaveTypeService $leaveTypeService;

    /**
     * The leave balance service instance.
     *
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $leaveBalanceService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveTypeService $leaveTypeService
     * @param LeaveBalanceService $leaveBalanceService
     */
    public function __construct(LeaveTypeService $leaveTypeService, LeaveBalanceService $leaveBalanceService) {
        $this->leaveTypeService = $leaveTypeService;
        $this->leaveBalanceService = $leaveBalanceService;
    }

    /**
     * Display the leave settings page.
     *
     * @return View
     */
    public function index(): View {
        // Load all leave settings as key-value pairs
        $settings = LeaveSetting::all()->pluck('value', 'key')->toArray();

        // Extract scalar values from nested arrays for easier template access
        $flatSettings = [];
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                // Store both the raw value and flattened version
                $flatSettings[$key] = $value;
            } else {
                $flatSettings[$key] = $value;
            }
        }

        // Get leave types for the Leave Types tab
        $leaveTypes = $this->leaveTypeService->getAll();
        $leaveTypeCounts = $this->leaveTypeService->getCounts();

        return view('leave.settings.index', [
            'settings' => $flatSettings,
            'leaveTypes' => $leaveTypes,
            'leaveTypeCounts' => $leaveTypeCounts,
        ]);
    }

    /**
     * Update leave settings.
     *
     * @param UpdateLeaveSettingsRequest $request
     * @return JsonResponse
     */
    public function update(UpdateLeaveSettingsRequest $request): JsonResponse {
        $validated = $request->validated();
        $userId = auth()->id();

        try {
            // General settings
            if (isset($validated['leave_year_start_month'])) {
                LeaveSetting::set('leave_year_start_month', ['month' => (int) $validated['leave_year_start_month']], $userId);
            }

            if (array_key_exists('weekend_days', $validated)) {
                // Convert to array of integers
                $days = isset($validated['weekend_days']) ? array_map('intval', $validated['weekend_days']) : [];
                LeaveSetting::set('weekend_days', ['days' => $days], $userId);
            }

            if (isset($validated['default_balance_mode'])) {
                LeaveSetting::set('default_balance_mode', ['mode' => $validated['default_balance_mode']], $userId);
            }

            if (isset($validated['default_carry_over_mode'])) {
                LeaveSetting::set('default_carry_over_mode', ['mode' => $validated['default_carry_over_mode']], $userId);
            }

            // Request settings
            if (array_key_exists('allow_backdated_requests', $validated)) {
                $maxDays = (int) ($validated['backdated_max_days'] ?? 7);
                LeaveSetting::set('allow_backdated_requests', [
                    'allowed' => (bool) $validated['allow_backdated_requests'],
                    'max_days' => $maxDays,
                ], $userId);
            }

            if (array_key_exists('leave_request_approval_required', $validated)) {
                LeaveSetting::set('leave_request_approval_required', [
                    'required' => (bool) $validated['leave_request_approval_required'],
                ], $userId);
            }

            if (isset($validated['max_negative_balance'])) {
                LeaveSetting::set('max_negative_balance', ['days' => (int) $validated['max_negative_balance']], $userId);
            }

            if (array_key_exists('auto_cancel_pending_enabled', $validated)) {
                $days = (int) ($validated['auto_cancel_pending_days'] ?? 30);
                LeaveSetting::set('auto_cancel_pending_after_days', [
                    'enabled' => (bool) $validated['auto_cancel_pending_enabled'],
                    'days' => $days,
                ], $userId);
            }

            // Notification settings
            if (isset($validated['leave_reminder_days_before'])) {
                LeaveSetting::set('leave_reminder_days_before', ['days' => (int) $validated['leave_reminder_days_before']], $userId);
            }

            if (isset($validated['pending_approval_reminder_hours'])) {
                LeaveSetting::set('pending_approval_reminder_hours', ['hours' => (int) $validated['pending_approval_reminder_hours']], $userId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Leave settings saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initialize leave balances for all current staff.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initializeBalances(Request $request): JsonResponse {
        try {
            // Get current leave year from TermHelper
            $currentTerm = TermHelper::getCurrentTerm();
            $leaveYear = $currentTerm ? (int) $currentTerm->year : (int) date('Y');

            // Initialize and sync balances for all current staff
            $stats = $this->leaveBalanceService->initializeBalancesForYear($leaveYear);

            // Build a descriptive message
            $messages = [];
            if ($stats['created'] > 0) {
                $messages[] = "{$stats['created']} created";
            }
            if ($stats['removed'] > 0) {
                $messages[] = "{$stats['removed']} removed (no longer eligible)";
            }
            if ($stats['updated'] > 0) {
                $messages[] = "{$stats['updated']} updated";
            }

            $totalChanges = $stats['created'] + $stats['removed'] + $stats['updated'];

            if ($totalChanges > 0) {
                $message = "Leave balances synced for {$leaveYear}: " . implode(', ', $messages) . ".";
            } else {
                $message = "All leave balances for {$leaveYear} are already up to date. No changes needed.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'stats' => $stats,
                'leave_year' => $leaveYear,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing balances: ' . $e->getMessage(),
            ], 500);
        }
    }
}
