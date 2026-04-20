<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ClassAnalysisExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles {
    protected $data;

    public function __construct($data){
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'TEACHER',
            'Class',
            'GENDER',
            'HOUSE',
            'A',
            'B',
            'C',
            'D',
            'E',
            'U',
            'X',
            'TOTAL',
            'G/TOTAL',
            'A-C%',
            'A-D%',
            'E-U%'
        ];
    }

    public function array(): array
    {
        $rows = [];
        
        foreach ($this->data['classes'] as $index => $class) {
            $rows[] = [
                $class['teacher'],
                $class['class'],
                'Male',
                $class['house'],
                $class['grades']['A']['M'],
                $class['grades']['B']['M'],
                $class['grades']['C']['M'],
                $class['grades']['D']['M'],
                $class['grades']['E']['M'],
                $class['grades']['U']['M'],
                $class['grades']['X']['M'],
                $class['male_count'],
                $class['total'],
                $class['a_c_percentage'],
                $class['a_d_percentage'],
                $class['e_u_percentage']
            ];
            
            $rows[] = [
                '', 
                '',
                'Female',
                '',
                $class['grades']['A']['F'],
                $class['grades']['B']['F'],
                $class['grades']['C']['F'],
                $class['grades']['D']['F'],
                $class['grades']['E']['F'],
                $class['grades']['U']['F'],
                $class['grades']['X']['F'],
                $class['female_count'],
                '',
                '',
                '',
                ''
            ];
        }
        
        $rows[] = [
            'Total',
            '',
            '',
            '',
            $this->data['totalGrades']['A'],
            $this->data['totalGrades']['B'],
            $this->data['totalGrades']['C'],
            $this->data['totalGrades']['D'],
            $this->data['totalGrades']['E'],
            $this->data['totalGrades']['U'],
            $this->data['totalGrades']['X'],
            $this->data['totalStudents'],
            $this->data['totalStudents'],
            $this->data['overallABPercentage'],
            $this->data['overallADPercentage'],
            $this->data['overallEUPercentage']
        ];
        
        return $rows;
    }

    public function styles(Worksheet $sheet){
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        $highestRow = $sheet->getHighestRow();
        $classCount = count($this->data['classes']);
        
        $sheet->getStyle('A1:P' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $sheet->getStyle('A2:P' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:P' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        $sheet->getStyle('A2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        for ($i = 0; $i < $classCount; $i++) {
            $startRow = 2 + ($i * 2);
            $endRow = $startRow + 1;
            
            $sheet->mergeCells("A{$startRow}:A{$endRow}");
        
            $sheet->mergeCells("B{$startRow}:B{$endRow}");

            $sheet->mergeCells("D{$startRow}:D{$endRow}");
            
            $sheet->mergeCells("M{$startRow}:M{$endRow}");
            
            $sheet->mergeCells("N{$startRow}:N{$endRow}");
            $sheet->mergeCells("O{$startRow}:O{$endRow}");
            $sheet->mergeCells("P{$startRow}:P{$endRow}");
            
            $acPercent = $sheet->getCell("N{$startRow}")->getValue();
            $adPercent = $sheet->getCell("O{$startRow}")->getValue();
            $euPercent = $sheet->getCell("P{$startRow}")->getValue();
            
            if ($acPercent >= 50) {
                $sheet->getStyle("N{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
            } elseif ($acPercent >= 30) {
                $sheet->getStyle("N{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
            } else {
                $sheet->getStyle("N{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
            }
            
            if ($adPercent >= 70) {
                $sheet->getStyle("O{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
            } elseif ($adPercent >= 50) {
                $sheet->getStyle("O{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
            } else {
                $sheet->getStyle("O{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
            }
            
            if ($euPercent <= 15) {
                $sheet->getStyle("P{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
            } elseif ($euPercent <= 30) {
                $sheet->getStyle("P{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
            } else {
                $sheet->getStyle("P{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
            }
        }
        
        $totalRow = $highestRow;
        $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
        $sheet->getStyle("A{$totalRow}:P{$totalRow}")->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFF99']
            ]
        ]);
        
        $acTotalPercent = $sheet->getCell("N{$totalRow}")->getValue();
        $adTotalPercent = $sheet->getCell("O{$totalRow}")->getValue();
        $euTotalPercent = $sheet->getCell("P{$totalRow}")->getValue();
        
        if ($acTotalPercent >= 50) {
            $sheet->getStyle("N{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
        } elseif ($acTotalPercent >= 30) {
            $sheet->getStyle("N{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
        } else {
            $sheet->getStyle("N{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
        }
        
        if ($adTotalPercent >= 70) {
            $sheet->getStyle("O{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
        } elseif ($adTotalPercent >= 50) {
            $sheet->getStyle("O{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
        } else {
            $sheet->getStyle("O{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
        }
        
        if ($euTotalPercent <= 15) {
            $sheet->getStyle("P{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
        } elseif ($euTotalPercent <= 30) {
            $sheet->getStyle("P{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
        } else {
            $sheet->getStyle("P{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
        }
        
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells('A1:P1');
        $titleText = 'Class Analysis Report - ' . ucfirst($this->data['type']) . ' Analysis';
        $sheet->setCellValue('A1', $titleText);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        $sheet->insertNewRowBefore(2, 1);
        $sheet->mergeCells('A2:P2');
        $termText = 'Term: ' . $this->data['currentTerm']->name . ' - Academic Year: ' . $this->data['currentTerm']->year;
        $sheet->setCellValue('A2', $termText);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        $sheet->getStyle('A4:P4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '000000']
            ]
        ]);
        
        $dataStartRow = 5;
        $highestRow = $sheet->getHighestRow();
        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            if (($row - $dataStartRow) % 2 == 1) {
                continue;
            }
            
            $nValue = $sheet->getCell("N{$row}")->getValue();
            if (is_numeric($nValue)) {
                $sheet->setCellValue("N{$row}", $nValue . '%');
            }
            
            $oValue = $sheet->getCell("O{$row}")->getValue();
            if (is_numeric($oValue)) {
                $sheet->setCellValue("O{$row}", $oValue . '%');
            }
            
            $pValue = $sheet->getCell("P{$row}")->getValue();
            if (is_numeric($pValue)) {
                $sheet->setCellValue("P{$row}", $pValue . '%');
            }
        }
        
        return [
            // Return an empty array since we've already styled everything
        ];
    }
}
