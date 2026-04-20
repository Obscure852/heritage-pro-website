<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\LtiLaunch;
use App\Models\Lms\LtiLineItem;
use App\Models\Lms\LtiPlacement;
use App\Models\Lms\LtiResourceLink;
use App\Models\Lms\LtiScore;
use App\Models\Lms\LtiTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LtiController extends Controller {
    // Admin: List all LTI tools
    public function index() {
        $tools = LtiTool::with('creator')
            ->withCount(['resourceLinks', 'launches'])
            ->orderBy('name')
            ->get();

        return view('lms.lti.index', compact('tools'));
    }

    // Admin: Create new tool form
    public function create() {
        return view('lms.lti.create');
    }

    // Admin: Store new tool
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tool_url' => 'required|url|max:255',
            'login_url' => 'nullable|url|max:255',
            'redirect_urls' => 'nullable|string',
            'public_key' => 'nullable|string',
            'public_key_url' => 'nullable|url|max:255',
            'lti_version' => 'required|in:1.1,1.3',
            'custom_parameters' => 'nullable|array',
            'privacy_level' => 'required|in:public,name_only,anonymous',
            'icon_url' => 'nullable|url|max:255',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['send_name'] = $validated['privacy_level'] !== 'anonymous';
        $validated['send_email'] = $validated['privacy_level'] === 'public';

        $tool = LtiTool::create($validated);

        return redirect()->route('lms.lti.show', $tool)
            ->with('success', 'LTI tool created successfully.');
    }

    // Admin: Show tool details
    public function show(LtiTool $tool) {
        $tool->load(['placements', 'resourceLinks.course', 'launches' => function ($q) {
            $q->latest()->take(20);
        }]);

        $recentLaunches = $tool->launches()->latest()->take(10)->with('user')->get();
        $launchStats = [
            'total' => $tool->launches()->count(),
            'today' => $tool->launches()->whereDate('launched_at', today())->count(),
            'this_week' => $tool->launches()->where('launched_at', '>=', now()->subWeek())->count(),
        ];

        return view('lms.lti.show', compact('tool', 'recentLaunches', 'launchStats'));
    }

    // Admin: Edit tool
    public function edit(LtiTool $tool) {
        return view('lms.lti.edit', compact('tool'));
    }

    // Admin: Update tool
    public function update(Request $request, LtiTool $tool) {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'tool_url' => 'url|max:255',
            'login_url' => 'nullable|url|max:255',
            'redirect_urls' => 'nullable|string',
            'public_key' => 'nullable|string',
            'public_key_url' => 'nullable|url|max:255',
            'custom_parameters' => 'nullable|array',
            'privacy_level' => 'in:public,name_only,anonymous',
            'is_active' => 'boolean',
            'icon_url' => 'nullable|url|max:255',
        ]);

        if (isset($validated['privacy_level'])) {
            $validated['send_name'] = $validated['privacy_level'] !== 'anonymous';
            $validated['send_email'] = $validated['privacy_level'] === 'public';
        }

        $tool->update($validated);

        return redirect()->route('lms.lti.show', $tool)
            ->with('success', 'LTI tool updated.');
    }

    // Admin: Delete tool
    public function destroy(LtiTool $tool) {
        $tool->delete();
        return redirect()->route('lms.lti.index')
            ->with('success', 'LTI tool deleted.');
    }

    // Add placement to tool
    public function addPlacement(Request $request, LtiTool $tool) {
        $validated = $request->validate([
            'placement_type' => 'required|in:' . implode(',', array_keys(LtiPlacement::$placementTypes)),
            'label' => 'nullable|string|max:255',
            'icon_url' => 'nullable|url|max:255',
        ]);

        $validated['tool_id'] = $tool->id;
        LtiPlacement::create($validated);

        return back()->with('success', 'Placement added.');
    }

    // Course: Available LTI tools
    public function courseTools(Course $course) {
        $availableTools = LtiTool::active()->get();
        $courseTools = $course->ltiTools()->with('tool')->get();

        return view('lms.lti.course-tools', compact('course', 'availableTools', 'courseTools'));
    }

    // Course: Enable/disable tool
    public function toggleCourseTool(Request $request, Course $course, LtiTool $tool) {
        $existing = $course->ltiTools()->where('tool_id', $tool->id)->first();

        if ($existing) {
            $existing->update(['is_enabled' => !$existing->is_enabled]);
        } else {
            $course->ltiTools()->create([
                'tool_id' => $tool->id,
                'is_enabled' => true,
            ]);
        }

        return back()->with('success', 'Tool settings updated.');
    }

    // Launch LTI tool (generates launch form)
    public function launch(Request $request, LtiTool $tool) {
        $user = Auth::user();
        $course = $request->course_id ? Course::find($request->course_id) : null;
        $resourceLink = $request->resource_link_id ? LtiResourceLink::find($request->resource_link_id) : null;

        // Build LTI launch parameters
        $params = $this->buildLaunchParams($tool, $user, $course, $resourceLink);

        // Log the launch
        LtiLaunch::log($tool, $user, 'LtiResourceLinkRequest', $resourceLink, $course, $params);

        // For LTI 1.3, redirect to OIDC login
        if ($tool->isLti13()) {
            return $this->oidcLogin($tool, $params, $course, $resourceLink);
        }

        // For LTI 1.1, render auto-submit form
        return view('lms.lti.launch', [
            'tool' => $tool,
            'launchUrl' => $resourceLink?->launch_url ?? $tool->tool_url,
            'params' => $params,
        ]);
    }

    // LTI 1.3 OIDC Login Initiation
    protected function oidcLogin(LtiTool $tool, array $params, ?Course $course, ?LtiResourceLink $resourceLink) {
        $state = Str::random(32);
        $nonce = Str::random(32);

        // Store state for verification
        session(['lti_state' => $state, 'lti_nonce' => $nonce, 'lti_params' => $params]);

        $loginParams = [
            'iss' => config('app.url'),
            'target_link_uri' => $resourceLink?->launch_url ?? $tool->tool_url,
            'login_hint' => Auth::id(),
            'lti_message_hint' => json_encode([
                'course_id' => $course?->id,
                'resource_link_id' => $resourceLink?->id,
            ]),
            'client_id' => $tool->client_id,
            'lti_deployment_id' => $tool->deployment_id,
        ];

        return redirect($tool->login_url . '?' . http_build_query($loginParams));
    }

    // Build launch parameters
    protected function buildLaunchParams(LtiTool $tool, $user, ?Course $course, ?LtiResourceLink $resourceLink): array {
        $params = [
            'lti_message_type' => 'basic-lti-launch-request',
            'lti_version' => $tool->lti_version === '1.3' ? 'LTI-1p3' : 'LTI-1p0',
            'resource_link_id' => $resourceLink?->resource_link_id ?? Str::uuid()->toString(),
            'resource_link_title' => $resourceLink?->title ?? $tool->name,
        ];

        // User info based on privacy level
        if ($tool->send_name) {
            $params['lis_person_name_full'] = $user->name;
            $params['lis_person_name_given'] = explode(' ', $user->name)[0] ?? '';
            $params['lis_person_name_family'] = explode(' ', $user->name)[1] ?? '';
        }

        if ($tool->send_email) {
            $params['lis_person_contact_email_primary'] = $user->email;
        }

        $params['user_id'] = $user->id;
        $params['roles'] = $this->getUserRoles($user, $course);

        // Course context
        if ($course) {
            $params['context_id'] = $course->id;
            $params['context_title'] = $course->title;
            $params['context_label'] = $course->code ?? $course->id;
            $params['context_type'] = 'CourseSection';
        }

        // Custom parameters
        if ($tool->custom_parameters) {
            foreach ($tool->custom_parameters as $key => $value) {
                $params['custom_' . $key] = $value;
            }
        }

        if ($resourceLink?->custom_parameters) {
            foreach ($resourceLink->custom_parameters as $key => $value) {
                $params['custom_' . $key] = $value;
            }
        }

        // Platform info
        $params['tool_consumer_instance_guid'] = config('app.url');
        $params['tool_consumer_instance_name'] = config('app.name');

        return $params;
    }

    protected function getUserRoles($user, ?Course $course): string {
        $roles = [];

        if ($user->can('manage-lms-courses')) {
            $roles[] = 'Instructor';
        }

        if ($course) {
            $enrollment = $course->enrollments()->where('student_id', $user->student?->id)->first();
            if ($enrollment) {
                $roles[] = 'Learner';
            }
        }

        if (empty($roles)) {
            $roles[] = 'Learner';
        }

        return implode(',', $roles);
    }

    // AGS: Get line items for a course
    public function lineItems(Course $course) {
        $lineItems = LtiLineItem::where('course_id', $course->id)
            ->with(['tool', 'resourceLink'])
            ->get();

        return response()->json($lineItems);
    }

    // AGS: Submit score
    public function submitScore(Request $request, LtiLineItem $lineItem) {
        $validated = $request->validate([
            'userId' => 'required|exists:users,id',
            'scoreGiven' => 'nullable|numeric|min:0',
            'scoreMaximum' => 'nullable|numeric|min:0',
            'comment' => 'nullable|string',
            'activityProgress' => 'in:' . implode(',', array_keys(LtiScore::$activityProgress)),
            'gradingProgress' => 'in:' . implode(',', array_keys(LtiScore::$gradingProgress)),
        ]);

        $score = LtiScore::updateOrCreate(
            ['line_item_id' => $lineItem->id, 'user_id' => $validated['userId']],
            [
                'score_given' => $validated['scoreGiven'] ?? null,
                'score_maximum' => $validated['scoreMaximum'] ?? $lineItem->score_maximum,
                'comment' => $validated['comment'] ?? null,
                'activity_progress' => $validated['activityProgress'] ?? 'Completed',
                'grading_progress' => $validated['gradingProgress'] ?? 'FullyGraded',
                'timestamp' => now(),
            ]
        );

        return response()->json(['success' => true, 'score' => $score]);
    }

    // JWKS endpoint for LTI 1.3
    public function jwks() {
        // Return platform public keys in JWKS format
        $keys = \DB::table('lms_lti_platform_keys')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get();

        $jwks = ['keys' => []];

        foreach ($keys as $key) {
            $jwks['keys'][] = [
                'kty' => 'RSA',
                'alg' => $key->alg,
                'kid' => $key->kid,
                'use' => 'sig',
                'n' => $this->extractModulus($key->public_key),
                'e' => 'AQAB',
            ];
        }

        return response()->json($jwks);
    }

    protected function extractModulus(string $publicKey): string {
        $key = openssl_pkey_get_public($publicKey);
        $details = openssl_pkey_get_details($key);
        return rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
    }
}
