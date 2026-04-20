<?php

namespace App\Exports;

use App\Models\SchoolSetup;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportTemplateExport implements FromArray, WithHeadings, WithStyles {
    private array $headings;
    private array $sampleRow;

    public function __construct(array $headings, array $sampleRow) {
        $this->headings = $headings;
        $this->sampleRow = $sampleRow;
    }

    public function headings(): array {
        return $this->headings;
    }

    public function array(): array {
        return [$this->sampleRow];
    }

    public function styles(Worksheet $sheet): array {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public static function staff(): self {
        return new self(
            ['firstname', 'middlename', 'lastname', 'email', 'date_of_birth', 'gender', 'position', 'area_of_work', 'phone', 'id_number', 'nationality', 'city', 'address', 'active', 'status', 'year'],
            ['Shawn', 'B.', 'Bolz', 'bolz@gmail.com', '03/09/1965', 'M', 'Teacher', 'Teaching', '72975334', '357718812', 'Motswana', 'Francistown', 'Block 3', 'True', 'Current', '2024']
        );
    }

    public static function sponsors(): self {
        return new self(
            ['connect_id', 'title', 'first_name', 'last_name', 'middle_name', 'email', 'gender', 'date_of_birth', 'nationality', 'relation', 'status', 'id_number', 'phone', 'profession', 'work_place', 'year'],
            ['345632', 'Mrs', 'Jane', 'Smith', '', 'jane.smith@example.com', 'F', '07/04/1976', 'Motswana', 'Mother', 'Current', '357718812', '71869865', 'Nurse', 'Sedilega Private Hospital', '2023']
        );
    }

    public static function students(string $schoolType): self {
        $baseHeadings = ['connect_id', 'first_name', 'last_name', 'middle_name', 'gender', 'date_of_birth', 'nationality', 'id_number', 'status', 'type', 'grade', 'boarding', 'class'];
        $baseSample = ['345632', 'Shawn', 'Bolz', 'B.', 'M', '03/09/2005', 'Motswana', '357718812', 'Current', 'Regular', '', '', '1A'];
        $resolvedType = SchoolSetup::normalizeType($schoolType) ?? SchoolSetup::TYPE_JUNIOR;
        $psleHeadings = [
            'overall_grade',
            'agriculture_grade',
            'mathematics_grade',
            'english_grade',
            'science_grade',
            'social_studies_grade',
            'setswana_grade',
            'capa_grade',
            'religious_and_moral_education_grade',
        ];
        $psleSample = ['B', 'A', 'C', 'B', 'B', 'A', 'D', 'C', 'B'];
        $jceHeadings = ['ov', 'math', 'eng', 'sci', 'set', 'dt', 'he', 'agr', 'me', 're', 'mus', 'pe', 'art', 'op', 'acc', 'fr', 'ss'];
        $jceSample = ['B', 'A', 'B', 'C', 'A', 'B', 'C', 'A', 'B', 'C', 'A', 'B', 'C', 'A', 'B', 'C', 'A'];

        [$sampleGrade, $gradeHeadings, $gradeSample] = match ($resolvedType) {
            SchoolSetup::TYPE_JUNIOR, SchoolSetup::TYPE_PRE_F3 => ['F1', $psleHeadings, $psleSample],
            SchoolSetup::TYPE_JUNIOR_SENIOR => ['F1', $psleHeadings, $psleSample],
            SchoolSetup::TYPE_SENIOR => ['F4', $jceHeadings, $jceSample],
            SchoolSetup::TYPE_K12 => ['F1', array_merge($psleHeadings, $jceHeadings), array_merge($psleSample, array_fill(0, count($jceHeadings), ''))],
            default => ['STD 1', [], []],
        };

        $baseSample[10] = $sampleGrade;

        return new self(
            array_merge($baseHeadings, $gradeHeadings),
            array_merge($baseSample, $gradeSample)
        );
    }

    public static function admissions(): self {
        return new self(
            ['connect_id', 'first_name', 'last_name', 'middle_name', 'gender', 'date_of_birth', 'nationality', 'phone', 'id_number', 'grade', 'year', 'status'],
            ['345632', 'Cheri', 'Bolz', 'B.', 'F', '03/09/2010', 'Motswana', '73879654', '367729981', 'STD 1', '2025', 'Applied']
        );
    }
}
