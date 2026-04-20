<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\PassingThresholdSetting;
use App\Services\ThresholdSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ThresholdSettingsController extends Controller
{
    public function __construct(
        private ThresholdSettingsService $thresholdService
    ) {
        $this->middleware('auth');
    }

    /**
     * Get effective threshold settings for current context (AJAX).
     * Called from markbook views to get applicable thresholds.
     */
    public function getEffectiveThreshold(Request $request): JsonResponse
    {
        try {
            $threshold = $this->thresholdService->getEffectiveThreshold(
                $request->input('grade_id') ? (int) $request->input('grade_id') : null,
                $request->input('grade_subject_id') ? (int) $request->input('grade_subject_id') : null,
                $request->input('test_type')
            );

            return response()->json([
                'success' => true,
                'data' => $threshold,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve threshold settings',
            ], 500);
        }
    }

    /**
     * Update teacher's personal threshold preference (AJAX).
     * Called from the settings modal in markbook views.
     */
    public function updateTeacherPreference(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'highlight_enabled' => 'required|boolean',
            'thresholds' => 'nullable|array',
            'thresholds.*.name' => 'required_with:thresholds|string|max:50',
            'thresholds.*.max_percentage' => 'required_with:thresholds|numeric|min:0|max:100',
            'thresholds.*.color' => 'required_with:thresholds|regex:/^#[0-9A-Fa-f]{6}$/',
        ], [
            'thresholds.*.name.required_with' => 'Each threshold must have a name',
            'thresholds.*.max_percentage.required_with' => 'Each threshold must have a max percentage',
            'thresholds.*.max_percentage.min' => 'Max percentage must be at least 0',
            'thresholds.*.max_percentage.max' => 'Max percentage cannot exceed 100',
            'thresholds.*.color.required_with' => 'Each threshold must have a color',
            'thresholds.*.color.regex' => 'Color must be a valid hex code (e.g., #ff0000)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Additional validation for thresholds
        $thresholds = $request->input('thresholds');
        if (!empty($thresholds)) {
            $validation = $this->thresholdService->validateThresholds($thresholds);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'errors' => ['thresholds' => $validation['errors']],
                ], 422);
            }
        }

        try {
            $preference = $this->thresholdService->updateTeacherPreference(
                Auth::id(),
                [
                    'highlight_enabled' => $request->boolean('highlight_enabled'),
                    'thresholds' => $thresholds,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Preferences saved successfully',
                'data' => [
                    'highlight_enabled' => $preference->highlight_enabled,
                    'thresholds' => $preference->thresholds,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences. Please try again.',
            ], 500);
        }
    }

    /**
     * Reset teacher preference to system defaults (AJAX).
     */
    public function resetTeacherPreference(): JsonResponse
    {
        try {
            $preference = $this->thresholdService->resetTeacherPreference(Auth::id());

            // Get fresh effective settings
            $effective = $this->thresholdService->getEffectiveThreshold();

            return response()->json([
                'success' => true,
                'message' => 'Preferences reset to system defaults',
                'data' => $effective,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset preferences. Please try again.',
            ], 500);
        }
    }

    /**
     * Admin: Display system threshold settings management page.
     */
    public function systemSettings(): View
    {
        $this->authorize('manage-academic');

        $settings = $this->thresholdService->getAllSystemSettings();
        $grades = Grade::orderBy('level')->get();
        $gradeSubjects = GradeSubject::with('subject', 'grade')
            ->orderBy('grade_id')
            ->get()
            ->groupBy('grade_id');

        return view('settings.threshold-settings', compact('settings', 'grades', 'gradeSubjects'));
    }

    /**
     * Admin: Store or update a system threshold setting (AJAX).
     */
    public function storeSystemSetting(Request $request): JsonResponse
    {
        $this->authorize('manage-academic');

        $validator = Validator::make($request->all(), [
            'school_type' => 'nullable|in:Junior,Senior,Primary',
            'grade_id' => 'nullable|exists:grades,id',
            'grade_subject_id' => 'nullable|exists:grade_subject,id',
            'test_type' => 'nullable|in:CA,Exam,Exercise',
            'thresholds' => 'required|array|min:1',
            'thresholds.*.name' => 'required|string|max:50',
            'thresholds.*.max_percentage' => 'required|numeric|min:0|max:100',
            'thresholds.*.color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate thresholds structure
        $validation = $this->thresholdService->validateThresholds($request->input('thresholds'));
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => ['thresholds' => $validation['errors']],
            ], 422);
        }

        try {
            $criteria = [
                'school_type' => $request->input('school_type'),
                'grade_id' => $request->input('grade_id'),
                'grade_subject_id' => $request->input('grade_subject_id'),
                'test_type' => $request->input('test_type'),
            ];

            $data = [
                'thresholds' => $request->input('thresholds'),
                'is_active' => $request->boolean('is_active', true),
            ];

            $setting = $this->thresholdService->updateSystemSetting($criteria, $data);

            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully',
                'data' => $setting->load(['grade', 'gradeSubject.subject']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save setting. Please try again.',
            ], 500);
        }
    }

    /**
     * Admin: Delete a system threshold setting (AJAX).
     */
    public function deleteSystemSetting(int $id): JsonResponse
    {
        $this->authorize('manage-academic');

        try {
            $this->thresholdService->deleteSystemSetting($id);

            return response()->json([
                'success' => true,
                'message' => 'Setting deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete setting. Please try again.',
            ], 500);
        }
    }

    /**
     * Admin: Toggle setting active status (AJAX).
     */
    public function toggleSystemSetting(int $id): JsonResponse
    {
        $this->authorize('manage-academic');

        try {
            $setting = PassingThresholdSetting::findOrFail($id);
            $setting->update(['is_active' => !$setting->is_active]);

            $this->thresholdService->clearAllCache();

            return response()->json([
                'success' => true,
                'message' => $setting->is_active ? 'Setting activated' : 'Setting deactivated',
                'data' => ['is_active' => $setting->is_active],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle setting. Please try again.',
            ], 500);
        }
    }
}
