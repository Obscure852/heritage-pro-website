<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\ContentItem;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Module;
use App\Models\Lms\Video;
use App\Models\Lms\VideoProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller {
    /**
     * Show video upload form
     */
    public function create(Module $module) {
        Gate::authorize('manage-lms-content');

        return view('lms.videos.create', compact('module'));
    }

    /**
     * Store uploaded video
     */
    public function store(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_type' => 'required|in:upload,youtube',
            'youtube_url' => 'required_if:source_type,youtube|nullable|string',
            'video_file' => 'required_if:source_type,upload|nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm|max:' . (Video::MAX_UPLOAD_SIZE_MB * 1024),
            'thumbnail' => 'nullable|image|max:5120',
            'auto_transcode' => 'boolean',
            'transcode_formats' => 'nullable|array',
            'transcode_formats.*' => 'in:1080p,720p,480p,360p',
        ]);

        $videoData = [
            'source_type' => $validated['source_type'],
            'uploaded_by' => Auth::id(),
        ];

        if ($validated['source_type'] === 'youtube') {
            $youtubeId = Video::extractYouTubeId($validated['youtube_url']);

            if (!$youtubeId) {
                return back()->with('error', 'Invalid YouTube URL.');
            }

            $videoData['source_id'] = $youtubeId;
            $videoData['transcoding_status'] = Video::TRANSCODING_NOT_REQUIRED;
        } else {
            // Handle file upload
            $file = $request->file('video_file');
            $fileName = uniqid('video_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $filePath = $file->storeAs('lms/videos/originals', $fileName, 'public');

            $videoData['file_path'] = $filePath;
            $videoData['original_filename'] = $file->getClientOriginalName();
            $videoData['mime_type'] = $file->getMimeType();
            $videoData['file_size_bytes'] = $file->getSize();
            $videoData['transcoding_status'] = Video::TRANSCODING_PENDING;
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbName = uniqid('thumb_') . '.' . $thumbnail->getClientOriginalExtension();
            $thumbPath = $thumbnail->storeAs('lms/videos/thumbnails', $thumbName, 'public');
            $videoData['thumbnail_path'] = $thumbPath;
        }

        // Create video record
        $video = Video::create($videoData);

        // Extract metadata for uploaded videos
        if ($validated['source_type'] === 'upload') {
            $metadata = $video->extractMetadata();
            if (!empty($metadata)) {
                $video->update($metadata);
            }

            // Generate thumbnail if not provided
            if (!$video->thumbnail_path) {
                $video->generateThumbnail();
            }
        }

        // Create content item
        $maxSequence = $module->contentItems()->max('sequence') ?? 0;
        ContentItem::create([
            'module_id' => $module->id,
            'type' => 'video',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sequence' => $maxSequence + 1,
            'is_required' => true,
            'contentable_id' => $video->id,
            'contentable_type' => Video::class,
        ]);

        // Start transcoding if requested
        if ($validated['source_type'] === 'upload' && $request->boolean('auto_transcode')) {
            $formats = $validated['transcode_formats'] ?? ['720p', '480p', '360p'];
            $video->startTranscoding($formats);
        }

        return redirect()
            ->route('lms.modules.edit', $module)
            ->with('success', 'Video uploaded successfully.' .
                ($video->needsTranscoding() ? ' Transcoding will begin shortly.' : ''));
    }

    /**
     * Show video details
     */
    public function show(Video $video) {
        $video->load(['contentItemMorph.module.course', 'qualities', 'transcodingJobs']);

        return view('lms.videos.show', compact('video'));
    }

    /**
     * Video player view
     */
    public function player(Video $video) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('student.login');
        }

        // Verify enrollment
        $contentItem = $video->contentItemMorph ?? $video->contentItem;
        if ($contentItem) {
            $enrollment = Enrollment::where('course_id', $contentItem->module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return back()->with('error', 'You must be enrolled in this course.');
            }
        }

        $progress = $video->getProgressForStudent($student->id);

        return view('lms.videos.player', compact('video', 'progress', 'contentItem'));
    }

    /**
     * Edit video settings
     */
    public function edit(Video $video) {
        Gate::authorize('manage-lms-content');

        $video->load(['contentItemMorph.module.course', 'qualities', 'transcodingJobs']);

        return view('lms.videos.edit', compact('video'));
    }

    /**
     * Update video settings
     */
    public function update(Request $request, Video $video) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completion_threshold' => 'required|integer|min:50|max:100',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        // Update thumbnail if provided
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($video->thumbnail_path && Storage::disk('public')->exists($video->thumbnail_path)) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }

            $thumbnail = $request->file('thumbnail');
            $thumbName = uniqid('thumb_') . '.' . $thumbnail->getClientOriginalExtension();
            $thumbPath = $thumbnail->storeAs('lms/videos/thumbnails', $thumbName, 'public');
            $video->thumbnail_path = $thumbPath;
        }

        $video->completion_threshold = $validated['completion_threshold'];
        $video->save();

        // Update content item
        $contentItem = $video->contentItemMorph ?? $video->contentItem;
        if ($contentItem) {
            $contentItem->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);
        }

        return back()->with('success', 'Video updated successfully.');
    }

    /**
     * Start transcoding for a video
     */
    public function transcode(Request $request, Video $video) {
        Gate::authorize('manage-lms-content');

        if (!$video->isUpload()) {
            return back()->with('error', 'Only uploaded videos can be transcoded.');
        }

        if ($video->isTranscoding()) {
            return back()->with('error', 'Video is already being transcoded.');
        }

        $validated = $request->validate([
            'formats' => 'required|array|min:1',
            'formats.*' => 'in:1080p,720p,480p,360p',
        ]);

        $video->startTranscoding($validated['formats']);

        return back()->with('success', 'Transcoding started. This may take a while.');
    }

    /**
     * Get transcoding status (AJAX)
     */
    public function transcodingStatus(Video $video) {
        $jobs = $video->transcodingJobs()
            ->select(['id', 'format', 'status', 'progress', 'error_message'])
            ->get();

        return response()->json([
            'status' => $video->transcoding_status,
            'jobs' => $jobs,
        ]);
    }

    /**
     * Delete video
     */
    public function destroy(Video $video) {
        Gate::authorize('manage-lms-content');

        // Delete content item if exists
        $contentItem = $video->contentItemMorph ?? $video->contentItem;
        if ($contentItem) {
            $contentItem->delete();
        }

        // Delete all video files
        $video->deleteVideoFiles();

        $video->delete();

        return back()->with('success', 'Video deleted.');
    }

    /**
     * Update video watch progress (AJAX)
     */
    public function updateProgress(Request $request, Video $video) {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'current_time' => 'required|numeric|min:0',
            'duration' => 'required|numeric|min:1',
        ]);

        // Get enrollment for the course this video belongs to
        $contentItem = $video->contentItem;
        if (!$contentItem) {
            return response()->json(['error' => 'Video not found in course content'], 404);
        }

        $course = $contentItem->module->course;
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Not enrolled'], 403);
        }

        // Get or create video progress
        $progress = VideoProgress::firstOrCreate(
            [
                'video_id' => $video->id,
                'student_id' => $student->id,
            ],
            [
                'duration_seconds' => (int) $validated['duration'],
            ]
        );

        // Update progress
        $currentTime = (int) $validated['current_time'];
        $duration = (int) $validated['duration'];

        // Track highest position reached
        if ($currentTime > $progress->last_position_seconds) {
            $progress->last_position_seconds = $currentTime;
        }

        // Calculate watch percentage
        $watchPercentage = $duration > 0 ? round(($progress->last_position_seconds / $duration) * 100, 2) : 0;
        $progress->watch_percentage = min(100, $watchPercentage);

        // Increment watch count if starting from beginning
        if ($currentTime < 5 && !$progress->wasRecentlyCreated) {
            // Likely a new watch session if at beginning
        }

        // Mark as completed if watched most of the video (90% threshold)
        if ($progress->watch_percentage >= VideoProgress::COMPLETION_THRESHOLD && !$progress->completed_at) {
            $progress->completed_at = now();
            $progress->watch_count = ($progress->watch_count ?? 0) + 1;
        }

        $progress->last_watched_at = now();
        $progress->save();

        // Update video duration if not set
        if (!$video->duration_seconds && $duration > 0) {
            $video->update(['duration_seconds' => $duration]);
        }

        return response()->json([
            'success' => true,
            'watch_percentage' => $progress->watch_percentage,
            'completed' => $progress->completed_at !== null,
            'last_position' => $progress->last_position_seconds,
        ]);
    }

    /**
     * Log video events (play, pause, seek, complete)
     */
    public function logEvent(Request $request, Video $video) {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'event' => 'required|in:play,pause,seek,complete,buffer,error',
            'current_time' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
        ]);

        $progress = VideoProgress::where('video_id', $video->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$progress) {
            return response()->json(['success' => true]);
        }

        // Handle specific events
        switch ($validated['event']) {
            case 'complete':
                if (!$progress->completed_at) {
                    $progress->completed_at = now();
                    $progress->watch_count = ($progress->watch_count ?? 0) + 1;
                    $progress->watch_percentage = 100;
                    $progress->save();

                    // Also update content progress
                    $contentItem = $video->contentItem;
                    if ($contentItem) {
                        $enrollment = Enrollment::where('course_id', $contentItem->module->course_id)
                            ->where('student_id', $student->id)
                            ->first();

                        if ($enrollment) {
                            $contentProgress = $enrollment->contentProgress()
                                ->where('content_item_id', $contentItem->id)
                                ->first();

                            if ($contentProgress) {
                                $contentProgress->markAsCompleted();
                                $enrollment->calculateProgress();
                            }
                        }
                    }
                }
                break;

            case 'play':
                // Track play count or session start
                break;

            case 'seek':
                // Could log seek events for analytics
                break;

            case 'error':
                // Log video playback errors
                \Log::warning('Video playback error', [
                    'video_id' => $video->id,
                    'student_id' => $student->id,
                    'metadata' => $validated['metadata'] ?? [],
                ]);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get video progress for resume functionality
     */
    public function getProgress(Video $video) {
        $student = Auth::guard('student')->user();

        $progress = VideoProgress::where('video_id', $video->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$progress) {
            return response()->json([
                'last_position' => 0,
                'watch_percentage' => 0,
                'completed' => false,
            ]);
        }

        return response()->json([
            'last_position' => $progress->last_position_seconds,
            'watch_percentage' => $progress->watch_percentage,
            'completed' => $progress->completed_at !== null,
        ]);
    }
}
