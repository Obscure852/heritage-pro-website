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

class HouseAnalysisExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle{
    private array $data;

    public function __construct(array $data){
        $this->data = $data;
    }
    
    public function title(): string{
        return 'House Analysis - ' . ucfirst($this->data['type']);
    }
    
    public function columnWidths(): array{
        return [
            'A' => 15,
            'B' => 10,
            'C' => 5,
            'D' => 5, 
            'E' => 5, 
            'F' => 5, 
            'G' => 5, 
            'H' => 5, 
            'I' => 5, 
            'J' => 5, 
            'K' => 5,
            'L' => 5, 
            'M' => 5, 
            'N' => 5, 
            'O' => 5, 
            'P' => 5, 
            'Q' => 5, 
            'R' => 5, 
            'S' => 5, 
            'T' => 5,
            'U' => 5, 
            'V' => 5, 
            'W' => 7, 
            'X' => 7, 
            'Y' => 7, 
            'Z' => 7, 
            'AA' => 7, 
            'AB' => 7, 
            'AC' => 7,
        ];
    }

    public function headings(): array{
        return [
            [
                'House', 'Class', 
                'A', 'A', 'A', 
                'B', 'B', 'B', 
                'C', 'C', 'C', 
                'D', 'D', 'D', 
                'E', 'E', 'E', 
                'U', 'U', 'U', 
                'Total', 'Total', 'Total', 
                'ABC %', 'ABC %', 'ABC %', 
                'ABCD %', 'ABCD %', 'ABCD %'
            ],
            [
                '', '',
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

    public function array(): array{
        $rows = [];

        foreach ($this->data['houseAnalysis'] as $house) {
            $rows[] = [
                $house['name'], '', '', '', '', '', '', '', '', '', '', 
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            foreach ($house['classes'] as $cls) {
                $maleABC = 0;
                $femaleABC = 0;
                $maleABCD = 0;
                $femaleABCD = 0;
                
                foreach (['A', 'B', 'C'] as $g) {
                    $maleABC += $cls['grades'][$g]['M'];
                    $femaleABC += $cls['grades'][$g]['F'];
                }
                
                $maleABCD = $maleABC + $cls['grades']['D']['M'];
                $femaleABCD = $femaleABC + $cls['grades']['D']['F'];
                
                $maleCount = $cls['male_count'] ?? 0;
                $femaleCount = $cls['female_count'] ?? 0;
                
                $maleABCPercent = $maleCount > 0 ? round(($maleABC / $maleCount) * 100, 1) : 0;
                $femaleABCPercent = $femaleCount > 0 ? round(($femaleABC / $femaleCount) * 100, 1) : 0;
                $maleABCDPercent = $maleCount > 0 ? round(($maleABCD / $maleCount) * 100, 1) : 0;
                $femaleABCDPercent = $femaleCount > 0 ? round(($femaleABCD / $femaleCount) * 100, 1) : 0;
                
                $rows[] = [
                    '', $cls['class_name'],
                    $cls['grades']['A']['M'], $cls['grades']['A']['F'], $cls['grades']['A']['M'] + $cls['grades']['A']['F'],
                    $cls['grades']['B']['M'], $cls['grades']['B']['F'], $cls['grades']['B']['M'] + $cls['grades']['B']['F'],
                    $cls['grades']['C']['M'], $cls['grades']['C']['F'], $cls['grades']['C']['M'] + $cls['grades']['C']['F'],
                    $cls['grades']['D']['M'], $cls['grades']['D']['F'], $cls['grades']['D']['M'] + $cls['grades']['D']['F'],
                    $cls['grades']['E']['M'], $cls['grades']['E']['F'], $cls['grades']['E']['M'] + $cls['grades']['E']['F'],
                    $cls['grades']['U']['M'], $cls['grades']['U']['F'], $cls['grades']['U']['M'] + $cls['grades']['U']['F'],
                    $maleCount, $femaleCount, $cls['total'],
                    $maleABCPercent, $femaleABCPercent, $cls['abc_percentage'],
                    $maleABCDPercent, $femaleABCDPercent, $cls['abcd_percentage']
                ];
            }

            $houseMaleABC = 0;
            $houseFemaleABC = 0;
            $houseMaleABCD = 0;
            $houseFemaleABCD = 0;
            
            foreach (['A', 'B', 'C'] as $g) {
                $houseMaleABC += $house['totals'][$g]['M'];
                $houseFemaleABC += $house['totals'][$g]['F'];
            }
            
            $houseMaleABCD = $houseMaleABC + $house['totals']['D']['M'];
            $houseFemaleABCD = $houseFemaleABC + $house['totals']['D']['F'];
            
            $houseMaleCount = $house['totals']['male_count'] ?? 0;
            $houseFemaleCount = $house['totals']['female_count'] ?? 0;
            
            $houseMaleABCPercent = $houseMaleCount > 0 ? round(($houseMaleABC / $houseMaleCount) * 100, 1) : 0;
            $houseFemaleABCPercent = $houseFemaleCount > 0 ? round(($houseFemaleABC / $houseFemaleCount) * 100, 1) : 0;
            $houseMaleABCDPercent = $houseMaleCount > 0 ? round(($houseMaleABCD / $houseMaleCount) * 100, 1) : 0;
            $houseFemaleABCDPercent = $houseFemaleCount > 0 ? round(($houseFemaleABCD / $houseFemaleCount) * 100, 1) : 0;
            
            $rows[] = [
                'Total', '',
                $house['totals']['A']['M'], $house['totals']['A']['F'], $house['totals']['A']['M'] + $house['totals']['A']['F'],
                $house['totals']['B']['M'], $house['totals']['B']['F'], $house['totals']['B']['M'] + $house['totals']['B']['F'],
                $house['totals']['C']['M'], $house['totals']['C']['F'], $house['totals']['C']['M'] + $house['totals']['C']['F'],
                $house['totals']['D']['M'], $house['totals']['D']['F'], $house['totals']['D']['M'] + $house['totals']['D']['F'],
                $house['totals']['E']['M'], $house['totals']['E']['F'], $house['totals']['E']['M'] + $house['totals']['E']['F'],
                $house['totals']['U']['M'], $house['totals']['U']['F'], $house['totals']['U']['M'] + $house['totals']['U']['F'],
                $houseMaleCount, $houseFemaleCount, $house['totals']['total'],
                $houseMaleABCPercent, $houseFemaleABCPercent, $house['totals']['abc_percentage'],
                $houseMaleABCDPercent, $houseFemaleABCDPercent, $house['totals']['abcd_percentage']
            ];
        }

        $totalMaleABC = 0;
        $totalFemaleABC = 0;
        $totalMaleABCD = 0;
        $totalFemaleABCD = 0;
        
        foreach (['A', 'B', 'C'] as $g) {
            $totalMaleABC += $this->data['totalGrades'][$g]['M'];
            $totalFemaleABC += $this->data['totalGrades'][$g]['F'];
        }
        
        $totalMaleABCD = $totalMaleABC + $this->data['totalGrades']['D']['M'];
        $totalFemaleABCD = $totalFemaleABC + $this->data['totalGrades']['D']['F'];
        
        $totalMaleCount = $this->data['totalMaleCount'] ?? 0;
        $totalFemaleCount = $this->data['totalFemaleCount'] ?? 0;
        
        $totalMaleABCPercent = $totalMaleCount > 0 ? round(($totalMaleABC / $totalMaleCount) * 100, 1) : 0;
        $totalFemaleABCPercent = $totalFemaleCount > 0 ? round(($totalFemaleABC / $totalFemaleCount) * 100, 1) : 0;
        $totalMaleABCDPercent = $totalMaleCount > 0 ? round(($totalMaleABCD / $totalMaleCount) * 100, 1) : 0;
        $totalFemaleABCDPercent = $totalFemaleCount > 0 ? round(($totalFemaleABCD / $totalFemaleCount) * 100, 1) : 0;
        
        $rows[] = [
            'Grand Total', '',
            $this->data['totalGrades']['A']['M'], $this->data['totalGrades']['A']['F'], $this->data['overallTotals']['A'],
            $this->data['totalGrades']['B']['M'], $this->data['totalGrades']['B']['F'], $this->data['overallTotals']['B'],
            $this->data['totalGrades']['C']['M'], $this->data['totalGrades']['C']['F'], $this->data['overallTotals']['C'],
            $this->data['totalGrades']['D']['M'], $this->data['totalGrades']['D']['F'], $this->data['overallTotals']['D'],
            $this->data['totalGrades']['E']['M'], $this->data['totalGrades']['E']['F'], $this->data['overallTotals']['E'],
            $this->data['totalGrades']['U']['M'], $this->data['totalGrades']['U']['F'], $this->data['overallTotals']['U'],
            $totalMaleCount, $totalFemaleCount, $this->data['grandTotal'],
            $totalMaleABCPercent, $totalFemaleABCPercent, $this->data['overallABCPercentage'],
            $totalMaleABCDPercent, $totalFemaleABCDPercent, $this->data['overallABCDPercentage']
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array{
        $lastRow = count($this->array()) + 2;
        
        $sheet->getStyle('A1:AC' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $sheet->getStyle('A1:AC2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000']
            ]
        ]);
        
        foreach(['C', 'F', 'I', 'L', 'O', 'R', 'U', 'X', 'AA'] as $colIndex => $col) {
            $sheet->mergeCells($col . '1:' . chr(ord($col) + 2) . '1');
        }
        
        $this->applyGenderColors($sheet, 3, $lastRow);
        
        $rowIndex = 3;
        foreach ($this->data['houseAnalysis'] as $house) {
            $sheet->mergeCells('A' . $rowIndex . ':AC' . $rowIndex);
            $sheet->getStyle('A' . $rowIndex . ':AC' . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '445E88']
                ]
            ]);
            
            $rowIndex += count($house['classes']) + 1;
            $sheet->getStyle('A' . $rowIndex . ':AC' . $rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ]
            ]);
            
            $rowIndex++;
        }
        
