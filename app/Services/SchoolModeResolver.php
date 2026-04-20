<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\StudentTerm;
use App\Models\User;
use App\Services\Finals\FinalsContextDefinition;
use App\Support\AcademicStructureRegistry;
use Illuminate\Support\Collection;

class SchoolModeResolver
{
    private const MARKBOOK_ADMIN_ROLES = [
        'Administrator',
        'Academic Admin',
        'Assessment Admin',
        'HOD',
    ];

    public const ASSESSMENT_CONTEXT_PRIMARY = 'primary';
    public const ASSESSMENT_CONTEXT_JUNIOR = 'junior';
    public const ASSESSMENT_CONTEXT_SENIOR = 'senior';

    public const FINALS_CONTEXT_JUNIOR = 'junior';
    public const FINALS_CONTEXT_SENIOR = 'senior';
    public const FINALS_CONTEXT_SESSION_KEY = 'finals_context';

    public const FILTER_ALL = 'all';
    public const FILTER_PRE_PRIMARY_PRIMARY = 'pre_primary_primary';
    public const FILTER_JUNIOR = 'junior';
    public const FILTER_SENIOR = 'senior';

    /**
     * @var array<string, array<int, string>>
     */
    private array $accessibleMarkbookContextsCache = [];

    /**
     * @return string
     */
    public function mode(): string
    {
        return SchoolSetup::schoolType() ?? SchoolSetup::TYPE_JUNIOR;
    }

    /**
     * @return array<int, string>
     */
    public function supportedLevels(?string $mode = null): array
    {
        return match (SchoolSetup::normalizeType($mode ?? $this->mode())) {
            SchoolSetup::TYPE_PRIMARY => [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY],
            SchoolSetup::TYPE_JUNIOR => [SchoolSetup::LEVEL_JUNIOR],
            SchoolSetup::TYPE_SENIOR => [SchoolSetup::LEVEL_SENIOR],
            SchoolSetup::TYPE_PRE_F3 => [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY, SchoolSetup::LEVEL_JUNIOR],
            SchoolSetup::TYPE_JUNIOR_SENIOR => [SchoolSetup::LEVEL_JUNIOR, SchoolSetup::LEVEL_SENIOR],
            SchoolSetup::TYPE_K12 => [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY, SchoolSetup::LEVEL_JUNIOR, SchoolSetup::LEVEL_SENIOR],
            default => [SchoolSetup::LEVEL_JUNIOR],
        };
    }

    public function normalizeLevel(?string $level): ?string
    {
        if ($level === null) {
            return null;
        }

        return match (trim($level)) {
            'Preschool' => SchoolSetup::LEVEL_PRE_PRIMARY,
            default => trim($level),
        };
    }

    public function subjectLevelForLevel(?string $level): ?string
    {
        return match ($this->normalizeLevel($level)) {
            SchoolSetup::LEVEL_PRE_PRIMARY => 'Preschool',
            default => $this->normalizeLevel($level),
        };
    }

    public function levelForGrade(Grade|int|null $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        if (is_int($grade)) {
            $grade = Grade::find($grade);
        }

        return $grade ? $this->normalizeLevel($grade->level) : null;
    }

    public function levelForKlass(Klass|int|null $klass): ?string
    {
        if ($klass === null) {
            return null;
        }

        if (is_int($klass)) {
            $klass = Klass::with('grade')->find($klass);
        }

        return $klass?->grade ? $this->levelForGrade($klass->grade) : null;
    }

    public function levelForStudent(Student|int|null $student, ?int $termId = null): ?string
    {
        if ($student === null) {
            return null;
        }

        if (is_int($student)) {
            $student = Student::find($student);
        }

        if (!$student) {
            return null;
        }

        if ($student->relationLoaded('currentGrade') && $student->currentGrade) {
            return $this->levelForGrade($student->currentGrade);
        }

        if ($termId !== null) {
            $studentTerm = StudentTerm::with('grade')
                ->where('student_id', $student->id)
                ->where('term_id', $termId)
                ->where('status', 'Current')
                ->first();

            return $studentTerm?->grade ? $this->levelForGrade($studentTerm->grade) : null;
        }

        $currentGrade = $student->currentGrade()->first();

        return $currentGrade ? $this->levelForGrade($currentGrade) : null;
    }

