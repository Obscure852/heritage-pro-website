<?php

namespace App\Models\Lms;

use App\Models\Grade;
use App\Models\Klass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarEvent extends Model {
    protected $table = 'lms_calendar_events';

    protected $fillable = [
        'title',
        'description',
        'type',
        'color',
        'start_date',
        'end_date',
        'all_day',
        'location',
        'meeting_url',
        'course_id',
        'eventable_type',
        'eventable_id',
        'recurrence_rule',
        'parent_event_id',
        'is_published',
        'audience_scope',
        'notify_students',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'all_day' => 'boolean',
        'is_published' => 'boolean',
        'notify_students' => 'boolean',
    ];

    public static array $eventTypes = [
        'class' => 'Class Session',
        'assignment' => 'Assignment Due',
        'quiz' => 'Quiz',
        'meeting' => 'Meeting',
        'office_hours' => 'Office Hours',
        'holiday' => 'Holiday',
        'deadline' => 'Deadline',
        'custom' => 'Custom Event',
    ];

    public static array $colors = [
        'class' => '#3b82f6',      // Blue
        'assignment' => '#f59e0b', // Amber
        'quiz' => '#ef4444',       // Red
        'meeting' => '#8b5cf6',    // Purple
        'office_hours' => '#10b981', // Green
        'holiday' => '#6b7280',    // Gray
        'deadline' => '#ec4899',   // Pink
        'custom' => '#6366f1',     // Indigo
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function eventable(): MorphTo {
        return $this->morphTo();
    }

    public function parentEvent(): BelongsTo {
        return $this->belongsTo(self::class, 'parent_event_id');
    }

    public function childEvents(): HasMany {
        return $this->hasMany(self::class, 'parent_event_id');
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): HasMany {
        return $this->hasMany(EventAttendee::class, 'event_id');
    }

    public function reminders(): HasMany {
        return $this->hasMany(EventReminder::class, 'event_id');
    }

    /**
     * Get all audience targets (polymorphic).
     */
    public function audiences(): HasMany {
        return $this->hasMany(EventAttendee::class, 'event_id');
    }

    /**
     * Get course audiences.
     */
    public function courseAudiences(): HasMany {
        return $this->audiences()->where('attendee_type', Course::class);
    }

    /**
     * Get grade audiences.
     */
    public function gradeAudiences(): HasMany {
        return $this->audiences()->where('attendee_type', Grade::class);
    }

    /**
     * Get class audiences.
     */
    public function classAudiences(): HasMany {
        return $this->audiences()->where('attendee_type', Klass::class);
    }

    public function scopePublished($query) {
        return $query->where('is_published', true);
    }

    public function scopeForCourse($query, int $courseId) {
        return $query->where('course_id', $courseId);
    }

    public function scopeInDateRange($query, $start, $end) {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
              ->orWhereBetween('end_date', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  $q2->where('start_date', '<=', $start)
                     ->where('end_date', '>=', $end);
              });
        });
    }

    public function scopeUpcoming($query) {
        return $query->where('start_date', '>=', now())
                     ->orderBy('start_date');
    }

    /**
     * Scope: Events visible to a specific student based on audience targeting.
     */
    public function scopeVisibleToStudent($query, Student $student) {
        return $query->where('is_published', true)
            ->where(function ($q) use ($student) {
                // Scope: all - visible to everyone
                $q->where('audience_scope', 'all')

                // Scope: course - student enrolled in one of the target courses
                ->orWhere(function ($q2) use ($student) {
                    $q2->where('audience_scope', 'course')
                       ->whereHas('audiences', function ($q3) use ($student) {
                           $q3->where('attendee_type', Course::class)
                              ->whereIn('attendee_id', $student->enrolledCourseIds());
                       });
                })

                // Scope: grade - student in one of the target grades
                ->orWhere(function ($q2) use ($student) {
                    $q2->where('audience_scope', 'grade')
                       ->whereHas('audiences', function ($q3) use ($student) {
                           $q3->where('attendee_type', Grade::class)
                              ->where('attendee_id', $student->currentGrade?->id);
                       });
                })

                // Scope: class - student in one of the target classes
                ->orWhere(function ($q2) use ($student) {
                    $q2->where('audience_scope', 'class')
                       ->whereHas('audiences', function ($q3) use ($student) {
                           $q3->where('attendee_type', Klass::class)
                              ->where('attendee_id', $student->currentClass?->id);
                       });
                })

                // Scope: mixed - any of the above audiences match
                ->orWhere(function ($q2) use ($student) {
                    $q2->where('audience_scope', 'mixed')
                       ->whereHas('audiences', function ($q3) use ($student) {
                           $q3->where(function ($q4) use ($student) {
                               $q4->where('attendee_type', Course::class)
                                  ->whereIn('attendee_id', $student->enrolledCourseIds());
                           })
                           ->orWhere(function ($q4) use ($student) {
                               $q4->where('attendee_type', Grade::class)
                                  ->where('attendee_id', $student->currentGrade?->id);
                           })
                           ->orWhere(function ($q4) use ($student) {
                               $q4->where('attendee_type', Klass::class)
                                  ->where('attendee_id', $student->currentClass?->id);
                           });
                       });
                });
            });
    }

    /**
     * Sync audience targets.
     */
    public function syncAudiences(array $audiences): void {
        $this->audiences()->delete();

        foreach ($audiences as $audience) {
            $this->audiences()->create([
                'attendee_type' => $audience['type'],
                'attendee_id' => $audience['id'],
            ]);
        }
    }

    public function toFullCalendarEvent(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start_date->toIso8601String(),
            'end' => $this->end_date?->toIso8601String(),
            'allDay' => $this->all_day,
            'color' => $this->color,
            'url' => $this->eventable ? route('lms.courses.learn', $this->course_id) : null,
            'extendedProps' => [
                'type' => $this->type,
                'description' => $this->description,
                'location' => $this->location,
                'meeting_url' => $this->meeting_url,
                'course_id' => $this->course_id,
                'audience_scope' => $this->audience_scope,
            ],
        ];
    }
}
