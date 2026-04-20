<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinalsClassSubjectSummaryReport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle{
    protected $reportData;

    public function __construct($reportData){
        $this->reportData = $reportData;
    }

    public function array(): array{
        $data = [];
        
        foreach ($this->reportData['subjects'] as $subject) {
            $subjectData = $this->reportData['subject_data'][$subject] ?? null;
            
            if ($subjectData) {
                $row = [
                    $subject,
                    
                    $subjectData['grades']['A']['M'],
                    $subjectData['grades']['A']['F'],
                    $subjectData['grades']['A']['T'],

                    $subjectData['grades']['B']['M'],
                    $subjectData['grades']['B']['F'],
                    $subjectData['grades']['B']['T'],
                    
                    $subjectData['grades']['C']['M'],
                    $subjectData['grades']['C']['F'],
                    $subjectData['grades']['C']['T'],
                    
                    $subjectData['grades']['D']['M'],
                    $subjectData['grades']['D']['F'],
                    $subjectData['grades']['D']['T'],

                    $subjectData['grades']['E']['M'],
                    $subjectData['grades']['E']['F'],
                    $subjectData['grades']['E']['T'],
                    
                    $subjectData['grades']['F']['M'] ?? 0,
                    $subjectData['grades']['F']['F'] ?? 0,
                    $subjectData['grades']['F']['T'] ?? 0,
                    
                    $subjectData['grades']['U']['M'],
                    $subjectData['grades']['U']['F'],
                    $subjectData['grades']['U']['T'],
                    
                    $subjectData['percentages']['AB']['M'] . '%',
                    $subjectData['percentages']['AB']['F'] . '%',
                    $subjectData['percentages']['AB']['T'] . '%',
                    
                    $subjectData['percentages']['ABC']['M'] . '%',
                    $subjectData['percentages']['ABC']['F'] . '%',
                    $subjectData['percentages']['ABC']['T'] . '%',
                    
                    $subjectData['percentages']['DEU']['M'] . '%',
                    $subjectData['percentages']['DEU']['F'] . '%',
                    $subjectData['percentages']['DEU']['T'] . '%',
                ];
                
                $data[] = $row;
            }
        }
        return $data;
    }

    public function headings(): array{
        return [
            ['Class Subjects Summary Report'],
            [''],
            [
                'Subject',
                'A', '', '',
                'B', '', '',
                'C', '', '',
                'D', '', '',
                'E', '', '',
                'F', '', '',
                'U', '', '',
                'AB%', '', '',
                'ABC%', '', '',
                'DEU%', '', ''
            ],
            [
                '',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T',
                'M', 'F', 'T'
            ]
        ];
    }

    public function styles(Worksheet $sheet){
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        
        $sheet->mergeCells('A1:AE1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '5156BE']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getStyle('A3:' . $highestColumn . '4')->applyFromArray([
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
                'startColor' => ['rgb' => '5156BE']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        $sheet->getStyle('A5:A' . $highestRow)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'e8f0fe']
            ]
        ]);

        $sheet->getStyle('B5:' . $highestColumn . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'cccccc']
                ]
            ]
        ]);

        $gradeColumns = [
            ['B3:D3', 'A'], ['E3:G3', 'B'], ['H3:J3', 'C'], 
            ['K3:M3', 'D'], ['N3:P3', 'E'], ['Q3:S3', 'F'], 
            ['T3:V3', 'U'], ['W3:Y3', 'AB%'], ['Z3:AB3', 'ABC%'], 
            ['AC3:AE3', 'DEU%']
        ];

        foreach ($gradeColumns as $column) {
            $sheet->mergeCells($column[0]);
            $sheet->setCellValue(explode(':', $column[0])[0], $column[1]);
        }

        for ($row = 5; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'f8f9fa']
                    ]
                ]);
            }
        }

        $sheet->freezePane('B5');
        return $sheet;
    }

    public function columnWidths(): array{
        return [
            'A' => 30,
            'B' => 8,      'C' => 8,      'D' => 8,
            'E' => 8,      'F' => 8,      'G' => 8,
            'H' => 8,      'I' => 8,      'J' => 8,
            'K' => 8,      'L' => 8,      'M' => 8,
            'N' => 8,      'O' => 8,      'P' => 8,
            'Q' => 8,      'R' => 8,      'S' => 8,
            'T' => 8,      'U' => 8,      'V' => 8,
            'W' => 10,     'X' => 10,     'Y' => 10,
            'Z' => 10,     'AA' => 10,    'AB' => 10,
            'AC' => 10,    'AD' => 10,    'AE' => 10,
        ];
    }

    public function title(): string{
        return 'Class Subjects Summary Analysis';
    }
}