<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\ContentItem;
use App\Models\Lms\Enrollment;
use App\Models\Lms\H5pContent;
use App\Models\Lms\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class H5pController extends Controller
{
    /**
     * Show H5P content creation form
     */
    public function create(Module $module)
    {
        Gate::authorize('manage-lms-content');

        return view('lms.h5p.create', compact('module'));
    }

    /**
     * Store new H5P content
     */
    public function store(Request $request, Module $module)
    {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package' => 'required|file|mimes:h5p,zip|max:512000', // 500MB max
        ]);

        $file = $request->file('package');
        $packagePath = $file->store('lms/h5p/packages', 'public');
        $extractPath = 'lms/h5p/content/' . uniqid('h5p_');

        // Extract the H5P package
        $zip = new ZipArchive();
        $fullZipPath = Storage::disk('public')->path($packagePath);
        $fullExtractPath = Storage::disk('public')->path($extractPath);

        if ($zip->open($fullZipPath) !== true) {
            Storage::disk('public')->delete($packagePath);
            return back()->with('error', 'Failed to open H5P package.');
        }

        if (!is_dir($fullExtractPath)) {
            mkdir($fullExtractPath, 0755, true);
        }

        $zip->extractTo($fullExtractPath);
        $zip->close();

        // Parse h5p.json
        $h5pJsonPath = $fullExtractPath . '/h5p.json';
        if (!file_exists($h5pJsonPath)) {
            Storage::disk('public')->delete($packagePath);
            Storage::disk('public')->deleteDirectory($extractPath);
            return back()->with('error', 'Invalid H5P package: h5p.json not found.');
        }

        $h5pJson = json_decode(file_get_contents($h5pJsonPath), true);

        if (!$h5pJson) {
            Storage::disk('public')->delete($packagePath);
            Storage::disk('public')->deleteDirectory($extractPath);
            return back()->with('error', 'Failed to parse H5P metadata.');
        }

        // Parse content.json for parameters
        $contentJsonPath = $fullExtractPath . '/content/content.json';
        $parameters = [];
        if (file_exists($contentJsonPath)) {
            $parameters = json_decode(file_get_contents($contentJsonPath), true) ?? [];
        }

        // Create H5P content record
        $h5pContent = H5pContent::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'library' => $h5pJson['mainLibrary'] ?? 'Unknown',
            'library_major_version' => $h5pJson['preloadedDependencies'][0]['majorVersion'] ?? null,
            'library_minor_version' => $h5pJson['preloadedDependencies'][0]['minorVersion'] ?? null,
            'parameters' => $parameters,
            'embed_type' => $h5pJson['embedTypes'][0] ?? 'div',
            'content_path' => $extractPath,
            'package_path' => $packagePath,
            'package_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        // Create content item
        $maxSequence = $module->contentItems()->max('sequence') ?? 0;
        ContentItem::create([
            'module_id' => $module->id,
            'type' => 'h5p',
            'title' => $validated['title'],
            'description' => $validated['description'],
            'sequence' => $maxSequence + 1,
            'is_required' => true,
            'contentable_id' => $h5pContent->id,
            'contentable_type' => H5pContent::class,
        ]);

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'H5P content uploaded successfully.');
    }

    /**
     * H5P player view
     */
    public function player(H5pContent $content, ?ContentItem $item = null)
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify enrollment if linked to content item
        if ($item) {
            $enrollment = Enrollment::where('course_id', $item->module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return back()->with('error', 'You must be enrolled in this course.');
            }
        }

        // Get or create result
        $result = $content->getOrCreateResult($student->id, $item?->id);
        $result->recordOpen();

        return view('lms.h5p.player', compact('content', 'result', 'item'));
    }

    /**
     * Handle xAPI events from H5P content
     */
    public function xapiEvent(Request $request, H5pContent $content)
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'verb' => 'required|string',
            'object_type' => 'nullable|string',
            'object_id' => 'nullable|string',
            'result' => 'nullable|array',
            'context' => 'nullable|array',
        ]);

        // Record the event
        $content->recordEvent($student->id, $validated['verb'], $validated);

        // Update result if completion or score event
        $result = $content->getOrCreateResult($student->id);

        if ($validated['verb'] === 'completed' || $validated['verb'] === 'answered') {
            if (isset($validated['result']['score'])) {
                $score = $validated['result']['score']['raw'] ?? 0;
                $maxScore = $validated['result']['score']['max'] ?? 100;
                $result->recordFinish($score, $maxScore);
                $result->updateContentProgress();
            }
        }

        if (isset($validated['result']['duration'])) {
            // Duration is in ISO 8601 format, convert to seconds
            $duration = $this->parseDuration($validated['result']['duration']);
            if ($duration > 0) {
                $result->addTimeSpent($duration);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete H5P content
     */
    public function destroy(H5pContent $content)
    {
        Gate::authorize('manage-lms-content');

        // Delete content item if exists
        if ($content->contentItem) {
            $content->contentItem->delete();
        }

        // Delete files
        $content->deleteContentFiles();

        $content->delete();

        return back()->with('success', 'H5P content deleted.');
    }

    /**
     * Parse ISO 8601 duration to seconds
     */
    protected function parseDuration(string $duration): int
    {
        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?/', $duration, $matches)) {
            $hours = (int)($matches[1] ?? 0);
            $minutes = (int)($matches[2] ?? 0);
            $seconds = (int)($matches[3] ?? 0);
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }
        return 0;
    }
}
