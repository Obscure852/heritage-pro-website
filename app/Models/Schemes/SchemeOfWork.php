<?php

namespace App\Models\Schemes;

use App\Models\Department;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\GradeSubject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

class SchemeOfWork extends Model {
    use HasFactory, SoftDeletes;

    private const SCHOOL_WIDE_VIEW_ROLES = [
        'Administrator',
        'Academic Admin',
        'HOD',
    ];

    protected $table = 'schemes_of_work';

    protected $fillable = [
        'klass_subject_id',
        'optional_subject_id',
        'term_id',
        'teacher_id',
        'status',
        'is_published',
        'published_at',
        'published_by',
        'review_comments',
        'reviewed_by',
        'reviewed_at',
        'supervisor_reviewed_by',
        'supervisor_reviewed_at',
        'supervisor_comments',
        'total_weeks',
        'cloned_from_id',
        'standard_scheme_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'supervisor_reviewed_at' => 'datetime',
    ];

    /**
     * Boot method — registers the XOR saving guard.
     * Enforces that exactly one of klass_subject_id or optional_subject_id is set.
     * This is the app-layer enforcement for FOUN-03 (DB-layer CHECK rejected by InnoDB FK constraint).
     */
    protected static function booted(): void {
        static::saving(function (SchemeOfWork $scheme): void {
            $hasKlass = !is_null($scheme->klass_subject_id);
            $hasOptional = !is_null($scheme->optional_subject_id);

            if ($hasKlass && $hasOptional) {
                throw new InvalidArgumentException(
                    'SchemeOfWork cannot have both klass_subject_id and optional_subject_id set.'
                );
            }

            if (!$hasKlass && !$hasOptional) {
                throw new InvalidArgumentException(
                    'SchemeOfWork must have either klass_subject_id or optional_subject_id set — not neither.'
                );
            }
        });
    }

    /**
     * Accessor: resolves the underlying GradeSubject regardless of XOR FK path.
     * Used by SchemeOfWorkPolicy for HOD department chain resolution.
     */
    public function getGradeSubjectAttribute(): ?GradeSubject {
        if (!is_null($this->klass_subject_id)) {
            return $this->klassSubject?->gradeSubject;
        }

        if (!is_null($this->optional_subject_id)) {
            return $this->optionalSubject?->gradeSubject;
        }

        return null;
    }

    public function entries(): HasMany {
        return $this->hasMany(SchemeOfWorkEntry::class, 'scheme_of_work_id')->orderBy('week_number', 'asc');
    }

    public function klassSubject(): BelongsTo {
        return $this->belongsTo(KlassSubject::class, 'klass_subject_id');
    }

    public function optionalSubject(): BelongsTo {
        return $this->belongsTo(OptionalSubject::class, 'optional_subject_id');
    }

    public function term(): BelongsTo {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function reviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publisher(): BelongsTo {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function supervisorReviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'supervisor_reviewed_by');
    }

    /**
     * Determine whether this scheme requires a supervisor review step.
     * Returns false if teacher has no supervisor, or if the supervisor IS the HOD/assistant for the department.
     */
    public function requiresSupervisorReview(): bool {
        $teacher = $this->teacher;
        if (!$teacher || is_null($teacher->reporting_to)) {
            return false;
        }

        $supervisorId = (int) $teacher->reporting_to;

        // Check if the supervisor is the HOD or assistant for this scheme's department
        $gradeSubject = $this->gradeSubject;
        if ($gradeSubject && !is_null($gradeSubject->department_id)) {
            $department = Department::find($gradeSubject->department_id);
            if ($department) {
                if ((!is_null($department->department_head) && (int) $department->department_head === $supervisorId)
                    || (!is_null($department->assistant) && (int) $department->assistant === $supervisorId)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns true if this scheme has already passed supervisor review at least once.
     * Used to skip supervisor on resubmission after HOD return.
     */
    public function hasPassedSupervisorReview(): bool {
        return !is_null($this->supervisor_reviewed_by);
    }

    public function lessonPlans(): HasMany {
        return $this->hasMany(LessonPlan::class, 'scheme_of_work_id');
    }

    public function clonedFrom(): BelongsTo {
        return $this->belongsTo(self::class, 'cloned_from_id');
    }

    public function standardScheme(): BelongsTo {
        return $this->belongsTo(StandardScheme::class, 'standard_scheme_id');
    }

    /**
     * Returns true if this scheme was distributed from a standard scheme.
     * When true, entries are read-only for the teacher.
     */
    public function isDerivedFromStandard(): bool {
        return !is_null($this->standard_scheme_id);
    }

    public function workflowAudits(): HasMany {
        return $this->hasMany(SchemeWorkflowAudit::class, 'scheme_of_work_id')->orderBy('created_at', 'desc');
    }

    public function scopeForGradeSubject(Builder $query, int $gradeSubjectId): Builder
    {
        return $query->where(function (Builder $builder) use ($gradeSubjectId): void {
            $builder->whereHas('klassSubject', function (Builder $klassQuery) use ($gradeSubjectId): void {
                $klassQuery->where('grade_subject_id', $gradeSubjectId);
            })->orWhereHas('optionalSubject', function (Builder $optionalQuery) use ($gradeSubjectId): void {
                $optionalQuery->where('grade_subject_id', $gradeSubjectId);
            });
        });
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (self::canViewAllTeacherSchemes($user)) {
            return $query;
        }

        $teacherIds = self::visibleTeacherIdsFor($user);

        if (empty($teacherIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($query->getModel()->qualifyColumn('teacher_id'), $teacherIds);
    }

    public static function canViewAllTeacherSchemes(User $user): bool
    {
        return $user->hasAnyRoles(self::SCHOOL_WIDE_VIEW_ROLES);
    }

    public static function visibilityModeFor(User $user): string
    {
        if (self::canViewAllTeacherSchemes($user)) {
            return 'all';
        }

        return empty(self::subordinateTeacherIdsFor($user)) ? 'own' : 'supervised';
    }

    /**
     * @return array<int>
     */
    public static function visibleTeacherIdsFor(User $user): array
    {
        $teacherIds = array_merge([$user->id], self::subordinateTeacherIdsFor($user));

        return array_values(array_unique(array_map('intval', $teacherIds)));
    }

    public static function canUserView(User $user, self $scheme): bool
    {
        if (self::canViewAllTeacherSchemes($user)) {
            return true;
        }

        return in_array((int) $scheme->teacher_id, self::visibleTeacherIdsFor($user), true);
    }

    /**
     * @return array<int>
     */
    private static function subordinateTeacherIdsFor(User $user): array
    {
        $collected = [];
        $frontier = [$user->id];

        $maxIterations = 50;
        while (!empty($frontier) && $maxIterations-- > 0) {
            $directReports = User::query()
                ->whereIn('reporting_to', $frontier)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $frontier = array_values(array_diff($directReports, $collected, [$user->id]));

            if (empty($frontier)) {
                break;
            }

            $collected = array_values(array_unique(array_merge($collected, $frontier)));
        }

        return $collected;
    }
}
