<?php

namespace App\Support;

use App\Models\SchoolSetup;

class AcademicStructureRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function gradeDefinitionsForMode(string $mode): array
    {
        return match (SchoolSetup::normalizeType($mode)) {
            SchoolSetup::TYPE_PRIMARY => self::primaryGradeDefinitions(),
            SchoolSetup::TYPE_JUNIOR => self::juniorGradeDefinitions(),
            SchoolSetup::TYPE_SENIOR => self::seniorGradeDefinitions(),
            SchoolSetup::TYPE_PRE_F3 => array_merge(
                self::primaryGradeDefinitions(true),
                self::juniorGradeDefinitions(9)
            ),
            SchoolSetup::TYPE_JUNIOR_SENIOR => array_merge(
                self::juniorGradeDefinitions(),
                self::seniorGradeDefinitions(4)
            ),
            SchoolSetup::TYPE_K12 => array_merge(
                self::primaryGradeDefinitions(true),
                self::juniorGradeDefinitions(9),
                self::seniorGradeDefinitions(12)
            ),
            default => self::juniorGradeDefinitions(),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function gradeNamesForLevels(array $levels): array
    {
        $definitions = collect(self::gradeDefinitionsForMode(SchoolSetup::TYPE_K12));

        return $definitions
            ->filter(fn (array $definition) => in_array($definition['level'], $levels, true))
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function departmentNamesForMode(string $mode): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode);

        return match ($resolvedMode) {
            SchoolSetup::TYPE_PRIMARY => self::PRIMARY_DEPARTMENTS,
            SchoolSetup::TYPE_JUNIOR => self::JUNIOR_DEPARTMENTS,
            SchoolSetup::TYPE_SENIOR => self::SENIOR_DEPARTMENTS,
            SchoolSetup::TYPE_PRE_F3 => array_values(array_unique(array_merge(self::PRIMARY_DEPARTMENTS, self::JUNIOR_DEPARTMENTS))),
            SchoolSetup::TYPE_JUNIOR_SENIOR => array_values(array_unique(array_merge(self::JUNIOR_DEPARTMENTS, self::SENIOR_DEPARTMENTS))),
            SchoolSetup::TYPE_K12 => array_values(array_unique(array_merge(self::PRIMARY_DEPARTMENTS, self::JUNIOR_DEPARTMENTS, self::SENIOR_DEPARTMENTS))),
            default => self::JUNIOR_DEPARTMENTS,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function subjectDefinitionsForMode(string $mode): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode);

        return match ($resolvedMode) {
            SchoolSetup::TYPE_PRIMARY => self::PRIMARY_SUBJECTS,
            SchoolSetup::TYPE_JUNIOR => self::JUNIOR_SUBJECTS,
            SchoolSetup::TYPE_SENIOR => self::SENIOR_SUBJECTS,
            SchoolSetup::TYPE_PRE_F3 => array_merge(self::PRIMARY_SUBJECTS, self::JUNIOR_SUBJECTS),
            SchoolSetup::TYPE_JUNIOR_SENIOR => array_merge(self::JUNIOR_SUBJECTS, self::SENIOR_SUBJECTS),
            SchoolSetup::TYPE_K12 => array_merge(self::PRIMARY_SUBJECTS, self::JUNIOR_SUBJECTS, self::SENIOR_SUBJECTS),
            default => self::JUNIOR_SUBJECTS,
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function gradeSubjectDefinitionsForMode(string $mode): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode);

        $definitions = [];

        foreach (self::subjectDefinitionsForMode($resolvedMode) as $index => $subject) {
            $canonicalKey = $subject['canonical_key'];
            $level = $subject['level'];

            foreach (self::gradeDefinitionsForMode($resolvedMode) as $grade) {
                if (!self::subjectAppliesToGradeLevel($level, $grade['level'])) {
                    continue;
                }

                $definitions[] = [
                    'grade_name' => $grade['name'],
                    'grade_level' => $grade['level'],
                    'subject_level' => $level,
                    'canonical_key' => $canonicalKey,
                    'sequence' => $index + 1,
                    'type' => self::subjectTypeFor($level, $canonicalKey),
                    'mandatory' => self::subjectMandatoryFor($level, $canonicalKey),
                    'components' => (bool) ($subject['components'] ?? false),
                ];
            }
        }

        return $definitions;
    }

    /**
     * @return array<int, string>
     */
    public static function supportedTestLevelsForMode(string $mode): array
    {
        return match (SchoolSetup::normalizeType($mode)) {
            SchoolSetup::TYPE_PRIMARY => [SchoolSetup::LEVEL_PRIMARY],
            SchoolSetup::TYPE_JUNIOR => [SchoolSetup::LEVEL_JUNIOR],
            SchoolSetup::TYPE_SENIOR => [SchoolSetup::LEVEL_SENIOR],
            SchoolSetup::TYPE_PRE_F3 => [SchoolSetup::LEVEL_PRIMARY, SchoolSetup::LEVEL_JUNIOR],
            SchoolSetup::TYPE_JUNIOR_SENIOR => [SchoolSetup::LEVEL_JUNIOR, SchoolSetup::LEVEL_SENIOR],
            SchoolSetup::TYPE_K12 => [SchoolSetup::LEVEL_PRIMARY, SchoolSetup::LEVEL_JUNIOR, SchoolSetup::LEVEL_SENIOR],
            default => [SchoolSetup::LEVEL_JUNIOR],
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function preschoolComponents(): array
    {
        return [
            'gross_motor_development' => ['Running', 'Jumping', 'Climbing', 'Throwing'],
            'fine_motor_development' => ['Drawing', 'Cutting with scissors', 'Pasting', 'Threading beads'],
            'numeracy' => ['Counting', 'Number recognition', 'Basic addition and subtraction', 'Patterns'],
            'cognitive_development' => ['Problem-solving', 'Memory games', 'Sorting and classifying', 'Following instructions'],
            'language_development' => ['Listening skills', 'Vocabulary building', 'Storytelling', 'Phonics'],
            'social_and_emotional_development' => ['Sharing', 'Taking turns', 'Understanding emotions', 'Cooperation'],
            'large_muscle_development' => ['Balancing', 'Hopping', 'Swinging', 'Catching'],
            'small_muscle_development' => ['Pinching', 'Grasping', 'Building with blocks', 'Lacing cards'],
        ];
    }

    public static function canonicalKeyFor(?string $level, ?string $abbrev, ?string $name): ?string
    {
        if (blank($level) && blank($abbrev) && blank($name)) {
            return null;
        }

        $normalizedLevel = trim((string) $level);
        $normalizedAbbrev = strtoupper(trim((string) $abbrev));
        $normalizedName = strtolower(trim((string) $name));

        foreach (array_merge(self::PRIMARY_SUBJECTS, self::JUNIOR_SUBJECTS, self::SENIOR_SUBJECTS) as $subject) {
            $subjectLevel = $subject['level'];
            $subjectAbbrev = strtoupper($subject['abbrev']);
            $subjectName = strtolower($subject['name']);

            if ($normalizedLevel !== '' && $subjectLevel !== $normalizedLevel) {
                continue;
            }

            if ($normalizedAbbrev !== '' && $subjectAbbrev === $normalizedAbbrev) {
                return $subject['canonical_key'];
            }

            if ($normalizedName !== '' && $subjectName === $normalizedName) {
                return $subject['canonical_key'];
            }
        }

        if ($normalizedName !== '') {
            return str($normalizedName)->replace('&', 'and')->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
        }

        if ($normalizedAbbrev !== '') {
            return strtolower($normalizedAbbrev);
        }

        return null;
    }

    private static function subjectAppliesToGradeLevel(string $subjectLevel, string $gradeLevel): bool
    {
        return match ($subjectLevel) {
            'Preschool' => $gradeLevel === SchoolSetup::LEVEL_PRE_PRIMARY,
            SchoolSetup::LEVEL_PRIMARY => $gradeLevel === SchoolSetup::LEVEL_PRIMARY,
            SchoolSetup::LEVEL_JUNIOR => $gradeLevel === SchoolSetup::LEVEL_JUNIOR,
            SchoolSetup::LEVEL_SENIOR => $gradeLevel === SchoolSetup::LEVEL_SENIOR,
            default => false,
        };
    }

    private static function subjectTypeFor(string $level, string $canonicalKey): int
    {
        if ($level === SchoolSetup::LEVEL_SENIOR) {
            return $canonicalKey === 'english' ? 1 : 0;
        }

        if ($level !== SchoolSetup::LEVEL_JUNIOR) {
            return 1;
        }

        return in_array($canonicalKey, [
            'setswana',
            'art',
            'design_and_technology',
            'music',
            'religious_education',
            'physical_education',
            'french',
            'home_economics',
            'office_procedures',
            'accounting',
        ], true) ? 0 : 1;
    }

    private static function subjectMandatoryFor(string $level, string $canonicalKey): bool
    {
        if ($level === SchoolSetup::LEVEL_JUNIOR) {
            return !in_array($canonicalKey, [
                'agriculture',
                'social_studies',
                'moral_education',
                'art',
                'design_and_technology',
                'music',
                'religious_education',
                'physical_education',
                'french',
                'home_economics',
                'office_procedures',
                'accounting',
            ], true);
        }

        if ($level === SchoolSetup::LEVEL_SENIOR) {
            return $canonicalKey === 'english';
        }

        return true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function primaryGradeDefinitions(bool $continueToJunior = false): array
    {
        return [
            ['sequence' => 1, 'name' => 'REC', 'promotion' => 'STD 1', 'description' => 'Reception', 'level' => SchoolSetup::LEVEL_PRE_PRIMARY],
            ['sequence' => 2, 'name' => 'STD 1', 'promotion' => 'STD 2', 'description' => 'Standard 1', 'level' => SchoolSetup::LEVEL_PRIMARY],
            ['sequence' => 3, 'name' => 'STD 2', 'promotion' => 'STD 3', 'description' => 'Standard 2', 'level' => SchoolSetup::LEVEL_PRIMARY],
            ['sequence' => 4, 'name' => 'STD 3', 'promotion' => 'STD 4', 'description' => 'Standard 3', 'level' => SchoolSetup::LEVEL_PRIMARY],
            ['sequence' => 5, 'name' => 'STD 4', 'promotion' => 'STD 5', 'description' => 'Standard 4', 'level' => SchoolSetup::LEVEL_PRIMARY],
            ['sequence' => 6, 'name' => 'STD 5', 'promotion' => 'STD 6', 'description' => 'Standard 5', 'level' => SchoolSetup::LEVEL_PRIMARY],
            ['sequence' => 7, 'name' => 'STD 6', 'promotion' => 'STD 7', 'description' => 'Standard 6', 'level' => SchoolSetup::LEVEL_PRIMARY],
            ['sequence' => 8, 'name' => 'STD 7', 'promotion' => $continueToJunior ? 'F1' : 'Alumni', 'description' => 'Standard 7', 'level' => SchoolSetup::LEVEL_PRIMARY],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function juniorGradeDefinitions(int $sequenceOffset = 1): array
    {
        return [
            ['sequence' => $sequenceOffset, 'name' => 'F1', 'promotion' => 'F2', 'description' => 'Form 1', 'level' => SchoolSetup::LEVEL_JUNIOR],
            ['sequence' => $sequenceOffset + 1, 'name' => 'F2', 'promotion' => 'F3', 'description' => 'Form 2', 'level' => SchoolSetup::LEVEL_JUNIOR],
            ['sequence' => $sequenceOffset + 2, 'name' => 'F3', 'promotion' => 'Alumni', 'description' => 'Form 3', 'level' => SchoolSetup::LEVEL_JUNIOR],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function seniorGradeDefinitions(int $sequenceOffset = 1): array
    {
        return [
            ['sequence' => $sequenceOffset, 'name' => 'F4', 'promotion' => 'F5', 'description' => 'Form 4', 'level' => SchoolSetup::LEVEL_SENIOR],
            ['sequence' => $sequenceOffset + 1, 'name' => 'F5', 'promotion' => 'Alumni', 'description' => 'Form 5', 'level' => SchoolSetup::LEVEL_SENIOR],
        ];
    }

    private const PRIMARY_DEPARTMENTS = [
        'Administration',
        'Lower Primary',
        'Middle Primary',
        'Upper Primary',
    ];

    private const JUNIOR_DEPARTMENTS = [
        'Administration',
        'Mathematics & Science',
        'Practicals',
        'Generals',
        'Humanities',
        'Languages',
        'CAPA',
    ];

    private const SENIOR_DEPARTMENTS = [
        'Administration',
        'Mathematics',
        'Extended Mathematics',
        'Double Science',
        'Accounting',
        'Biology',
        'Physics',
        'Chemistry',
        'Humanities',
        'English',
        'Physical Education',
        'Religious Education',
        'Business Studies',
        'Setswana',
        'Design & Technology',
        'Geography',
        'Art',
        'Statistics',
        'Agriculture',
        'French',
        'History',
    ];

    private const PRIMARY_SUBJECTS = [
        ['canonical_key' => 'gross_motor_development', 'abbrev' => 'GMD', 'name' => 'Gross Motor Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'fine_motor_development', 'abbrev' => 'FMD', 'name' => 'Fine Motor Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'numeracy', 'abbrev' => 'NUM', 'name' => 'Numeracy', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'cognitive_development', 'abbrev' => 'COD', 'name' => 'Cognitive Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'language_development', 'abbrev' => 'LND', 'name' => 'Language Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'social_and_emotional_development', 'abbrev' => 'SED', 'name' => 'Social and Emotional Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'large_muscle_development', 'abbrev' => 'LMD', 'name' => 'Large Muscle Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'small_muscle_development', 'abbrev' => 'SMD', 'name' => 'Small Muscle Development', 'level' => 'Preschool', 'components' => true, 'description' => '', 'department' => 'Administration'],
        ['canonical_key' => 'agriculture', 'abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'Agriculture'],
        ['canonical_key' => 'mathematics', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['canonical_key' => 'english', 'abbrev' => 'ENG', 'name' => 'English', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'English'],
        ['canonical_key' => 'science', 'abbrev' => 'SCI', 'name' => 'Science', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'Science'],
        ['canonical_key' => 'social_studies', 'abbrev' => 'SOC', 'name' => 'Social Studies', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'Social Studies'],
        ['canonical_key' => 'setswana', 'abbrev' => 'SET', 'name' => 'Setswana', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'Foreign Language'],
        ['canonical_key' => 'capa', 'abbrev' => 'CAP', 'name' => 'CAPA', 'level' => SchoolSetup::LEVEL_PRIMARY, 'components' => false, 'description' => '', 'department' => 'Physical Education'],
    ];

    private const JUNIOR_SUBJECTS = [
        ['canonical_key' => 'mathematics', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics & Science'],
        ['canonical_key' => 'english', 'abbrev' => 'ENG', 'name' => 'English', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Languages'],
        ['canonical_key' => 'science', 'abbrev' => 'SCI', 'name' => 'Science', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics & Science'],
        ['canonical_key' => 'setswana', 'abbrev' => 'SETS', 'name' => 'Setswana', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Languages'],
        ['canonical_key' => 'agriculture', 'abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Agriculture'],
        ['canonical_key' => 'social_studies', 'abbrev' => 'SOC', 'name' => 'Social Studies', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Social Studies'],
        ['canonical_key' => 'moral_education', 'abbrev' => 'ME', 'name' => 'Moral Education', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Humanities'],
        ['canonical_key' => 'art', 'abbrev' => 'ART', 'name' => 'Art', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Art'],
        ['canonical_key' => 'design_and_technology', 'abbrev' => 'DT', 'name' => 'Design & Technology', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Design & Technology'],
        ['canonical_key' => 'music', 'abbrev' => 'MUS', 'name' => 'Music', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Music'],
        ['canonical_key' => 'religious_education', 'abbrev' => 'RE', 'name' => 'Religious Education', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Humanities'],
        ['canonical_key' => 'physical_education', 'abbrev' => 'PE', 'name' => 'Physical Education', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Humanities'],
        ['canonical_key' => 'french', 'abbrev' => 'FR', 'name' => 'French', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Generals'],
        ['canonical_key' => 'home_economics', 'abbrev' => 'HE', 'name' => 'Home Economics', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Home Economics'],
        ['canonical_key' => 'office_procedures', 'abbrev' => 'OP', 'name' => 'Office Procedures', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Office Procedures'],
        ['canonical_key' => 'accounting', 'abbrev' => 'ACC', 'name' => 'Accounting', 'level' => SchoolSetup::LEVEL_JUNIOR, 'components' => false, 'description' => '', 'department' => 'Accounting'],
    ];

    private const SENIOR_SUBJECTS = [
        // Mathematics
        ['canonical_key' => 'mathematics', 'abbrev' => 'MATH', 'name' => 'Mathematics', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['canonical_key' => 'mathematics_i', 'abbrev' => 'MATH1', 'name' => 'Mathematics I', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['canonical_key' => 'mathematics_ii', 'abbrev' => 'MATH2', 'name' => 'Mathematics II', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['canonical_key' => 'extended_mathematics', 'abbrev' => 'EXM', 'name' => 'Extended Mathematics', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['canonical_key' => 'additional_mathematics', 'abbrev' => 'AMA', 'name' => 'Add Mathematics', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Mathematics'],
        ['canonical_key' => 'statistics', 'abbrev' => 'STA', 'name' => 'Statistics', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Statistics'],
        // Sciences
        ['canonical_key' => 'chemistry', 'abbrev' => 'CHI', 'name' => 'Chemistry', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Science'],
        ['canonical_key' => 'physics', 'abbrev' => 'PHY', 'name' => 'Physics', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Science'],
        ['canonical_key' => 'biology', 'abbrev' => 'BIO', 'name' => 'Biology', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Science'],
        ['canonical_key' => 'double_science', 'abbrev' => 'DS', 'name' => 'Double Science', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Science'],
        ['canonical_key' => 'computer_studies', 'abbrev' => 'CS', 'name' => 'Computer Studies', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Computer Studies'],
        ['canonical_key' => 'agriculture', 'abbrev' => 'AGR', 'name' => 'Agriculture', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Agriculture'],
        // Languages
        ['canonical_key' => 'english', 'abbrev' => 'ENG', 'name' => 'English', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'English'],
        ['canonical_key' => 'english_literature', 'abbrev' => 'EL', 'name' => 'English Literature', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'English'],
        ['canonical_key' => 'setswana', 'abbrev' => 'SETS', 'name' => 'Setswana', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Foreign Language'],
        // Humanities & Social Sciences
        ['canonical_key' => 'history', 'abbrev' => 'HIS', 'name' => 'History', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'History'],
        ['canonical_key' => 'geography', 'abbrev' => 'GEO', 'name' => 'Geography', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Geography'],
        ['canonical_key' => 'social_studies', 'abbrev' => 'SOS', 'name' => 'Social Studies', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Social Studies'],
        ['canonical_key' => 'development_studies', 'abbrev' => 'DVS', 'name' => 'Development Studies', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Development Studies'],
        ['canonical_key' => 'moral_education', 'abbrev' => 'ME', 'name' => 'Moral Education', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Moral Education'],
        ['canonical_key' => 'religious_education', 'abbrev' => 'RE', 'name' => 'Religious Education', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Religious Education'],
        // Business
        ['canonical_key' => 'business_studies', 'abbrev' => 'BS', 'name' => 'Business Studies', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Business'],
        ['canonical_key' => 'business_management', 'abbrev' => 'BM', 'name' => 'Business Management', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Business'],
        ['canonical_key' => 'accounting', 'abbrev' => 'ACC', 'name' => 'Accounting', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Accounting'],
        ['canonical_key' => 'commerce', 'abbrev' => 'COMM', 'name' => 'Commerce', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Commerce'],
        ['canonical_key' => 'entrepreneurship', 'abbrev' => 'ENT', 'name' => 'Entrepreneurship', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Entrepreneurship'],
        // Practical & Creative
        ['canonical_key' => 'fashion_and_fabrics', 'abbrev' => 'FF', 'name' => 'Fashion & Fabrics', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Fashion & Fabrics'],
        ['canonical_key' => 'food_and_nutrition', 'abbrev' => 'FN', 'name' => 'Food & Nutrition', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Food & Nutrition'],
        ['canonical_key' => 'art', 'abbrev' => 'ART', 'name' => 'Art', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Art'],
        ['canonical_key' => 'design_and_technology', 'abbrev' => 'DT', 'name' => 'Design & Technology', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Design & Technology'],
        ['canonical_key' => 'music', 'abbrev' => 'MUS', 'name' => 'Music', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Music'],
        ['canonical_key' => 'physical_education', 'abbrev' => 'PE', 'name' => 'Physical Education', 'level' => SchoolSetup::LEVEL_SENIOR, 'components' => false, 'description' => '', 'department' => 'Physical Education'],
    ];
}
