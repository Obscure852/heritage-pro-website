<?php

namespace App\Models\Schemes;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonPlan extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'lesson_plans';

    protected $fillable = [
        'scheme_of_work_id',
        'scheme_of_work_entry_id',
        'teacher_id',
        'date',
        'period',
        'topic',
        'sub_topic',
        'learning_objectives',
        'content',
        'activities',
        'teaching_learning_aids',
        'lesson_evaluation',
        'resources',
        'homework',
        'status',
        'taught_at',
        'reflection_notes',
        'supervisor_reviewed_by',
        'supervisor_reviewed_at',
        'supervisor_comments',
        'reviewed_by',
        'reviewed_at',
        'review_comments',
    ];

    protected $casts = [
        'date' => 'date',
        'taught_at' => 'datetime',
        'supervisor_reviewed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function scheme(): BelongsTo {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_of_work_id');
    }

    public function entry(): BelongsTo {
        return $this->belongsTo(SchemeOfWorkEntry::class, 'scheme_of_work_entry_id');
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function supervisorReviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'supervisor_reviewed_by');
    }

    public function reviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isEditable(): bool {
        return in_array($this->status, ['draft', 'revision_required'], true);
    }

    public function isDraft(): bool {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool {
        return $this->status === 'approved';
    }

    /**
     * Determine whether this lesson plan requires a supervisor review step.
     * Returns false if teacher has no supervisor, or if the supervisor IS the HOD/assistant.
     */
    public function requiresSupervisorReview(): bool {
        $teacher = $this->teacher;
        if (!$teacher || is_null($teacher->reporting_to)) {
            return false;
        }

        $supervisorId = (int) $teacher->reporting_to;

        // If this plan is linked to a scheme, check if supervisor is HOD for the scheme's department
        $scheme = $this->scheme;
        if ($scheme) {
            $gradeSubject = $scheme->gradeSubject;
            if ($gradeSubject && !is_null($gradeSubject->department_id)) {
                $department = Department::find($gradeSubject->department_id);
                if ($department) {
                    if ((!is_null($department->department_head) && (int) $department->department_head === $supervisorId)
                        || (!is_null($department->assistant) && (int) $department->assistant === $supervisorId)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
