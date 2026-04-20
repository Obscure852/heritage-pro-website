<?php

namespace App\Http\Requests\Lms;

use App\Models\Lms\ContentItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Gate::allows('manage-lms-content');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        $baseValidation = [
            'type' => 'required|in:' . implode(',', array_keys(ContentItem::CONTENT_TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_required' => 'boolean',
            'estimated_duration' => 'nullable|integer|min:1|max:480',
        ];

        $type = $this->input('type');

        $typeValidation = match ($type) {
            'text' => ['content' => 'required|string|max:100000'],
            'document' => ['file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:20480'],
            'video_youtube' => ['youtube_url' => ['required', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/']],
            'video_upload' => ['video_file' => 'required|file|mimes:mp4,webm,mov|max:512000'],
            'audio' => ['audio_file' => 'required|file|mimes:mp3,wav,ogg|max:51200'],
            'image' => ['image_file' => 'required|image|max:10240'],
            'quiz' => [
                'quiz_instructions' => 'nullable|string|max:5000',
                'quiz_time_limit' => 'nullable|integer|min:1|max:480',
                'quiz_passing_score' => 'nullable|integer|min:0|max:100',
                'quiz_max_attempts' => 'nullable|integer|min:1|max:100',
                'quiz_shuffle_questions' => 'boolean',
            ],
            'external_link' => ['external_url' => 'required|url|max:2000'],
            default => [],
        };

        return array_merge($baseValidation, $typeValidation);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'type.required' => 'Please select a content type.',
            'type.in' => 'Invalid content type selected.',
            'title.required' => 'Please provide a title for the content.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'content.required' => 'Text content is required.',
            'file.required' => 'Please upload a document file.',
            'file.mimes' => 'Document must be a PDF, DOC, DOCX, PPT, or PPTX file.',
            'file.max' => 'Document cannot exceed 20MB.',
            'youtube_url.required' => 'Please provide a YouTube URL.',
            'youtube_url.regex' => 'Please provide a valid YouTube URL.',
            'video_file.required' => 'Please upload a video file.',
            'video_file.mimes' => 'Video must be an MP4, WEBM, or MOV file.',
            'video_file.max' => 'Video cannot exceed 500MB.',
            'audio_file.required' => 'Please upload an audio file.',
            'audio_file.mimes' => 'Audio must be an MP3, WAV, or OGG file.',
            'audio_file.max' => 'Audio cannot exceed 50MB.',
            'image_file.required' => 'Please upload an image.',
            'image_file.image' => 'The file must be an image.',
            'image_file.max' => 'Image cannot exceed 10MB.',
            'external_url.required' => 'Please provide an external URL.',
            'external_url.url' => 'Please provide a valid URL.',
        ];
    }
}
