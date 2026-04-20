<?php

namespace App\Services\Finals\ImportProfiles;

use App\Models\SchoolSetup;
use App\Services\BECPdfParserService;
use App\Services\Finals\FinalsContextDefinition;

class SeniorBgcseImportProfile implements FinalsImportProfile
{
    public function definition(): FinalsContextDefinition
    {
        $catalog = [
            ['source_key' => 'english', 'source_code' => '0561 / 1234', 'source_label' => 'English Language / BSSE English', 'default_subject_name' => 'English'],
            ['source_key' => 'setswana', 'source_code' => '0562 / 1235', 'source_label' => 'Setswana / BSSE Setswana', 'default_subject_name' => 'Setswana'],
            ['source_key' => 'mathematics', 'source_code' => '0563 / 1236', 'source_label' => 'Mathematics / BSSE General Mathematics', 'default_subject_name' => 'Mathematics'],
            ['source_key' => 'science_single_award', 'source_code' => '0568', 'source_label' => 'Science Single Award', 'default_subject_name' => 'Science'],
            ['source_key' => 'science_double_award', 'source_code' => '0569', 'source_label' => 'Science Double Award', 'default_subject_name' => 'Double Science'],
            ['source_key' => 'chemistry', 'source_code' => '0570', 'source_label' => 'Chemistry', 'default_subject_name' => 'Chemistry'],
            ['source_key' => 'physics', 'source_code' => '0571', 'source_label' => 'Physics', 'default_subject_name' => 'Physics'],
            ['source_key' => 'biology', 'source_code' => '0572', 'source_label' => 'Biology', 'default_subject_name' => 'Biology'],
            ['source_key' => 'human_and_social_biology', 'source_code' => '0573', 'source_label' => 'Human and Social Biology', 'default_subject_name' => 'Biology'],
            ['source_key' => 'history', 'source_code' => '0583', 'source_label' => 'History', 'default_subject_name' => 'History'],
            ['source_key' => 'geography', 'source_code' => '0584', 'source_label' => 'Geography', 'default_subject_name' => 'Geography'],
            ['source_key' => 'social_studies', 'source_code' => '0585', 'source_label' => 'Social Studies', 'default_subject_name' => 'Social Studies'],
            ['source_key' => 'development_studies', 'source_code' => '0586', 'source_label' => 'Development Studies', 'default_subject_name' => 'Development Studies'],
            ['source_key' => 'literature_in_english', 'source_code' => '0587', 'source_label' => 'Literature in English', 'default_subject_name' => 'English Literature'],
            ['source_key' => 'religious_education', 'source_code' => '0588', 'source_label' => 'Religious Education', 'default_subject_name' => 'Religious Education'],
            ['source_key' => 'design_and_technology', 'source_code' => '0595', 'source_label' => 'Design and Technology', 'default_subject_name' => 'Design & Technology'],
            ['source_key' => 'art', 'source_code' => '0596 / 1261', 'source_label' => 'Art and Design / BSSE Visual Arts', 'default_subject_name' => 'Art'],
            ['source_key' => 'computer_studies', 'source_code' => '0597', 'source_label' => 'Computer Studies', 'default_subject_name' => 'Computer Studies'],
            ['source_key' => 'commerce', 'source_code' => '0598', 'source_label' => 'Commerce', 'default_subject_name' => 'Commerce'],
            ['source_key' => 'agriculture', 'source_code' => '0599', 'source_label' => 'Agriculture', 'default_subject_name' => 'Agriculture'],
            ['source_key' => 'food_and_nutrition', 'source_code' => '0611 / 1262', 'source_label' => 'Food and Nutrition / BSSE Food Studies', 'default_subject_name' => 'Food & Nutrition'],
            ['source_key' => 'fashion_and_fabrics', 'source_code' => '0612', 'source_label' => 'Fashion and Fabrics', 'default_subject_name' => 'Fashion & Fabrics'],
            ['source_key' => 'home_management', 'source_code' => '0613', 'source_label' => 'Home Management', 'default_subject_name' => 'Home Management'],
            ['source_key' => 'accounting', 'source_code' => '0614', 'source_label' => 'Accounting', 'default_subject_name' => 'Accounting'],
            ['source_key' => 'business_studies', 'source_code' => '0615', 'source_label' => 'Business Studies', 'default_subject_name' => 'Business Studies'],
            ['source_key' => 'physical_education', 'source_code' => '0616 / 1259', 'source_label' => 'Physical Education / BSSE Physical Education', 'default_subject_name' => 'Physical Education'],
            ['source_key' => 'music', 'source_code' => '0617 / 1258', 'source_label' => 'Music / BSSE Music', 'default_subject_name' => 'Music'],
            ['source_key' => 'french', 'source_code' => '0618', 'source_label' => 'French', 'default_subject_name' => 'French'],
            ['source_key' => 'add_mathematics', 'source_code' => '1237', 'source_label' => 'BSSE Scientific Mathematics', 'default_subject_name' => 'Add Mathematics'],
            ['source_key' => 'hospitality_and_tourism_studies', 'source_code' => '1254', 'source_label' => 'Hospitality and Tourism Studies', 'default_subject_name' => 'Hospitality and Tourism Studies'],
            ['source_key' => 'animal_production', 'source_code' => '1255', 'source_label' => 'Animal Production', 'default_subject_name' => 'Animal Production'],
            ['source_key' => 'field_crop_production', 'source_code' => '1256', 'source_label' => 'Field Crop Production', 'default_subject_name' => 'Field Crop Production'],
            ['source_key' => 'horticulture', 'source_code' => '1257', 'source_label' => 'Horticulture', 'default_subject_name' => 'Horticulture'],
        ];

        $headers = (new BECPdfParserService())->getBGCSEExcelHeaders();

        return new FinalsContextDefinition(
            context: 'senior',
            contextLabel: 'Senior',
            description: 'BGCSE final results, transcripts and analysis for Form 5 graduates.',
            level: SchoolSetup::LEVEL_SENIOR,
            schoolType: SchoolSetup::TYPE_SENIOR,
            examType: 'BGCSE',
            examLabel: 'BGCSE',
            graduationGradeNames: ['F5'],
            eligiblePriorYearGrade: 'F5',
            passGradeSet: ['A', 'B', 'C'],
            overallGradeScale: ['A', 'B', 'C', 'D', 'E', 'U'],
            templateHeaders: $headers,
            sampleRows: [
                ['0001', 'C', 'D', 'C', 'B', '', '', '', '', '', '', 'C', '', '', '', 'C', '', '', '', 'C', 'C', '', '', '', 'C', '', 'B', 'B', '', '', '', '', '', '', ''],
                ['0002', 'B', 'C', 'B', '', 'B', '', '', 'B', '', 'C', '', '', '', '', '', 'A', '', '', '', '', 'C', '', '', 'C', '', '', 'B', '', 'C', '', '', '', '', ''],
            ],
            supportedPdfFormats: ['Official BEC BGCSE Results Broadsheet (Senior)'],
            subjectMappingCatalog: $catalog,
            reportMenu: [
                'students' => [
                    ['route' => 'finals.senior.students.top-performers', 'label' => 'BGCSE Top Performers', 'icon' => 'fas fa-chart-line text-primary', 'params' => ['year' => '__YEAR__']],
                    ['route' => 'finals.senior.students.transcripts-list', 'label' => 'BGCSE Transcripts', 'icon' => 'fas fa-file-alt text-success', 'params' => ['year' => '__YEAR__']],
                ],
                'classes' => [
                    ['route' => 'finals.senior.students.top-performers', 'label' => 'BGCSE Top Performers', 'icon' => 'fas fa-chart-line text-primary', 'params' => ['year' => '__YEAR__']],
                    ['route' => 'finals.senior.students.transcripts-list', 'label' => 'BGCSE Transcripts', 'icon' => 'fas fa-file-alt text-success', 'params' => ['year' => '__YEAR__']],
                ],
                'core' => [
                    ['route' => 'finals.senior.students.top-performers', 'label' => 'BGCSE Top Performers', 'icon' => 'fas fa-chart-line text-primary', 'params' => ['year' => '__YEAR__']],
                ],
                'subjects' => [
                    ['route' => 'finals.senior.students.top-performers', 'label' => 'BGCSE Top Performers', 'icon' => 'fas fa-chart-line text-primary', 'params' => ['year' => '__YEAR__']],
                    ['route' => 'finals.senior.students.transcripts-list', 'label' => 'BGCSE Transcripts', 'icon' => 'fas fa-file-alt text-success', 'params' => ['year' => '__YEAR__']],
                ],
                'optionals' => [
                    ['route' => 'finals.senior.students.transcripts-list', 'label' => 'BGCSE Transcripts', 'icon' => 'fas fa-file-alt text-success', 'params' => ['year' => '__YEAR__']],
                ],
                'houses' => [
                    ['route' => 'finals.senior.students.top-performers', 'label' => 'BGCSE Top Performers', 'icon' => 'fas fa-chart-line text-primary', 'params' => ['year' => '__YEAR__']],
                ],
            ],
            requiredColumns: ['exam_number'],
            optionalColumns: [],
            subjectColumnReferences: [
                'BGCSE subject columns: English, Setswana, Mathematics, Science Single Award, Science Double Award, Chemistry, Physics, Biology, Human and Social Biology, History, Geography, Social Studies, Development Studies, Literature in English, Religious Education, Design and Technology, Art, Computer Studies, Commerce, Agriculture, Food and Nutrition, Fashion and Fabrics, Home Management, Accounting, Business Studies, Physical Education, Music, French, Add Mathematics, Hospitality and Tourism Studies, Animal Production, Field Crop Production, Horticulture',
            ],
            performanceCategories: [
                'high_achievement' => ['label' => 'AB %', 'grades' => ['A', 'B']],
                'pass_rate' => ['label' => 'ABC %', 'grades' => ['A', 'B', 'C']],
                'non_failure' => ['label' => 'ABCD %', 'grades' => ['A', 'B', 'C', 'D']],
            ],
            performanceDefaults: [
                'high_achievement' => 30.0,
                'pass_rate' => 70.0,
                'non_failure' => 90.0,
            ],
        );
    }

    public function parserService(): BECPdfParserService
    {
        return new BECPdfParserService();
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
