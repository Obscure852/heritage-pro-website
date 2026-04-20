<?php

namespace App\Services\Finals\ImportProfiles;

use App\Models\SchoolSetup;
use App\Services\Finals\FinalsContextDefinition;
use App\Services\JCEPdfParserService;

class JuniorJceImportProfile implements FinalsImportProfile
{
    public function definition(): FinalsContextDefinition
    {
        $catalog = [
            ['source_key' => 'setswana', 'source_code' => '11', 'source_label' => 'Setswana', 'default_subject_name' => 'Setswana'],
            ['source_key' => 'english', 'source_code' => '12', 'source_label' => 'English', 'default_subject_name' => 'English'],
            ['source_key' => 'mathematics', 'source_code' => '13', 'source_label' => 'Mathematics', 'default_subject_name' => 'Mathematics'],
            ['source_key' => 'science', 'source_code' => '14', 'source_label' => 'Science', 'default_subject_name' => 'Science'],
            ['source_key' => 'social_studies', 'source_code' => '15', 'source_label' => 'Social Studies', 'default_subject_name' => 'Social Studies'],
            ['source_key' => 'agriculture', 'source_code' => '16', 'source_label' => 'Agriculture', 'default_subject_name' => 'Agriculture'],
            ['source_key' => 'design_and_technology', 'source_code' => '17', 'source_label' => 'Design and Technology', 'default_subject_name' => 'Design & Technology'],
            ['source_key' => 'moral_education', 'source_code' => '18', 'source_label' => 'Moral Education', 'default_subject_name' => 'Moral Education'],
            ['source_key' => 'home_economics', 'source_code' => '21', 'source_label' => 'Home Economics', 'default_subject_name' => 'Home Economics'],
            ['source_key' => 'office_procedures', 'source_code' => '25', 'source_label' => 'Office Procedures', 'default_subject_name' => 'Office Procedures'],
            ['source_key' => 'accounting', 'source_code' => '26', 'source_label' => 'Accounting', 'default_subject_name' => 'Accounting'],
            ['source_key' => 'religious_education', 'source_code' => '31', 'source_label' => 'Religious Education', 'default_subject_name' => 'Religious Education'],
            ['source_key' => 'art', 'source_code' => '33', 'source_label' => 'Art', 'default_subject_name' => 'Art'],
            ['source_key' => 'music', 'source_code' => '34', 'source_label' => 'Music', 'default_subject_name' => 'Music'],
            ['source_key' => 'physical_education', 'source_code' => '35', 'source_label' => 'Physical Education', 'default_subject_name' => 'Physical Education'],
        ];

        $headers = array_merge(['exam_number'], array_column($catalog, 'source_key'));

        return new FinalsContextDefinition(
            context: 'junior',
            contextLabel: 'Junior',
            description: 'JCE final results, transcripts and analysis for Form 3 graduates.',
            level: SchoolSetup::LEVEL_JUNIOR,
            schoolType: SchoolSetup::TYPE_JUNIOR,
            examType: 'JCE',
            examLabel: 'JCE',
            graduationGradeNames: ['F3'],
            eligiblePriorYearGrade: 'F3',
            passGradeSet: ['Merit', 'A', 'B', 'C'],
            overallGradeScale: ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'],
            templateHeaders: $headers,
            sampleRows: [
                ['0001', 'C', 'C', 'B', 'C', 'C', 'B', 'C', 'B', 'C', '', '', 'C', 'B', 'B', 'B'],
                ['0002', 'B', 'B', 'A', 'B', 'B', 'C', 'B', 'A', '', 'C', 'B', '', 'A', '', 'A'],
            ],
            supportedPdfFormats: ['Official JCE Grade Listing PDFs (Junior)'],
            subjectMappingCatalog: $catalog,
            reportMenu: [
                'students' => [
                    ['route' => 'finals.year.overall-analysis', 'label' => 'JCE Top Performers', 'icon' => 'fas fa-chart-line text-primary'],
                    ['route' => 'finals.students.transcripts-list', 'label' => 'JCE Transcripts', 'icon' => 'fas fa-file-alt text-success'],
                ],
                'classes' => [
                    ['route' => 'finals.classes.overall-analysis', 'label' => 'JCE Top Classes', 'icon' => 'fas fa-medal text-warning'],
                    ['route' => 'finals.classes.overall-performance-analysis', 'label' => 'JCE Class Subject Analysis', 'icon' => 'fas fa-chart-line text-primary'],
                    ['route' => 'finals.classes.jce-psle-grade-comparison', 'label' => 'JCE Grade Value Addition', 'icon' => 'fas fa-exchange-alt text-success'],
                ],
                'core' => [
                    ['route' => 'finals.core.subjects-analysis', 'label' => 'JCE Class Subjects Analysis', 'icon' => 'fas fa-chart-line text-primary'],
                    ['route' => 'finals.core.department-subjects-analysis', 'label' => 'JCE Department Subject Analysis', 'icon' => 'fas fa-chart-bar text-success'],
                    ['route' => 'finals.core.teacher-subjects-analysis', 'label' => 'JCE Teacher Performance', 'icon' => 'fas fa-user-check text-warning'],
                ],
                'subjects' => [
                    ['route' => 'finals.class.subjects-summary-analyis', 'label' => 'JCE Class Subjects Summary', 'icon' => 'fas fa-chart-pie text-warning', 'params' => ['classId' => '__CLASS_ID__']],
                    ['route' => 'finals.subjects.overall-teachers-analysis', 'label' => 'JCE Overall Teachers Analysis', 'icon' => 'fas fa-users text-info', 'params' => ['classId' => '__CLASS_ID__', 'type' => 'Exam', 'sequence' => 1]],
                    ['route' => 'finals.subjects.subject-gender-grades-report', 'label' => 'JCE Subject Performance Summary', 'icon' => 'fas fa-chart-line text-primary'],
                    ['route' => 'finals.subjects.subject-psle-jce-comparison', 'label' => 'JCE vs PSLE Subject Analysis', 'icon' => 'fas fa-balance-scale text-success'],
                ],
                'optionals' => [
                    ['route' => 'finals.optionals.subjects-analysis', 'label' => 'JCE Optional Subjects Analysis', 'icon' => 'fas fa-chart-bar text-primary'],
                    ['route' => 'finals.optionals.department-analysis', 'label' => 'JCE Optionals by Department', 'icon' => 'fas fa-chart-line text-success'],
                    ['route' => 'finals.optionals.teachers-analysis', 'label' => 'JCE Optionals by Teacher', 'icon' => 'fas fa-trophy text-warning'],
                ],
                'houses' => [
                    ['route' => 'finals.houses.houses-class-analysis', 'label' => 'JCE House Distribution', 'icon' => 'fas fa-building-columns text-primary'],
                    ['route' => 'finals.houses.performance-analysis', 'label' => 'JCE House Performance', 'icon' => 'fas fa-chart-line text-success'],
                    ['route' => 'finals.houses.exam-houses-overall-analysis', 'label' => 'JCE Overall Houses Analysis', 'icon' => 'fas fa-chart-area text-info'],
                ],
            ],
            requiredColumns: ['exam_number'],
            optionalColumns: [],
            subjectColumnReferences: [
                'JCE subject columns: Setswana, English, Mathematics, Science, Social Studies, Agriculture, Design and Technology, Moral Education, Home Economics, Office Procedures, Accounting, Religious Education, Art, Music, Physical Education',
            ],
            performanceCategories: [
                'high_achievement' => ['label' => 'MAB %', 'grades' => ['Merit', 'A', 'B']],
                'pass_rate' => ['label' => 'MABC %', 'grades' => ['Merit', 'A', 'B', 'C']],
                'non_failure' => ['label' => 'MABCD %', 'grades' => ['Merit', 'A', 'B', 'C', 'D']],
            ],
            performanceDefaults: [
                'high_achievement' => 25.0,
                'pass_rate' => 65.0,
                'non_failure' => 85.0,
            ],
        );
    }

    public function parserService(): JCEPdfParserService
    {
        return new JCEPdfParserService();
    }

    public function subjectCodeMap(): array
    {
        $map = [];
        foreach ($this->definition()->subjectMappingCatalog as $item) {
            $map[$item['source_key']] = $item['source_code'];
        }

        return $map;
    }

    public function defaultSubjectNameMap(): array
    {
        $map = [];
        foreach ($this->definition()->subjectMappingCatalog as $item) {
            $map[$item['source_key']] = $item['default_subject_name'];
        }

        return $map;
    }
}