    public function isCombinedMode(?string $mode = null): bool
    {
        return in_array(
            SchoolSetup::normalizeType($mode ?? $this->mode()),
            [SchoolSetup::TYPE_PRE_F3, SchoolSetup::TYPE_JUNIOR_SENIOR, SchoolSetup::TYPE_K12],
            true
        );
    }

    public function supportsLevel(?string $level, ?string $mode = null): bool
    {
        $normalizedLevel = $this->normalizeLevel($level);

        return $normalizedLevel !== null
            && in_array($normalizedLevel, $this->supportedLevels($mode), true);
    }

    public function supportsFinals(?string $level = null, ?string $mode = null): bool
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $resolvedLevel = $this->normalizeLevel($level);

        if ($resolvedLevel === null) {
            return in_array($resolvedMode, [
                SchoolSetup::TYPE_JUNIOR,
                SchoolSetup::TYPE_SENIOR,
                SchoolSetup::TYPE_PRE_F3,
                SchoolSetup::TYPE_JUNIOR_SENIOR,
                SchoolSetup::TYPE_K12,
            ], true);
        }

        return match ($resolvedLevel) {
            SchoolSetup::LEVEL_JUNIOR => in_array($resolvedMode, [
                SchoolSetup::TYPE_JUNIOR,
                SchoolSetup::TYPE_PRE_F3,
                SchoolSetup::TYPE_JUNIOR_SENIOR,
                SchoolSetup::TYPE_K12,
            ], true),
            SchoolSetup::LEVEL_SENIOR => in_array($resolvedMode, [
                SchoolSetup::TYPE_SENIOR,
                SchoolSetup::TYPE_JUNIOR_SENIOR,
                SchoolSetup::TYPE_K12,
            ], true),
            default => false,
        };
    }

    public function supportsOptionals(?string $level = null, ?string $mode = null): bool
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $resolvedLevel = $this->normalizeLevel($level);

        if ($resolvedLevel === null) {
            return in_array($resolvedMode, [
                SchoolSetup::TYPE_JUNIOR,
                SchoolSetup::TYPE_SENIOR,
                SchoolSetup::TYPE_PRE_F3,
                SchoolSetup::TYPE_JUNIOR_SENIOR,
                SchoolSetup::TYPE_K12,
            ], true);
        }

        return match ($resolvedLevel) {
            SchoolSetup::LEVEL_JUNIOR => in_array($resolvedMode, [
                SchoolSetup::TYPE_JUNIOR,
                SchoolSetup::TYPE_PRE_F3,
                SchoolSetup::TYPE_JUNIOR_SENIOR,
                SchoolSetup::TYPE_K12,
            ], true),
            SchoolSetup::LEVEL_SENIOR => in_array($resolvedMode, [
                SchoolSetup::TYPE_SENIOR,
                SchoolSetup::TYPE_JUNIOR_SENIOR,
                SchoolSetup::TYPE_K12,
            ], true),
            default => false,
        };
    }

    /**
     * Finals contexts available for the current school mode.
     *
     * Mirrors availableAssessmentContexts() but scoped to the Finals module,
     * which only deals with Junior (JCE/PSLE) and Senior (BGCSE) school types.
     *
     * @return array<int, string>
     */
    public function availableFinalsContexts(?string $mode = null): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $contexts = [];

        if ($this->supportsFinals(SchoolSetup::LEVEL_JUNIOR, $resolvedMode)) {
            $contexts[] = self::FINALS_CONTEXT_JUNIOR;
        }

        if ($this->supportsFinals(SchoolSetup::LEVEL_SENIOR, $resolvedMode)) {
            $contexts[] = self::FINALS_CONTEXT_SENIOR;
        }

        return $contexts;
    }

    public function defaultFinalsContext(?string $mode = null): string
    {
        return $this->availableFinalsContexts($mode)[0] ?? self::FINALS_CONTEXT_JUNIOR;
    }

    public function resolveFinalsContext(?string $context = null, ?string $mode = null): ?string
    {
        if ($context === null || trim($context) === '') {
            return null;
        }

        $normalized = strtolower(trim($context));

        return in_array($normalized, $this->availableFinalsContexts($mode), true)
            ? $normalized
            : null;
    }

    /**
     * Resolve the active finals context for the request:
     * 1. Explicit context arg if valid
     * 2. Session value if valid
     * 3. Default for the school mode
     */
    public function currentFinalsContext(?string $context = null, ?string $mode = null): string
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());

        return $this->resolveFinalsContext($context, $resolvedMode)
            ?? $this->resolveFinalsContext(session(self::FINALS_CONTEXT_SESSION_KEY), $resolvedMode)
            ?? $this->defaultFinalsContext($resolvedMode);
    }

    public function isCombinedFinalsMode(?string $mode = null): bool
    {
        return count($this->availableFinalsContexts($mode)) > 1;
    }

    public function finalsDefinition(?string $context = null, ?string $mode = null): FinalsContextDefinition
    {
        $resolvedContext = $this->currentFinalsContext($context, $mode);

        return app(\App\Services\Finals\FinalsContextRegistry::class)->definition($resolvedContext);
    }

    public function finalsContextLabel(string $context): string
    {
        return $this->finalsDefinition($context)->examLabel;
    }

    public function finalsContextDescription(string $context): string
    {
        return $this->finalsDefinition($context)->description;
    }

    /**
     * Map a finals context to the underlying grade level it operates on.
     */
    public function levelForFinalsContext(string $context): string
    {
        return match ($context) {
            self::FINALS_CONTEXT_SENIOR => SchoolSetup::LEVEL_SENIOR,
            default => SchoolSetup::LEVEL_JUNIOR,
        };
    }

    /**
     * @return array<int, string>
     */
    public function optionalLevels(?string $mode = null): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $levels = [];

        if ($this->supportsOptionals(SchoolSetup::LEVEL_JUNIOR, $resolvedMode)) {
            $levels[] = SchoolSetup::LEVEL_JUNIOR;
        }

        if ($this->supportsOptionals(SchoolSetup::LEVEL_SENIOR, $resolvedMode)) {
            $levels[] = SchoolSetup::LEVEL_SENIOR;
        }

        return $levels;
    }

    public function supportsSeniorAdmissions(?string $mode = null): bool
    {
        return in_array(
            SchoolSetup::normalizeType($mode ?? $this->mode()),
            [SchoolSetup::TYPE_SENIOR, SchoolSetup::TYPE_JUNIOR_SENIOR, SchoolSetup::TYPE_K12],
            true
        );
    }

    /**
     * @return array<int, string>
     */
    public function valueAdditionSchoolTypes(?string $mode = null): array
    {
        return match (SchoolSetup::normalizeType($mode ?? $this->mode())) {
            SchoolSetup::TYPE_JUNIOR,
            SchoolSetup::TYPE_PRE_F3 => [SchoolSetup::TYPE_JUNIOR],
            SchoolSetup::TYPE_SENIOR => [SchoolSetup::TYPE_SENIOR],
            SchoolSetup::TYPE_JUNIOR_SENIOR => [SchoolSetup::TYPE_JUNIOR, SchoolSetup::TYPE_SENIOR],
            SchoolSetup::TYPE_K12 => [SchoolSetup::TYPE_JUNIOR, SchoolSetup::TYPE_SENIOR],
            default => [],
        };
    }

    public function assessmentDriverForLevel(?string $level): string
    {
        return match ($this->normalizeLevel($level)) {
            SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY => 'primary',
            SchoolSetup::LEVEL_SENIOR => 'senior',
            default => 'junior',
        };
    }

    public function portalReportCardDriverForLevel(?string $level): string
    {
        return $this->assessmentDriverForLevel($level);
    }

    /**
     * @return array<int, string>
     */
    public function availableAssessmentContexts(?string $mode = null): array
    {
        return match (SchoolSetup::normalizeType($mode ?? $this->mode())) {
            SchoolSetup::TYPE_PRIMARY => [self::ASSESSMENT_CONTEXT_PRIMARY],
            SchoolSetup::TYPE_JUNIOR => [self::ASSESSMENT_CONTEXT_JUNIOR],
            SchoolSetup::TYPE_SENIOR => [self::ASSESSMENT_CONTEXT_SENIOR],
            SchoolSetup::TYPE_PRE_F3 => [
                self::ASSESSMENT_CONTEXT_PRIMARY,
                self::ASSESSMENT_CONTEXT_JUNIOR,
            ],
            SchoolSetup::TYPE_JUNIOR_SENIOR => [
                self::ASSESSMENT_CONTEXT_JUNIOR,
                self::ASSESSMENT_CONTEXT_SENIOR,
            ],
            SchoolSetup::TYPE_K12 => [
                self::ASSESSMENT_CONTEXT_PRIMARY,
                self::ASSESSMENT_CONTEXT_JUNIOR,
                self::ASSESSMENT_CONTEXT_SENIOR,
            ],
            default => [self::ASSESSMENT_CONTEXT_JUNIOR],
        };
    }

    public function defaultAssessmentContext(?string $mode = null): string
    {
        return $this->availableAssessmentContexts($mode)[0] ?? self::ASSESSMENT_CONTEXT_JUNIOR;
    }

    public function resolveAssessmentContext(?string $context = null, ?string $mode = null): ?string
    {
        if ($context === null || trim($context) === '') {
            return null;
        }

        $normalized = strtolower(trim($context));

        return in_array($normalized, $this->availableAssessmentContexts($mode), true)
            ? $normalized
            : null;
    }

    /**
     * @return array<int, string>
     */
    public function levelsForAssessmentContext(?string $context = null, ?string $mode = null): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $resolvedContext = $this->resolveAssessmentContext($context, $resolvedMode)
            ?? $this->defaultAssessmentContext($resolvedMode);

        return match ($resolvedContext) {
            self::ASSESSMENT_CONTEXT_PRIMARY => array_values(array_intersect(
                $this->supportedLevels($resolvedMode),
                [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY]
            )),
            self::ASSESSMENT_CONTEXT_SENIOR => array_values(array_intersect(
                $this->supportedLevels($resolvedMode),
                [SchoolSetup::LEVEL_SENIOR]
            )),
            default => array_values(array_intersect(
                $this->supportedLevels($resolvedMode),
                [SchoolSetup::LEVEL_JUNIOR]
            )),
        };
    }

    public function gradebookRouteName(?string $context = null, ?string $mode = null): string
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $resolvedContext = $this->resolveAssessmentContext($context, $resolvedMode);

        if ($resolvedContext === null) {
            if ($this->isCombinedMode($resolvedMode)) {
                return 'assessment.index';
            }

            $resolvedContext = $this->defaultAssessmentContext($resolvedMode);
        }

        return match ($resolvedContext) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'assessment.gradebook.primary',
            self::ASSESSMENT_CONTEXT_SENIOR => 'assessment.gradebook.senior',
            default => 'assessment.gradebook.junior',
        };
    }

    public function gradebookUrl(?string $context = null, ?string $mode = null): string
    {
        return route($this->gradebookRouteName($context, $mode));
    }

    public function markbookRouteName(?string $context = null, ?string $mode = null): string
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $resolvedContext = $this->resolveAssessmentContext($context, $resolvedMode);

        if ($resolvedContext === null) {
            if ($this->isCombinedMode($resolvedMode)) {
                return 'assessment.markbook';
            }

            $resolvedContext = $this->defaultAssessmentContext($resolvedMode);
        }

        return match ($resolvedContext) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'assessment.markbook.primary',
            self::ASSESSMENT_CONTEXT_SENIOR => 'assessment.markbook.senior',
            default => 'assessment.markbook.junior',
        };
    }

    public function markbookUrl(?string $context = null, ?string $mode = null): string
    {
        return route($this->markbookRouteName($context, $mode));
    }

    public function assessmentContextLabel(string $context): string
    {
        return match ($context) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'Elementary Gradebook',
            self::ASSESSMENT_CONTEXT_SENIOR => 'High School Gradebook',
            default => 'Middle School Gradebook',
        };
    }

    public function assessmentContextDescription(string $context): string
    {
        return match ($context) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'Elementary grades from REC through STD 7, including report cards and analysis.',
            self::ASSESSMENT_CONTEXT_SENIOR => 'High school grades F4 through F5, including senior reports and analysis.',
            default => 'Middle school grades F1 through F3, including PSLE analysis and report cards.',
        };
    }

    public function markbookContextLabel(string $context): string
    {
        return match ($context) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'Elementary Markbook',
            self::ASSESSMENT_CONTEXT_SENIOR => 'High School Markbook',
            default => 'Middle School Markbook',
        };
    }

    public function markbookContextDescription(string $context): string
    {
        return match ($context) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'Elementary class subjects from REC through STD 7 for mark entry.',
            self::ASSESSMENT_CONTEXT_SENIOR => 'High school class and optional subjects for F4 through F5 mark entry.',
            default => 'Middle school class and optional subjects for F1 through F3 mark entry.',
        };
    }

    public function assessmentContextForLevel(?string $level): string
    {
        return match ($this->assessmentDriverForLevel($level)) {
            'primary' => self::ASSESSMENT_CONTEXT_PRIMARY,
            'senior' => self::ASSESSMENT_CONTEXT_SENIOR,
            default => self::ASSESSMENT_CONTEXT_JUNIOR,
        };
    }

    /**
     * @return array<int, string>
     */
    public function accessibleMarkbookContexts(?User $user = null, ?int $termId = null, ?string $mode = null): array
    {
        $user ??= auth()->user();

        if (!$user) {
            return [];
        }

        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode()) ?? SchoolSetup::TYPE_JUNIOR;
        $selectedTermId = $termId ?? session('selected_term_id') ?? optional(TermHelper::getCurrentTerm())->id;
        $cacheKey = implode(':', [
            $user->id,
            $selectedTermId ?? 'all',
            $resolvedMode,
        ]);

        if (array_key_exists($cacheKey, $this->accessibleMarkbookContextsCache)) {
            return $this->accessibleMarkbookContextsCache[$cacheKey];
        }

        $availableContexts = $this->availableAssessmentContexts($resolvedMode);

        if ($user->hasAnyRoles(self::MARKBOOK_ADMIN_ROLES)) {
            return $this->accessibleMarkbookContextsCache[$cacheKey] = $availableContexts;
        }

        $teacherIds = $this->markbookTeacherIds($user);

        if (empty($teacherIds)) {
            return $this->accessibleMarkbookContextsCache[$cacheKey] = [];
        }

        $supportedLevels = $this->supportedLevels($resolvedMode);

        $klassContexts = KlassSubject::query()
            ->with('klass.grade')
            ->when($selectedTermId !== null, fn ($query) => $query->where('term_id', $selectedTermId))
            ->where(function ($query) use ($teacherIds) {
                $query->whereIn('user_id', $teacherIds)
                    ->orWhereIn('assistant_user_id', $teacherIds);
            })
            ->whereHas('klass.grade', function ($gradeQuery) use ($supportedLevels) {
                $gradeQuery->whereIn('level', $supportedLevels);
            })
            ->whereHas('subject.subject', function ($subjectQuery) {
                $subjectQuery->where('components', 0);
            })
            ->get()
            ->map(fn (KlassSubject $klassSubject) => $this->levelForKlass($klassSubject->klass))
            ->filter()
            ->map(fn (string $level) => $this->assessmentContextForLevel($level))
            ->toBase();

        $optionalContexts = OptionalSubject::query()
            ->with('grade')
            ->when($selectedTermId !== null, fn ($query) => $query->where('term_id', $selectedTermId))
            ->where(function ($query) use ($teacherIds) {
                $query->whereIn('user_id', $teacherIds)
                    ->orWhereIn('assistant_user_id', $teacherIds);
            })
            ->whereHas('grade', function ($gradeQuery) use ($supportedLevels) {
                $gradeQuery->whereIn('level', $supportedLevels);
            })
            ->get()
            ->map(fn (OptionalSubject $optionalSubject) => $this->levelForGrade($optionalSubject->grade))
            ->filter()
            ->map(fn (string $level) => $this->assessmentContextForLevel($level))
            ->toBase();

        $discoveredContexts = $klassContexts
            ->merge($optionalContexts)
            ->unique()
            ->values()
            ->all();

        return $this->accessibleMarkbookContextsCache[$cacheKey] = array_values(array_filter(
            $availableContexts,
            fn (string $context) => in_array($context, $discoveredContexts, true)
        ));
    }

    public function hasMarkbookAccess(?User $user = null, ?int $termId = null, ?string $mode = null): bool
    {
        return !empty($this->accessibleMarkbookContexts($user, $termId, $mode));
    }

    public function canAccessMarkbookContext(User $user, ?string $context, ?int $termId = null, ?string $mode = null): bool
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode()) ?? SchoolSetup::TYPE_JUNIOR;
        $resolvedContext = $this->resolveAssessmentContext($context, $resolvedMode);

        if ($resolvedContext === null) {
            return false;
        }

        return in_array(
            $resolvedContext,
            $this->accessibleMarkbookContexts($user, $termId, $resolvedMode),
            true
        );
    }

    public function usesSplitAssessmentSidebar(?string $mode = null): bool
    {
        return $this->isCombinedMode($mode);
    }

    public function assessmentContextSidebarLabel(string $context): string
    {
        return match ($context) {
            self::ASSESSMENT_CONTEXT_PRIMARY => 'Elementary',
            self::ASSESSMENT_CONTEXT_SENIOR => 'High School',
            default => 'Middle School',
        };
    }

    public function selectedLevelFilter(?string $mode = null): string
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());
        $selected = (string) session('combined_school_level_filter', self::FILTER_ALL);

        if (!$this->isCombinedMode($resolvedMode)) {
            return self::FILTER_ALL;
        }

        if (!in_array($selected, $this->availableFiltersForMode($resolvedMode), true)) {
            return self::FILTER_ALL;
        }

        return $selected;
    }

    /**
     * @return array<int, string>
     */
    public function availableFiltersForMode(?string $mode = null): array
    {
        return match (SchoolSetup::normalizeType($mode ?? $this->mode())) {
            SchoolSetup::TYPE_PRE_F3 => [self::FILTER_ALL, self::FILTER_PRE_PRIMARY_PRIMARY, self::FILTER_JUNIOR],
            SchoolSetup::TYPE_JUNIOR_SENIOR => [self::FILTER_ALL, self::FILTER_JUNIOR, self::FILTER_SENIOR],
            SchoolSetup::TYPE_K12 => [self::FILTER_ALL, self::FILTER_PRE_PRIMARY_PRIMARY, self::FILTER_JUNIOR, self::FILTER_SENIOR],
            default => [self::FILTER_ALL],
        };
    }

    /**
     * @return array<int, string>
     */
    public function levelsForFilter(?string $filter = null, ?string $mode = null): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode ?? $this->mode());

        return match ($filter ?? $this->selectedLevelFilter($resolvedMode)) {
            self::FILTER_PRE_PRIMARY_PRIMARY => array_values(array_intersect(
                $this->supportedLevels($resolvedMode),
                [SchoolSetup::LEVEL_PRE_PRIMARY, SchoolSetup::LEVEL_PRIMARY]
            )),
            self::FILTER_JUNIOR => array_values(array_intersect(
                $this->supportedLevels($resolvedMode),
                [SchoolSetup::LEVEL_JUNIOR]
            )),
            self::FILTER_SENIOR => array_values(array_intersect(
                $this->supportedLevels($resolvedMode),
                [SchoolSetup::LEVEL_SENIOR]
            )),
            default => $this->supportedLevels($resolvedMode),
        };
    }

    /**
     * @return array<int, string>
     */
    public function dashboardGradeNames(?string $mode = null, ?string $filter = null): array
    {
        return AcademicStructureRegistry::gradeNamesForLevels($this->levelsForFilter($filter, $mode));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Grade>|array<int, \App\Models\Grade>  $grades
     * @return \Illuminate\Support\Collection<int, \App\Models\Grade>
     */
    public function filterGradesBySelectedLevels(Collection|array $grades, ?string $filter = null, ?string $mode = null): Collection
    {
        $collection = $grades instanceof Collection ? $grades : collect($grades);
        $levels = $this->levelsForFilter($filter, $mode);

        return $collection->filter(function ($grade) use ($levels) {
            return in_array($this->normalizeLevel($grade->level ?? null), $levels, true);
        })->values();
    }

    /**
     * @return array<int, int>
     */
    private function markbookTeacherIds(User $user): array
    {
        return array_values(array_unique(array_filter(array_merge(
            [$user->id],
            $user->subordinates()->pluck('id')->all()
        ))));
    }
}
