<?php

namespace App\Models\Schemes;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandardScheme extends Model {
    use SoftDeletes;

    protected $table = 'standard_schemes';

    protected $fillable = [
        'subject_id',
        'grade_id',
        'term_id',
        'department_id',
        'created_by',
        'panel_lead_id',
        'status',
        'total_weeks',
        'review_comments',
        'reviewed_by',
        'reviewed_at',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'reviewed_at'  => 'datetime',
        'published_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function entries(): HasMany {
        return $this->hasMany(StandardSchemeEntry::class, 'standard_scheme_id')
            ->orderBy('week_number', 'asc');
    }

    public function subject(): BelongsTo {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function grade(): BelongsTo {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function term(): BelongsTo {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function panelLead(): BelongsTo {
        return $this->belongsTo(User::class, 'panel_lead_id');
    }

    public function reviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function publisher(): BelongsTo {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function contributors(): BelongsToMany {
        return $this->belongsToMany(User::class, 'standard_scheme_contributors', 'standard_scheme_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function derivedSchemes(): HasMany {
        return $this->hasMany(SchemeOfWork::class, 'standard_scheme_id');
    }

    public function workflowAudits(): HasMany {
        return $this->hasMany(StandardSchemeWorkflowAudit::class, 'standard_scheme_id')
            ->orderBy('created_at', 'desc');
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    /**
     * Filter standard schemes visible to a user based on their role.
     * - Scheme Admin, HOD, Academic Admin, Administrator: see all (or department-scoped for HOD)
     * - Scheme View: see all
     * - Teachers: see only those where they are contributors
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder {
        if ($user->hasAnyRoles(['Administrator', 'Academic Admin', 'Scheme Admin', 'Scheme View'])) {
            return $query;
        }

        if ($user->hasAnyRoles(['HOD'])) {
            $departmentIds = Department::query()
                ->where('department_head', $user->id)
                ->orWhere('assistant', $user->id)
                ->pluck('id');

            return $query->whereIn('department_id', $departmentIds);
        }

        // Teachers only see schemes they contribute to
        return $query->whereHas('contributors', function (Builder $q) use ($user): void {
            $q->where('user_id', $user->id);
        });
    }

    // ── Published scope ────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder {
        return $query->whereNotNull('published_at');
    }

    // ── Helper methods ─────────────────────────────────────────────────

    public function isEditable(): bool {
        return in_array($this->status, ['draft', 'revision_required'], true);
    }

    public function isPublished(): bool {
        return !is_null($this->published_at);
    }

    /**
     * Find all teachers assigned to teach this subject+grade in this term.
     * Returns distinct User models from KlassSubject and OptionalSubject.
     */
    public function getTeachersForSubject(): \Illuminate\Support\Collection {
        $gradeSubjectIds = GradeSubject::query()
            ->where('subject_id', $this->subject_id)
            ->where('term_id', $this->term_id)
            ->whereHas('grade', function (Builder $q): void {
                $q->where('id', $this->grade_id);
            })
            ->pluck('id');

        $klassTeacherIds = KlassSubject::query()
            ->whereIn('grade_subject_id', $gradeSubjectIds)
            ->where('term_id', $this->term_id)
            ->where('active', true)
            ->pluck('user_id');

        $optionalTeacherIds = OptionalSubject::query()
            ->whereIn('grade_subject_id', $gradeSubjectIds)
            ->where('term_id', $this->term_id)
            ->pluck('user_id');

        $allTeacherIds = $klassTeacherIds->merge($optionalTeacherIds)->unique()->filter();

        return User::whereIn('id', $allTeacherIds)->get();
    }

    /**
     * Find the published standard scheme for a given subject+grade+term.
     * Used by the individual scheme show page to resolve the Browse Scheme drawer.
     */
    public static function findPublishedFor(int $subjectId, int $gradeId, int $termId): ?self {
        return static::query()
            ->where('subject_id', $subjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->whereNotNull('published_at')
            ->first();
    }
}
