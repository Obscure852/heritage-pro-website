<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\ContentItem;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Module;
use App\Models\Lms\ScormAttempt;
use App\Models\Lms\ScormPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ScormController extends Controller {
    /**
     * Upload SCORM package form
     */
    public function create(Module $module) {
        Gate::authorize('manage-lms-content');

        return view('lms.scorm.create', compact('module'));
    }

    /**
     * Upload and process SCORM package
     */
    public function store(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package' => 'required|file|mimes:zip|max:512000', // 500MB max
            'mastery_score' => 'nullable|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
        ]);

        $file = $request->file('package');
        $zipPath = $file->store('lms/scorm/packages', 'public');
        $extractPath = 'lms/scorm/content/' . uniqid('scorm_');

        // Extract the zip
        $zip = new ZipArchive();
        $fullZipPath = Storage::disk('public')->path($zipPath);
        $fullExtractPath = Storage::disk('public')->path($extractPath);

        if ($zip->open($fullZipPath) !== true) {
            Storage::disk('public')->delete($zipPath);
            return back()->with('error', 'Failed to open SCORM package.');
        }

        // Create extraction directory
        if (!is_dir($fullExtractPath)) {
            mkdir($fullExtractPath, 0755, true);
        }

        $zip->extractTo($fullExtractPath);
        $zip->close();

        // Parse imsmanifest.xml
        $manifestPath = $fullExtractPath . '/imsmanifest.xml';
        if (!file_exists($manifestPath)) {
            Storage::disk('public')->delete($zipPath);
            Storage::disk('public')->deleteDirectory($extractPath);
            return back()->with('error', 'Invalid SCORM package: imsmanifest.xml not found.');
        }

        $manifest = $this->parseManifest($manifestPath);

        if (!$manifest) {
            Storage::disk('public')->delete($zipPath);
            Storage::disk('public')->deleteDirectory($extractPath);
            return back()->with('error', 'Failed to parse SCORM manifest.');
        }

        // Create SCORM package record
        $scormPackage = ScormPackage::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'version' => $manifest['version'],
            'zip_path' => $zipPath,
            'extracted_path' => $extractPath,
            'launch_url' => $manifest['launch_url'],
            'identifier' => $manifest['identifier'],
            'manifest_data' => $manifest['raw'],
            'organizations' => $manifest['organizations'],
            'resources' => $manifest['resources'],
            'mastery_score' => $validated['mastery_score'],
            'time_limit_minutes' => $validated['time_limit_minutes'],
            'max_attempts' => $validated['max_attempts'],
            'package_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        // Create content item
        $maxSequence = $module->contentItems()->max('sequence') ?? 0;
        ContentItem::create([
            'module_id' => $module->id,
            'type' => 'scorm',
            'title' => $validated['title'],
            'description' => $validated['description'],
            'sequence' => $maxSequence + 1,
            'is_required' => true,
            'contentable_id' => $scormPackage->id,
            'contentable_type' => ScormPackage::class,
        ]);

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'SCORM package uploaded successfully.');
    }

    /**
     * Parse imsmanifest.xml
     */
    protected function parseManifest(string $path): ?array
    {
        $xml = simplexml_load_file($path);
        if (!$xml) {
            return null;
        }

        // Detect SCORM version
        $namespaces = $xml->getNamespaces(true);
        $version = '1.2'; // Default

        if (isset($namespaces['adlcp'])) {
            $adlcpNs = $namespaces['adlcp'];
            if (str_contains($adlcpNs, '2004')) {
                if (str_contains($adlcpNs, '4th')) {
                    $version = '2004_4th';
                } elseif (str_contains($adlcpNs, '3rd')) {
                    $version = '2004_3rd';
                } else {
                    $version = '2004_2nd';
                }
            }
        }

        // Get identifier
        $identifier = (string) $xml->attributes()['identifier'] ?? null;

        // Parse organizations
        $organizations = [];
        foreach ($xml->organizations->organization as $org) {
            $organizations[] = [
                'identifier' => (string) $org->attributes()['identifier'],
                'title' => (string) $org->title,
            ];
        }

        // Parse resources and find launch URL
        $resources = [];
        $launchUrl = null;

        foreach ($xml->resources->resource as $resource) {
            $resId = (string) $resource->attributes()['identifier'];
            $href = (string) $resource->attributes()['href'];
            $type = (string) $resource->attributes()['type'];

            $resources[] = [
                'identifier' => $resId,
                'href' => $href,
                'type' => $type,
            ];

            // First SCO resource is typically the launch URL
            if (!$launchUrl && $type === 'webcontent' && $href) {
                $launchUrl = $href;
            }
        }

        // If no launch URL found from resources, try to get from first organization item
        if (!$launchUrl && !empty($organizations)) {
            foreach ($xml->organizations->organization->item as $item) {
                $resRef = (string) $item->attributes()['identifierref'];
                if ($resRef) {
                    foreach ($resources as $res) {
                        if ($res['identifier'] === $resRef && $res['href']) {
                            $launchUrl = $res['href'];
                            break 2;
                        }
                    }
                }
            }
        }

        return [
            'version' => $version,
            'identifier' => $identifier,
            'launch_url' => $launchUrl,
            'organizations' => $organizations,
            'resources' => $resources,
            'raw' => json_decode(json_encode($xml), true),
        ];
    }

    /**
     * SCORM player view
     */
    public function player(ScormPackage $package, ?ContentItem $content = null) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify enrollment if linked to content
        if ($content) {
            $enrollment = Enrollment::where('course_id', $content->module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return back()->with('error', 'You must be enrolled in this course.');
            }
        }

        // Get or create attempt
        $attempt = $package->getOrCreateAttempt($student->id, $content?->id);

        return view('lms.scorm.player', compact('package', 'attempt', 'content'));
    }

    /**
     * SCORM Runtime API - Initialize
     */
    public function apiInitialize(Request $request, ScormAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get initial data for resume
        $data = $attempt->getInitialData();

        return response()->json([
            'success' => true,
            'data' => $data,
            'version' => $attempt->package->version,
        ]);
    }

    /**
     * SCORM Runtime API - GetValue
     */
    public function apiGetValue(Request $request, ScormAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $element = $request->input('element');
        $value = $attempt->getCmiValue($element);

        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }

    /**
     * SCORM Runtime API - SetValue
     */
    public function apiSetValue(Request $request, ScormAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $element = $request->input('element');
        $value = $request->input('value');

        $attempt->setCmiValue($element, $value);

        return response()->json(['success' => true]);
    }

    /**
     * SCORM Runtime API - Commit
     */
    public function apiCommit(Request $request, ScormAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update last accessed
        $attempt->update(['last_accessed_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * SCORM Runtime API - Terminate
     */
    public function apiTerminate(Request $request, ScormAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt->terminate();

        return response()->json(['success' => true]);
    }

    /**
     * SCORM Runtime API - Batch update (for efficiency)
     */
    public function apiBatchUpdate(Request $request, ScormAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->input('data', []);

        foreach ($data as $element => $value) {
            $attempt->setCmiValue($element, $value);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete SCORM package
     */
    public function destroy(ScormPackage $package) {
        Gate::authorize('manage-lms-content');

        // Delete content item if exists
        if ($package->contentItem) {
            $package->contentItem->delete();
        }

        // Delete package files
        $package->deletePackageFiles();

        $package->delete();

        return back()->with('success', 'SCORM package deleted.');
    }

    /**
     * List all SCORM packages
     */
    public function index() {
        Gate::authorize('manage-lms-content');

        $packages = ScormPackage::with(['uploader', 'contentItem.module.course'])
            ->withCount('attempts')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('lms.scorm.index', compact('packages'));
    }

    /**
     * View SCORM package details
     */
    public function show(ScormPackage $package) {
        Gate::authorize('manage-lms-content');

        $package->load(['uploader', 'contentItem.module.course', 'attempts.student']);

        $stats = [
            'total_attempts' => $package->attempts->count(),
            'unique_students' => $package->attempts->unique('student_id')->count(),
            'completed' => $package->attempts->where('completion_status', 'completed')->count(),
            'passed' => $package->attempts->where('success_status', 'passed')->count(),
            'avg_score' => $package->attempts->avg('score_raw'),
        ];

        return view('lms.scorm.show', compact('package', 'stats'));
    }

    /**
     * Edit SCORM package settings
     */
    public function edit(ScormPackage $package) {
        Gate::authorize('manage-lms-content');

        return view('lms.scorm.edit', compact('package'));
    }

    /**
     * Update SCORM package settings
     */
    public function update(Request $request, ScormPackage $package) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mastery_score' => 'nullable|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
        ]);

        $package->update($validated);

        // Update content item title if exists
        if ($package->contentItem) {
            $package->contentItem->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);
        }

        return redirect()
            ->route('lms.scorm.show', $package)
            ->with('success', 'SCORM package updated successfully.');
    }

    /**
     * Preview SCORM package (for instructors)
     */
    public function preview(ScormPackage $package) {
        Gate::authorize('manage-lms-content');

        // Verify package has required data
        if (!$package->extracted_path || !$package->launch_url) {
            return redirect()
                ->route('lms.scorm.show', $package)
                ->with('error', 'SCORM package is missing required launch configuration.');
        }

        // Verify extracted content exists
        $launchPath = Storage::disk('public')->path($package->extracted_path . '/' . $package->launch_url);
        if (!file_exists($launchPath)) {
            return redirect()
                ->route('lms.scorm.show', $package)
                ->with('error', 'SCORM package content files are missing. Please re-upload the package.');
        }

        return view('lms.scorm.player', [
            'package' => $package,
            'attempt' => null,
            'content' => $package->contentItem,
            'preview' => true,
        ]);
    }
}
