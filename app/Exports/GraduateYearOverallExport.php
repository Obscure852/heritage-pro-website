<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GraduateYearOverallExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle {
    protected $reportData;
    protected $schoolData;

    public function __construct($reportData, $schoolData) {
        $this->reportData = $reportData;
        $this->schoolData = $schoolData;
    }

    public function title(): string {
        return 'Graduate Year ' . $this->reportData['graduation_year'];
    }

    public function array(): array {
        $rows = [];
        
        foreach ($this->reportData['students'] as $student) {
            $row = [
                $student['student_name'],
                $student['class_name'],
                $student['psle_grade'] ?? '-',
                $student['exam_type'] ?? 'N/A'
            ];
            
            // Add subject grades
            foreach ($this->reportData['subjects'] as $subject) {
                $row[] = $student['subjects'][$subject] ?? '';
            }
            
            // Add total points and overall grade
            $row[] = (($student['has_results'] ?? false) && $student['total_points'] !== null)
                ? number_format($student['total_points'], 1)
                : '-';
            $row[] = !empty($student['overall_grade']) ? $student['overall_grade'] : '-';
            
            $rows[] = $row;
        }
        
        return $rows;
    }

    public function headings(): array {
        $headings = [
            'Student Name',
            'Class',
            'PSLE',
            'Exam'
        ];
        
        foreach ($this->reportData['subjects'] as $subject) {
            $headings[] = strtoupper(substr($subject, 0, 3));
        }
        
        $headings[] = 'TP';
        $headings[] = 'Grade';
        
        return $headings;
    }

    public function styles(Worksheet $sheet) {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        
        // Style the heading row
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        
        // Style all data cells with borders
        if ($lastRow > 1) {
            $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);
            
            // Center align all columns except first (student name)
            $sheet->getStyle('B2:' . $lastColumn . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }
        
        return [];
    }
}
