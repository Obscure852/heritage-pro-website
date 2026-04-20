<?php

namespace App\Services\Finals;

class FinalsContextDefinition
{
    /**
     * @param  array<int, string>  $graduationGradeNames
     * @param  array<int, string>  $passGradeSet
     * @param  array<int, string>  $overallGradeScale
     * @param  array<int, string>  $templateHeaders
     * @param  array<int, array<int, string>>  $sampleRows
     * @param  array<int, string>  $supportedPdfFormats
     * @param  array<int, array<string, mixed>>  $subjectMappingCatalog
     * @param  array<string, array<int, array<string, mixed>>>  $reportMenu
     * @param  array<int, string>  $requiredColumns
     * @param  array<int, string>  $optionalColumns
     * @param  array<int, string>  $subjectColumnReferences
     * @param  array<string, array<string, mixed>>  $performanceCategories
     * @param  array<string, float>  $performanceDefaults
     */
    public function __construct(
        public string $context,
        public string $contextLabel,
        public string $description,
        public string $level,
        public string $schoolType,
        public string $examType,
        public string $examLabel,
        public array $graduationGradeNames,
        public string $eligiblePriorYearGrade,
        public array $passGradeSet,
        public array $overallGradeScale,
        public array $templateHeaders,
        public array $sampleRows,
        public array $supportedPdfFormats,
        public array $subjectMappingCatalog,
        public array $reportMenu,
        public array $requiredColumns = ['exam_number'],
        public array $optionalColumns = [],
        public array $subjectColumnReferences = [],
        public array $performanceCategories = [],
        public array $performanceDefaults = [],
    ) {
    }

    public function matchesGradeName(?string $gradeName): bool
    {
        if ($gradeName === null) {
            return false;
        }

        return in_array(strtoupper(trim($gradeName)), array_map('strtoupper', $this->graduationGradeNames), true);
    }

    public function isOverallPass(?string $grade): bool
    {
        if ($grade === null || trim($grade) === '') {
            return false;
        }

        return in_array(strtoupper(trim($grade)), array_map('strtoupper', $this->passGradeSet), true);
    }

    /**
     * @return array<int, string>
     */
    public function nonFailureGrades(): array
    {
        $nonFailure = [];
        foreach ($this->performanceCategories['non_failure']['grades'] ?? [] as $grade) {
            $nonFailure[] = $grade;
        }

        return $nonFailure;
    }
}
