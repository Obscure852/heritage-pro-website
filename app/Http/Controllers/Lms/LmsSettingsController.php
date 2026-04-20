<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\LtiTool;
use App\Models\Lms\Rubric;
use App\Models\SMSApiSetting;
use App\Services\LmsSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Exception;

class LmsSettingsController extends Controller
{
    /**
     * Display the LMS settings page
     */
    public function index()
    {
        Gate::authorize('manage-lms-courses');

        try {
            // Get settings grouped by prefix
            $generalSettings = $this->getSettingsByPrefix(['lms_scorm_max', 'lms_h5p_max', 'lms_video_max', 'lms_library_max']);
            $courseSettings = $this->getSettingsByPrefix(['lms_course_', 'lms_gradebook_']);
            $quizSettings = $this->getSettingsByPrefix(['lms_quiz_']);
            $assignmentSettings = $this->getSettingsByPrefix(['lms_assignment_']);
            $videoSettings = $this->getSettingsByPrefix(['lms_video_']);
            $scormSettings = $this->getSettingsByPrefix(['lms_scorm_']);
            $ltiSettings = $this->getSettingsByPrefix(['lms_lti_']);
            $gamificationSettings = $this->getSettingsByPrefix(['lms_gamification_']);
            $calendarSettings = $this->getCalendarSettings();

            // Get LTI tools for management tab
            $ltiTools = collect();
            if (class_exists(LtiTool::class)) {
                try {
                    $ltiTools = LtiTool::with('creator')
                        ->withCount(['resourceLinks', 'launches'])
                        ->orderBy('name')
                        ->get();
                } catch (Exception $e) {
                    // LTI tools table may not exist
                    Log::info('LTI tools table not available', ['error' => $e->getMessage()]);
                }
            }

            // Get rubrics for management tab
            $rubrics = collect();
            try {
                $rubrics = Rubric::with('creator')
                    ->withCount(['criteria', 'assignments'])
                    ->where(function ($query) {
                        $query->where('is_template', true)
                            ->orWhere('created_by', Auth::id());
                    })
                    ->orderBy('title')
                    ->get();
            } catch (Exception $e) {
                Log::info('Rubrics table not available', ['error' => $e->getMessage()]);
            }

            return view('lms.settings.index', compact(
                'generalSettings',
                'courseSettings',
                'quizSettings',
                'assignmentSettings',
                'videoSettings',
                'scormSettings',
                'ltiSettings',
                'gamificationSettings',
                'calendarSettings',
                'ltiTools',
                'rubrics'
            ));
        } catch (Exception $e) {
            Log::error('Error loading LMS settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while loading LMS settings.');
        }
    }

    /**
     * Update multiple LMS settings
     */
    public function update(Request $request)
    {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['settings'] as $key => $value) {
                $setting = SMSApiSetting::where('key', $key)
                    ->where('category', 'lms')
                    ->where('is_editable', true)
                    ->first();

                if ($setting) {
                    $preparedValue = $this->prepareValueForStorage($value, $setting->type);
                    $setting->update(['value' => $preparedValue]);
                }
            }

            DB::commit();
            LmsSettingsService::clearCache();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully.'
                ]);
            }

            return back()->with('success', 'Settings updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update LMS settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings.'
                ], 500);
            }

            return back()->with('error', 'Failed to update settings.');
        }
    }

    /**
     * Update a single LMS setting via AJAX
     */
    public function updateSingle(Request $request)
    {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $updated = LmsSettingsService::update($validated['key'], $validated['value']);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Setting not found or not editable.'
        ], 404);
    }

    /**
     * Get settings that match any of the given prefixes
     */
    private function getSettingsByPrefix(array $prefixes): \Illuminate\Support\Collection
    {
        return SMSApiSetting::where('category', 'lms')
            ->where(function ($query) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $query->orWhere('key', 'like', $prefix . '%');
                }
            })
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Prepare value for storage based on type
     */
    private function prepareValueForStorage($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            case 'json':
            case 'array':
                return is_string($value) ? $value : json_encode($value);
            case 'integer':
            case 'int':
                return (string) (int) $value;
            case 'decimal':
            case 'float':
                return (string) (float) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Get calendar notification settings.
     */
    private function getCalendarSettings(): array
    {
        $settings = SMSApiSetting::whereIn('key', [
            'lms_calendar_notifications_enabled',
            'lms_calendar_notification_queue',
            'lms_calendar_notification_batch_size',
        ])->pluck('value', 'key');

        return [
            'notifications_enabled' => ($settings['lms_calendar_notifications_enabled'] ?? 'false') === 'true',
            'queue_name' => $settings['lms_calendar_notification_queue'] ?? 'calendar-notifications',
            'batch_size' => (int) ($settings['lms_calendar_notification_batch_size'] ?? 100),
        ];
    }

    /**
     * Update calendar notification settings.
     */
    public function updateCalendarSettings(Request $request)
    {
        Gate::authorize('manage-lms-courses');

        $validated = $request->validate([
            'notifications_enabled' => 'boolean',
            'queue_name' => 'required|string|max:100|regex:/^[a-z0-9\-]+$/',
            'batch_size' => 'required|integer|min:1|max:500',
        ]);

        try {
            DB::beginTransaction();

            SMSApiSetting::updateOrCreate(
                ['key' => 'lms_calendar_notifications_enabled'],
                ['value' => $validated['notifications_enabled'] ? 'true' : 'false']
            );

            SMSApiSetting::updateOrCreate(
                ['key' => 'lms_calendar_notification_queue'],
                ['value' => $validated['queue_name']]
            );

            SMSApiSetting::updateOrCreate(
                ['key' => 'lms_calendar_notification_batch_size'],
                ['value' => (string) $validated['batch_size']]
            );

            DB::commit();

            return redirect()
                ->route('lms.settings.index', ['tab' => 'calendar'])
                ->with('success', 'Calendar notification settings updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update calendar settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Failed to update calendar settings.');
        }
    }
}
