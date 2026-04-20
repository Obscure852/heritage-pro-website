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

class GradeDistributionExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles {
    protected $data;

    public function __construct($data){
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Gender',
            'Merit',
            'A',
            'B',
            'C',
            'D',
            'E',
            'U',
            'Total',
            'MAB%',
            'MABC%',
            'MABCD%',
            'Total',
        ];
    }

    public function array(): array
    {
        $rows = [];
        
        // Calculate MAB percentages
        $mab_M_Percentage = $this->data['validMaleCount'] > 0 ? 
            round((($this->data['m_M'] + $this->data['a_M'] + $this->data['b_M']) / $this->data['validMaleCount']) * 100, 1) : 0;
            
        $mab_F_Percentage = $this->data['validFemaleCount'] > 0 ? 
            round((($this->data['m_F'] + $this->data['a_F'] + $this->data['b_F']) / $this->data['validFemaleCount']) * 100, 1) : 0;
            
        $mab_T_percentage = $this->data['validTotalStudents'] > 0 ? 
            round((($this->data['sumM'] + $this->data['sumA'] + $this->data['sumB']) / $this->data['validTotalStudents']) * 100, 1) : 0;
        
        // Male row
        $rows[] = [
            'Male',
            $this->data['m_M'],
            $this->data['a_M'],
            $this->data['b_M'],
            $this->data['c_M'],
            $this->data['d_M'],
            $this->data['e_M'],
            $this->data['u_M'],
            $this->data['validMaleCount'],
            $mab_M_Percentage,
            $this->data['mabc_M_Percentage'],
            $this->data['mabcd_M_Percentage'],
            $this->data['maleCount'],
        ];
        
        // Male percentage row
        $validMaleCount = $this->data['validMaleCount'];
        $rows[] = [
            '%',
            $validMaleCount > 0 ? round(($this->data['m_M'] / $validMaleCount) * 100, 1) : 0,
            $validMaleCount > 0 ? round(($this->data['a_M'] / $validMaleCount) * 100, 1) : 0,
            $validMaleCount > 0 ? round(($this->data['b_M'] / $validMaleCount) * 100, 1) : 0,
            $validMaleCount > 0 ? round(($this->data['c_M'] / $validMaleCount) * 100, 1) : 0,
            $validMaleCount > 0 ? round(($this->data['d_M'] / $validMaleCount) * 100, 1) : 0,
            $validMaleCount > 0 ? round(($this->data['e_M'] / $validMaleCount) * 100, 1) : 0,
            $validMaleCount > 0 ? round(($this->data['u_M'] / $validMaleCount) * 100, 1) : 0,
            '',
            '',
            '',
            '',
            '',
        ];
        
        // Female row
        $rows[] = [
            'Female',
            $this->data['m_F'],
            $this->data['a_F'],
            $this->data['b_F'],
            $this->data['c_F'],
            $this->data['d_F'],
            $this->data['e_F'],
            $this->data['u_F'],
            $this->data['validFemaleCount'],
            $mab_F_Percentage,
            $this->data['mabc_F_Percentage'],
            $this->data['mabcd_F_Percentage'],
            $this->data['femaleCount'],
        ];
        
        // Female percentage row
        $validFemaleCount = $this->data['validFemaleCount'];
        $rows[] = [
            '%',
            $validFemaleCount > 0 ? round(($this->data['m_F'] / $validFemaleCount) * 100, 1) : 0,
            $validFemaleCount > 0 ? round(($this->data['a_F'] / $validFemaleCount) * 100, 1) : 0,
            $validFemaleCount > 0 ? round(($this->data['b_F'] / $validFemaleCount) * 100, 1) : 0,
            $validFemaleCount > 0 ? round(($this->data['c_F'] / $validFemaleCount) * 100, 1) : 0,
            $validFemaleCount > 0 ? round(($this->data['d_F'] / $validFemaleCount) * 100, 1) : 0,
            $validFemaleCount > 0 ? round(($this->data['e_F'] / $validFemaleCount) * 100, 1) : 0,
            $validFemaleCount > 0 ? round(($this->data['u_F'] / $validFemaleCount) * 100, 1) : 0,
            '',
            '',
            '',
            '',
            '',
        ];
        
        // Total row
        $validTotalStudents = $this->data['validTotalStudents'];
        $rows[] = [
            'Total (' . $validTotalStudents . ')',
            $this->data['sumM'],
            $this->data['sumA'],
            $this->data['sumB'],
            $this->data['sumC'],
            $this->data['sumD'],
            $this->data['sumE'],
            $this->data['sumU'],
            $validTotalStudents,
            $mab_T_percentage,
            $this->data['mabc_T_percentage'],
            $this->data['mabcd_T_percentage'],
            $this->data['totalStudents'],
        ];
        
        // Total percentage row
        $rows[] = [
            '%',
            $validTotalStudents > 0 ? round(($this->data['sumM'] / $validTotalStudents) * 100, 1) : 0,
            $validTotalStudents > 0 ? round(($this->data['sumA'] / $validTotalStudents) * 100, 1) : 0,
            $validTotalStudents > 0 ? round(($this->data['sumB'] / $validTotalStudents) * 100, 1) : 0,
            $validTotalStudents > 0 ? round(($this->data['sumC'] / $validTotalStudents) * 100, 1) : 0,
            $validTotalStudents > 0 ? round(($this->data['sumD'] / $validTotalStudents) * 100, 1) : 0,
            $validTotalStudents > 0 ? round(($this->data['sumE'] / $validTotalStudents) * 100, 1) : 0,
            $validTotalStudents > 0 ? round(($this->data['sumU'] / $validTotalStudents) * 100, 1) : 0,
            '',
            '',
            '',
            '',
            '100%',
        ];
        
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:M1')->applyFromArray([
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
        
        // Apply borders to all cells
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:M' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Center align all data cells
        $sheet->getStyle('A2:M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:M' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        // Style percentage rows with italic
        $sheet->getStyle('A3:M3')->getFont()->setItalic(true);
        $sheet->getStyle('A3:M3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
        
        $sheet->getStyle('A5:M5')->getFont()->setItalic(true);
        $sheet->getStyle('A5:M5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
        
        $sheet->getStyle('A7:M7')->getFont()->setItalic(true);
        $sheet->getStyle('A7:M7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
        
        // Style the totals row
        $sheet->getStyle('A6:M6')->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFF00']  // Yellow background
            ]
        ]);
        
        // Apply color to performance percentages for MAB%, MABC%, and MABCD%
        
        // Male performance percentages (row 2)
        $mabMalePercent = $sheet->getCell("J2")->getValue();
        $mabcMalePercent = $sheet->getCell("K2")->getValue();
        $mabcdMalePercent = $sheet->getCell("L2")->getValue();
        
        $this->applyPercentageColors($sheet, 'J2', $mabMalePercent, 20, 40);
        $this->applyPercentageColors($sheet, 'K2', $mabcMalePercent, 30, 50);
        $this->applyPercentageColors($sheet, 'L2', $mabcdMalePercent, 50, 70);
        
        // Female performance percentages (row 4)
        $mabFemalePercent = $sheet->getCell("J4")->getValue();
        $mabcFemalePercent = $sheet->getCell("K4")->getValue();
        $mabcdFemalePercent = $sheet->getCell("L4")->getValue();
        
        $this->applyPercentageColors($sheet, 'J4', $mabFemalePercent, 20, 40);
        $this->applyPercentageColors($sheet, 'K4', $mabcFemalePercent, 30, 50);
        $this->applyPercentageColors($sheet, 'L4', $mabcdFemalePercent, 50, 70);
        
        // Total performance percentages (row 6)
        $mabTotalPercent = $sheet->getCell("J6")->getValue();
        $mabcTotalPercent = $sheet->getCell("K6")->getValue();
        $mabcdTotalPercent = $sheet->getCell("L6")->getValue();
        
        $this->applyPercentageColors($sheet, 'J6', $mabTotalPercent, 20, 40);
        $this->applyPercentageColors($sheet, 'K6', $mabcTotalPercent, 30, 50);
        $this->applyPercentageColors($sheet, 'L6', $mabcdTotalPercent, 50, 70);
        
        // Add a title above the report
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells('A1:M1');
        $titleText = 'Grade Distribution By Gender Report';
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
        
        // Add term information
        $sheet->insertNewRowBefore(2, 1);
        $sheet->mergeCells('A2:M2');
        $termText = 'Term: ' . $this->data['currentTerm']->term . ' - Academic Year: ' . $this->data['currentTerm']->year;
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
        
        // Adjust the header row style
        $sheet->getStyle('A3:M3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '000000']
            ]
        ]);
        
        // Add % to percentage columns in data rows
        foreach ([4, 6, 8] as $row) {
            // Add % to MAB column
            $jValue = $sheet->getCell("J{$row}")->getValue();
            if (is_numeric($jValue)) {
                $sheet->setCellValue("J{$row}", $jValue . '%');
            }
            
            // Add % to MABC column
            $kValue = $sheet->getCell("K{$row}")->getValue();
            if (is_numeric($kValue)) {
                $sheet->setCellValue("K{$row}", $kValue . '%');
            }
            
            // Add % to MABCD column
            $lValue = $sheet->getCell("L{$row}")->getValue();
            if (is_numeric($lValue)) {
                $sheet->setCellValue("L{$row}", $lValue . '%');
            }
        }
        
        return [];
    }
    
    private function applyPercentageColors(Worksheet $sheet, $cellRef, $value, $mediumThreshold, $highThreshold){
        if ($value >= $highThreshold) {
            $sheet->getStyle($cellRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5f5d5');
        } elseif ($value >= $mediumThreshold) {
            $sheet->getStyle($cellRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffffd5');
        } else {
            $sheet->getStyle($cellRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffd5d5');
        }
    }
}