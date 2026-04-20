<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class H5pContent extends Model
{
    use HasFactory;

    protected $table = 'lms_h5p_contents';

    protected $fillable = [
        'title',
        'description',
        'library',
        'library_major_version',
        'library_minor_version',
        'parameters',
        'embed_type',
        'content_path',
        'package_path',
        'package_size',
        'disable',
        'slug',
        'uploaded_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'disable' => 'boolean',
    ];

    // Common H5P content types
    public const CONTENT_TYPES = [
        'H5P.InteractiveVideo' => 'Interactive Video',
        'H5P.CoursePresentation' => 'Course Presentation',
        'H5P.QuestionSet' => 'Quiz (Question Set)',
        'H5P.DragQuestion' => 'Drag and Drop',
        'H5P.MultiChoice' => 'Multiple Choice',
        'H5P.TrueFalse' => 'True/False',
        'H5P.Blanks' => 'Fill in the Blanks',
        'H5P.MarkTheWords' => 'Mark the Words',
        'H5P.DragText' => 'Drag Text',
        'H5P.Accordion' => 'Accordion',
        'H5P.Timeline' => 'Timeline',
        'H5P.ImageHotspots' => 'Image Hotspots',
        'H5P.Column' => 'Column Layout',
        'H5P.InteractiveBook' => 'Interactive Book',
        'H5P.BranchingScenario' => 'Branching Scenario',
        'H5P.Flashcards' => 'Flashcards',
        'H5P.MemoryGame' => 'Memory Game',
        'H5P.ArithmeticQuiz' => 'Arithmetic Quiz',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->title) . '-' . Str::random(6);
            }
        });
    }

    // Relationships
    public function contentItem(): MorphOne
    {
        return $this->morphOne(ContentItem::class, 'contentable');
    }

    public function results(): HasMany
    {
        return $this->hasMany(H5pResult::class, 'h5p_content_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(H5pEvent::class, 'h5p_content_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors
    public function getLibraryNameAttribute(): string
    {
        // Extract library name without version (e.g., "H5P.InteractiveVideo" from "H5P.InteractiveVideo 1.21")
        return explode(' ', $this->library)[0] ?? $this->library;
    }

    public function getLibraryDisplayNameAttribute(): string
    {
        return self::CONTENT_TYPES[$this->library_name] ?? $this->library_name;
    }

    public function getEmbedUrlAttribute(): string
    {
        if ($this->content_path) {
            return Storage::disk('public')->url($this->content_path . '/content/');
        }

        return '';
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->package_size) {
            return 'Unknown';
        }

        $bytes = $this->package_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Methods
    public function getOrCreateResult(int $studentId, ?int $contentItemId = null): H5pResult
    {
        return H5pResult::firstOrCreate([
            'h5p_content_id' => $this->id,
            'student_id' => $studentId,
        ], [
            'content_item_id' => $contentItemId,
            'first_opened_at' => now(),
        ]);
    }

    public function recordEvent(int $studentId, string $verb, array $data = []): H5pEvent
    {
        return $this->events()->create([
            'student_id' => $studentId,
            'verb' => $verb,
            'object_type' => $data['object_type'] ?? null,
            'object_id' => $data['object_id'] ?? null,
            'result' => $data['result'] ?? null,
            'context' => $data['context'] ?? null,
        ]);
    }

    public function deleteContentFiles(): void
    {
        if ($this->package_path && Storage::disk('public')->exists($this->package_path)) {
            Storage::disk('public')->delete($this->package_path);
        }

        if ($this->content_path && Storage::disk('public')->exists($this->content_path)) {
            Storage::disk('public')->deleteDirectory($this->content_path);
        }
    }
}
