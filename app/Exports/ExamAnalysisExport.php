<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamAnalysisExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle{
    protected $reportCardsData;
    protected $allSubjects;
    protected $gradeCounts;
    protected $gradePercentages;
    protected $subjectGradeCounts;
    protected $psleGradeCounts;
    protected $mabcPercentage;
    protected $mabcdPercentage;

    public function __construct($data)
    {
        $this->reportCardsData = $data['reportCards'];
        $this->allSubjects = $data['allSubjects'];
        $this->gradeCounts = $data['gradeCounts'];
        $this->gradePercentages = $data['gradePercentages'];
        $this->subjectGradeCounts = $data['subjectGradeCounts'];
        $this->psleGradeCounts = $data['psleGradeCounts'];
        $this->mabcPercentage = $data['mabcPercentage'];
        $this->mabcdPercentage = $data['mabcdPercentage'];
    }

    public function array(): array {
        $examData = [];
        
        $examData[] = ['EXAM CLASS ANALYSIS'];
        $examData[] = [''];
        
        $headers = ['#', 'Name', 'Class', 'Sex', 'PSLE'];
        foreach ($this->allSubjects as $subject) {
            $headers[] = $subject . ' %';
            $headers[] = $subject . ' Grade';
        }
        $headers = array_merge($headers, ['Total Points', 'Overall Grade', 'Position']);
        $examData[] = $headers;

        foreach ($this->reportCardsData as $index => $reportCard) {
            $row = [
                $index + 1,
                $reportCard['student']->fullName ?? '',
                $reportCard['class_name'] ?? '',
                $reportCard['student']->gender ?? '',
                $reportCard['student']->psle->grade ?? '',
            ];

            foreach ($this->allSubjects as $subject) {
                $row[] = isset($reportCard['scores'][$subject]['percentage']) ? 
                    round($reportCard['scores'][$subject]['percentage']) : '';
                $row[] = $reportCard['scores'][$subject]['grade'] ?? '';
            }

            $row[] = $reportCard['totalPoints'] ?? '';
            $row[] = $reportCard['grade'] ?? '';
            $row[] = $reportCard['position'] ?? '';

            $examData[] = $row;
        }

        $examData[] = [''];
        $examData[] = [''];

        $examData[] = ['CLASS GRADES ANALYSIS'];
        $examData[] = [''];
        
        $gradeHeaders = ['Grade'];
        foreach (['M', 'A', 'B', 'C', 'D'] as $grade) {
            $gradeHeaders[] = $grade . ' (M)';
            $gradeHeaders[] = $grade . ' (F)';
        }
        $gradeHeaders[] = 'MABC%';
        $gradeHeaders[] = 'MABCD%';
        
        $examData[] = $gradeHeaders;
        
        $gradeRow = ['Total'];
        foreach (['M', 'A', 'B', 'C', 'D'] as $grade) {
            $gradeRow[] = $this->gradeCounts[$grade]['M'];
            $gradeRow[] = $this->gradeCounts[$grade]['F'];
        }
        $gradeRow[] = $this->mabcPercentage . '%';
        $gradeRow[] = $this->mabcdPercentage . '%';
        
        $examData[] = $gradeRow;

        $examData[] = [''];
        $examData[] = [''];

        $examData[] = ['SUBJECTS ANALYSIS'];
        $examData[] = [''];

        $subjectHeaders = ['Subject'];
        foreach (['A', 'B', 'C', 'D'] as $grade) {
            $subjectHeaders[] = $grade . ' (M)';
            $subjectHeaders[] = $grade . ' (F)';
        }
        $subjectHeaders[] = 'ABC% (M)';
        $subjectHeaders[] = 'ABC% (F)';
        $subjectHeaders[] = 'ABCD% (M)';
        $subjectHeaders[] = 'ABCD% (F)';

        $examData[] = $subjectHeaders;

        foreach ($this->subjectGradeCounts as $subject => $counts) {
            $row = [$subject];
            foreach (['A', 'B', 'C', 'D'] as $grade) {
                $row[] = $counts[$grade]['M'];
                $row[] = $counts[$grade]['F'];
            }
            $row[] = $counts['ABC%']['M'];
            $row[] = $counts['ABC%']['F'];
            $row[] = $counts['ABCD%']['M'];
            $row[] = $counts['ABCD%']['F'];
            $examData[] = $row;
        }

        return $examData;
    }

    public function headings(): array{
        return []; 
    }

    public function styles(Worksheet $sheet){
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A13')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A20')->getFont()->setBold(true)->setSize(12);

        $headerRanges = ['A3:Z3', 'A15:Z15', 'A22:Z22'];
        foreach ($headerRanges as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ]);
        }

        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ]);
    }

    public function title(): string{
        return 'Exam Analysis';
    }
}
