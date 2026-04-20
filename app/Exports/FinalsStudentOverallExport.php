<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class FinalsStudentOverallExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents{
    protected $reportData;
    protected $schoolData;
    protected $subjectAbbreviations;

    public function __construct($reportData, $schoolData){
        $this->reportData = $reportData;
        $this->schoolData = $schoolData;
        $this->initializeSubjectAbbreviations();
    }

    protected function initializeSubjectAbbreviations(){
        $this->subjectAbbreviations = [
            'english' => 'ENG',
            'mathematics' => 'MATH',
            'science' => 'SCI',
            'social studies' => 'SS',
            'setswana' => 'SET',
            'agriculture' => 'AGR',
            'moral education' => 'ME',
            'design and technology' => 'DT',
            'home economics' => 'HE',
            'art' => 'ART',
            'music' => 'MUS',
            'physical education' => 'PE',
            'religious education' => 'RE',
            'commerce and accounting' => 'CA',
            'commerce and office procedures' => 'COP',
            'creative arts' => 'CAPA',
            'french' => 'FR'
        ];
    }

    protected function getSubjectAbbreviation($subject){
        $subjectLower = strtolower($subject);
        return $this->subjectAbbreviations[$subjectLower] ?? strtoupper(substr($subject, 0, 3));
    }

    public function array(): array{
        $data = [];
        
        foreach ($this->reportData['students'] as $student) {
            $row = [
                $student['student_name'],
                $student['class_name'],
                $student['psle_grade'] ?? '-',
                $student['exam_type'] ?? 'N/A'
            ];
            
            foreach ($this->reportData['subjects'] as $subject) {
                $row[] = $student['subjects'][$subject] ?? '-';
            }
            
            $row[] = (($student['has_results'] ?? false) && $student['total_points'] !== null)
                ? number_format($student['total_points'], 1)
                : '-';
            $row[] = !empty($student['overall_grade']) ? $student['overall_grade'] : '-';
            
            $data[] = $row;
        }
        
        if (!empty($data)) {
            $data[] = [];
            $data[] = $this->getSummaryStatistics();
        }
        
        return $data;
    }

    protected function getSummaryStatistics(){
        $studentsWithResults = collect($this->reportData['students'])->filter(function($student) {
            return $student['has_results'] ?? false;
        });
        
        $totalWithResults = $studentsWithResults->count();
        
        if ($totalWithResults === 0) {
            return ['No external exam results found'];
        }
        
        $passCount = $studentsWithResults->whereIn('overall_grade', ['A', 'B', 'C', 'Merit'])->count();
        $passRate = round(($passCount / $totalWithResults) * 100, 1);
        $averagePoints = round($studentsWithResults->avg('total_points'), 1);
        $highestPoints = $studentsWithResults->max('total_points');
        
        $stats = [];
        $stats[] = ['SUMMARY STATISTICS'];
        $stats[] = ['Total Students with Results:', $totalWithResults];
        $stats[] = ['Pass Rate (A-C):', $passRate . '%'];
        $stats[] = ['Average Points:', $averagePoints];
        $stats[] = ['Highest Points:', $highestPoints];
        $stats[] = [];
        $stats[] = ['GRADE DISTRIBUTION'];

        foreach(['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
            $count = $studentsWithResults->where('overall_grade', $grade)->count();
            if ($count > 0) {
                $percentage = round(($count / $totalWithResults) * 100, 1);
                $stats[] = ["Grade $grade:", "$count students ($percentage%)"];
            }
        }
        
        return $stats;
    }

    public function headings(): array{
        $headers = [
            'Student Name',
            'Class',
            'PSLE',
            'Exam'
        ];
        
        foreach ($this->reportData['subjects'] as $subject) {
            $headers[] = $this->getSubjectAbbreviation($subject);
        }
        
        $headers[] = 'TP';
        $headers[] = 'Grade';
        
        return [
            ['Student Overall Performance'],
            [''],
            [''],
            [''],
            $headers
        ];
    }

    public function styles(Worksheet $sheet){
        $lastColumn = $this->getLastColumnLetter(count($this->headings()[4]));
        $dataStartRow = 6;
        $lastDataRow = $dataStartRow + count($this->reportData['students']) - 1;
        
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18,
                'color' => ['rgb' => '5156be']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('1')->setRowHeight(35);
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
    
        $sheet->mergeCells('A3:' . $lastColumn . '3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        $sheet->getStyle('A5:' . $lastColumn . '5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5156be']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        $sheet->getRowDimension('5')->setRowHeight(25);
        $sheet->getStyle('A' . $dataStartRow . ':' . $lastColumn . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'cccccc']
                ]
            ]
        ]);
        
        $this->applyGradeColors($sheet, $dataStartRow, $lastDataRow);
        for ($row = $dataStartRow; $row <= $lastDataRow; $row++) {
            if (($row - $dataStartRow) % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'f5f5fa']
                    ]
                ]);
            }
        }
        
        if ($lastDataRow < $sheet->getHighestRow()) {
            $summaryStartRow = $lastDataRow + 2;
            $sheet->getStyle('A' . $summaryStartRow . ':B' . $sheet->getHighestRow())->applyFromArray([
                'font' => [
                    'bold' => true
                ]
            ]);

            $sheet->getStyle('A' . $summaryStartRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => '5156be']
                ]
            ]);
            
            $gradeDistRow = $summaryStartRow + 6;
            if ($sheet->getCellByColumnAndRow(1, $gradeDistRow)->getValue() == 'GRADE DISTRIBUTION') {
                $sheet->getStyle('A' . $gradeDistRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '5156be']
                    ]
                ]);
            }
        }
        
        $psleCol = 'C';
        $examCol = 'D';
        $tpCol = $this->getColumnLetter(count($this->headings()[4]) - 1);
        $gradeCol = $lastColumn;
        
        $sheet->getStyle($psleCol . $dataStartRow . ':' . $psleCol . $lastDataRow)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($tpCol . $dataStartRow . ':' . $tpCol . $lastDataRow)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($gradeCol . $dataStartRow . ':' . $gradeCol . $lastDataRow)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->freezePane('E6');
        
        return $sheet;
    }

    protected function applyGradeColors(Worksheet $sheet, $startRow, $endRow){
        $gradeColors = [
            'A' => 'd5f5d5',
            'B' => 'e5f3ff',
            'C' => 'fff3e5',
            'D' => 'ffffd5',
            'E' => 'ffe5e5',
            'U' => 'ffd5d5',
            'Merit' => 'd5f5d5'
        ];
        
        $psleCol = 3;
        $subjectStartCol = 5;
        $subjectEndCol = $subjectStartCol + count($this->reportData['subjects']) - 1;
        $gradeCol = $subjectEndCol + 2;
        
        for ($row = $startRow; $row <= $endRow; $row++) {
            $psleGrade = $sheet->getCellByColumnAndRow($psleCol, $row)->getValue();
            if (isset($gradeColors[$psleGrade])) {
                $sheet->getCellByColumnAndRow($psleCol, $row)->getStyle()->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $gradeColors[$psleGrade]]
                    ]
                ]);
            }
            
            for ($col = $subjectStartCol; $col <= $subjectEndCol; $col++) {
                $grade = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                if (isset($gradeColors[$grade])) {
                    $sheet->getCellByColumnAndRow($col, $row)->getStyle()->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $gradeColors[$grade]]
                        ]
                    ]);
                }
            }
            
            $overallGrade = $sheet->getCellByColumnAndRow($gradeCol, $row)->getValue();
            if (isset($gradeColors[$overallGrade])) {
                $sheet->getCellByColumnAndRow($gradeCol, $row)->getStyle()->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $gradeColors[$overallGrade]]
                    ]
                ]);
            }
            
            $tpCol = $gradeCol - 1;
            $totalPoints = floatval($sheet->getCellByColumnAndRow($tpCol, $row)->getValue());
            if ($totalPoints > 0) {
                $color = $totalPoints >= 40 ? 'd5f5d5' : ($totalPoints >= 25 ? 'ffffd5' : 'ffffff');
                $sheet->getCellByColumnAndRow($tpCol, $row)->getStyle()->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $color]
                    ]
                ]);
            }
        }
    }

    public function columnWidths(): array{
        $widths = [
            'A' => 30,
            'B' => 12,
            'C' => 8,
            'D' => 10,
        ];
        
        $col = 'E';
        foreach ($this->reportData['subjects'] as $subject) {
            $widths[$col] = 8;
            $col++;
        }
        
        $widths[$col] = 10;
        $widths[++$col] = 10;
        
        return $widths;
    }

    protected function getColumnLetter($columnNumber){
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }

    protected function getLastColumnLetter($totalColumns){
        return $this->getColumnLetter($totalColumns);
    }

    public function title(): string{
        return 'Student Performance - ' . $this->reportData['class_info']['name'];
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow() + 2;
                
                $sheet->setCellValue('A' . $lastRow, 'Generated on: ' . date('d/m/Y H:i'));
                $sheet->getStyle('A' . $lastRow)->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => '666666']
                    ]
                ]);
            },
        ];
    }
}