        $sheet->getStyle('A' . $lastRow . ':AC' . $lastRow)->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFC107']
            ]
        ]);
        
        $sheet->getStyle('A3:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->formatPercentageCells($sheet, 3, $lastRow);
        $this->conditionalFormatPercentages($sheet, 3, $lastRow);
        
        return [];
    }
    
    private function applyGenderColors(Worksheet $sheet, int $startRow, int $endRow): void{
        $maleColumns = ['C', 'F', 'I', 'L', 'O', 'R', 'U', 'X', 'AA'];
        foreach ($maleColumns as $col) {
            $sheet->getStyle($col . $startRow . ':' . $col . $endRow)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ]);
        }
        
        $femaleColumns = ['D', 'G', 'J', 'M', 'P', 'S', 'V', 'Y', 'AB'];
        foreach ($femaleColumns as $col) {
            $sheet->getStyle($col . $startRow . ':' . $col . $endRow)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FCE4EC']
                ]
            ]);
        }
        
        $totalColumns = ['E', 'H', 'K', 'N', 'Q', 'T', 'W', 'Z', 'AC'];
        foreach ($totalColumns as $col) {
            $sheet->getStyle($col . $startRow . ':' . $col . $endRow)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0F7FA']
                ]
            ]);
        }
    }
    
    private function formatPercentageCells(Worksheet $sheet, int $startRow, int $endRow): void{
        for ($row = $startRow; $row <= $endRow; $row++) {
            foreach (['X', 'Y', 'Z', 'AA', 'AB', 'AC'] as $col) {
                $cell = $sheet->getCell($col . $row);
                $value = $cell->getValue();
                
                if ($value === '') continue;
                
                if (substr($value, -1) !== '%') {
                    $sheet->setCellValue($col . $row, $value . '%');
                }
            }
        }
    }
    
    private function conditionalFormatPercentages(Worksheet $sheet, int $startRow, int $endRow): void{
        for ($row = $startRow; $row <= $endRow; $row++) {

            if ($sheet->getCell('X' . $row)->getValue() === '') continue;
            
            $abcMale = (float)$sheet->getCell('X' . $row)->getValue();
            $abcFemale = (float)$sheet->getCell('Y' . $row)->getValue();
            $abcTotal = (float)$sheet->getCell('Z' . $row)->getValue();
            
            $abcdMale = (float)$sheet->getCell('AA' . $row)->getValue();
            $abcdFemale = (float)$sheet->getCell('AB' . $row)->getValue();
            $abcdTotal = (float)$sheet->getCell('AC' . $row)->getValue();
            
            $abcMale = str_replace('%', '', $abcMale);
            $abcFemale = str_replace('%', '', $abcFemale);
            $abcTotal = str_replace('%', '', $abcTotal);
            $abcdMale = str_replace('%', '', $abcdMale);
            $abcdFemale = str_replace('%', '', $abcdFemale);
            $abcdTotal = str_replace('%', '', $abcdTotal);
            
            $this->applyPercentageColor($sheet, 'X' . $row, $abcMale, 50, 30);
            $this->applyPercentageColor($sheet, 'Y' . $row, $abcFemale, 50, 30);
            $this->applyPercentageColor($sheet, 'Z' . $row, $abcTotal, 50, 30);
            $this->applyPercentageColor($sheet, 'AA' . $row, $abcdMale, 70, 50);
            $this->applyPercentageColor($sheet, 'AB' . $row, $abcdFemale, 70, 50);
            $this->applyPercentageColor($sheet, 'AC' . $row, $abcdTotal, 70, 50);
        }
    }
    
    private function applyPercentageColor(Worksheet $sheet, string $cell, float $value, int $highThreshold, int $mediumThreshold): void{
        $color = $value >= $highThreshold ? 'd5f5d5' : ($value >= $mediumThreshold ? 'ffffd5' : 'ffd5d5');
        $sheet->getStyle($cell)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color]
            ]
        ]);
    }
}