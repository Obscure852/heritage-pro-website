<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class OverallTeacherPerformanceExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $teacherPerformance;
    protected $totals;
    protected $type;
    protected $sequence;
    protected $grade;

    public function __construct($teacherPerformance, $totals, $type = '', $sequence = '', $grade = '')
    {
        $this->teacherPerformance = $teacherPerformance;
        $this->totals = $totals;
        $this->type = $type;
        $this->sequence = $sequence;
        $this->grade = $grade;
    }

    public function collection()
    {
        $data = collect();
        
        // Add teacher performance data
        foreach ($this->teacherPerformance as $index => $teacher) {
            $totalStudents = $teacher['totalMale'] + $teacher['totalFemale'];
            
            $row = [
                $teacher['teacher_name'],
                
                // Grade A
                $teacher['grades']['A']['M'],
                $teacher['grades']['A']['F'],
                $teacher['grades']['A']['M'] + $teacher['grades']['A']['F'],
                
                // Grade B
                $teacher['grades']['B']['M'],
                $teacher['grades']['B']['F'],
                $teacher['grades']['B']['M'] + $teacher['grades']['B']['F'],
                
                // Grade C
                $teacher['grades']['C']['M'],
                $teacher['grades']['C']['F'],
                $teacher['grades']['C']['M'] + $teacher['grades']['C']['F'],
                
                // Grade D
                $teacher['grades']['D']['M'],
                $teacher['grades']['D']['F'],
                $teacher['grades']['D']['M'] + $teacher['grades']['D']['F'],
                
                // Grade E
                $teacher['grades']['E']['M'],
                $teacher['grades']['E']['F'],
                $teacher['grades']['E']['M'] + $teacher['grades']['E']['F'],
                
                // Grade U
                $teacher['grades']['U']['M'],
                $teacher['grades']['U']['F'],
                $teacher['grades']['U']['M'] + $teacher['grades']['U']['F'],
                
                // Total
                $teacher['totalMale'],
                $teacher['totalFemale'],
                $totalStudents,
                
                // AB%
                $teacher['AB%']['M'] . '%',
                $teacher['AB%']['F'] . '%',
                $totalStudents > 0 ? round(($teacher['grades']['A']['M'] + $teacher['grades']['A']['F'] + $teacher['grades']['B']['M'] + $teacher['grades']['B']['F']) / $totalStudents * 100, 1) . '%' : '0%',
                
                // ABC%
                $teacher['ABC%']['M'] . '%',
                $teacher['ABC%']['F'] . '%',
                $totalStudents > 0 ? round(($teacher['grades']['A']['M'] + $teacher['grades']['A']['F'] + $teacher['grades']['B']['M'] + $teacher['grades']['B']['F'] + $teacher['grades']['C']['M'] + $teacher['grades']['C']['F']) / $totalStudents * 100, 1) . '%' : '0%',
                
                // ABCD%
                $teacher['ABCD%']['M'] . '%',
                $teacher['ABCD%']['F'] . '%',
                $totalStudents > 0 ? round(($teacher['grades']['A']['M'] + $teacher['grades']['A']['F'] + $teacher['grades']['B']['M'] + $teacher['grades']['B']['F'] + $teacher['grades']['C']['M'] + $teacher['grades']['C']['F'] + $teacher['grades']['D']['M'] + $teacher['grades']['D']['F']) / $totalStudents * 100, 1) . '%' : '0%',
            ];
            
            $data->push($row);
        }
        
        // Add totals row
        if (!empty($this->totals)) {
            $totalStudents = $this->totals['totalMale'] + $this->totals['totalFemale'];
            
            $totalsRow = [
                'TOTALS',
                
                // Grade A
                $this->totals['grades']['A']['M'],
                $this->totals['grades']['A']['F'],
                $this->totals['grades']['A']['M'] + $this->totals['grades']['A']['F'],
                
                // Grade B
                $this->totals['grades']['B']['M'],
                $this->totals['grades']['B']['F'],
                $this->totals['grades']['B']['M'] + $this->totals['grades']['B']['F'],
                
                // Grade C
                $this->totals['grades']['C']['M'],
                $this->totals['grades']['C']['F'],
                $this->totals['grades']['C']['M'] + $this->totals['grades']['C']['F'],
                
                // Grade D
                $this->totals['grades']['D']['M'],
                $this->totals['grades']['D']['F'],
                $this->totals['grades']['D']['M'] + $this->totals['grades']['D']['F'],
                
                // Grade E
                $this->totals['grades']['E']['M'],
                $this->totals['grades']['E']['F'],
                $this->totals['grades']['E']['M'] + $this->totals['grades']['E']['F'],
                
                // Grade U
                $this->totals['grades']['U']['M'],
                $this->totals['grades']['U']['F'],
                $this->totals['grades']['U']['M'] + $this->totals['grades']['U']['F'],
                
                // Total
                $this->totals['totalMale'],
                $this->totals['totalFemale'],
                $totalStudents,
                
                // AB%
                $this->totals['AB%']['M'] . '%',
                $this->totals['AB%']['F'] . '%',
                $this->totals['AB%']['T'] . '%',
                
                // ABC%
                $this->totals['ABC%']['M'] . '%',
                $this->totals['ABC%']['F'] . '%',
                $this->totals['ABC%']['T'] . '%',
                
                // ABCD%
                $this->totals['ABCD%']['M'] . '%',
                $this->totals['ABCD%']['F'] . '%',
                $this->totals['ABCD%']['T'] . '%',
            ];
            
            $data->push($totalsRow);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            // Main headers row 1
            ['Teacher', 'A', '', '', 'B', '', '', 'C', '', '', 'D', '', '', 'E', '', '', 'U', '', '', 'TOT', '', '', 'AB%', '', '', 'ABC%', '', '', 'ABCD%', '', ''],
            // Sub headers row 2
            ['', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T', 'M', 'F', 'T']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Teacher name
            'B' => 8, 'C' => 8, 'D' => 8, // A grades M,F,T
            'E' => 8, 'F' => 8, 'G' => 8, // B grades M,F,T
            'H' => 8, 'I' => 8, 'J' => 8, // C grades M,F,T
            'K' => 8, 'L' => 8, 'M' => 8, // D grades M,F,T
            'N' => 8, 'O' => 8, 'P' => 8, // E grades M,F,T
            'Q' => 8, 'R' => 8, 'S' => 8, // U grades M,F,T
            'T' => 8, 'U' => 8, 'V' => 8, // TOT M,F,T
            'W' => 10, 'X' => 10, 'Y' => 10, // AB% M,F,T
            'Z' => 10, 'AA' => 10, 'AB' => 10, // ABC% M,F,T
            'AC' => 10, 'AD' => 10, 'AE' => 10, // ABCD% M,F,T
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'B4C6E7']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
            // Teacher name column alignment
            'A:A' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'font' => ['bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $teacherCount = count($this->teacherPerformance);
                $dataStartRow = 3; // Data starts from row 3 (after 2 header rows)
                $dataEndRow = $dataStartRow + $teacherCount - 1;
                $totalsRow = $dataEndRow + 1;
                
                // Add title at the top
                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', "Overall Teacher Performance Report - {$this->grade} - {$this->type} - Sequence {$this->sequence}");
                $sheet->mergeCells('A1:AE1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '000000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E7E6E6']],
                ]);
                
                // Adjust row references after inserting title rows
                $headerRow1 = 4;
                $headerRow2 = 5;
                $dataStartRow = 6;
                $dataEndRow = $dataStartRow + $teacherCount - 1;
                $totalsRow = $dataEndRow + 1;
                
                // Merge header cells for better appearance
                $mergeRanges = [
                    'B4:D4' => 'A', 'E4:G4' => 'B', 'H4:J4' => 'C', 'K4:M4' => 'D',
                    'N4:P4' => 'E', 'Q4:S4' => 'U', 'T4:V4' => 'TOT',
                    'W4:Y4' => 'AB%', 'Z4:AB4' => 'ABC%', 'AC4:AE4' => 'ABCD%'
                ];
                
                foreach ($mergeRanges as $range => $value) {
                    $sheet->mergeCells($range);
                    $startCell = explode(':', $range)[0];
                    $sheet->setCellValue($startCell, $value);
                }
                
                // Style data rows
                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    $sheet->getStyle("A{$row}:AE{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                
                // Highlight best teacher (first row - green)
                if ($teacherCount > 0) {
                    $bestTeacherRow = $dataStartRow;
                    $sheet->getStyle("A{$bestTeacherRow}:AE{$bestTeacherRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C6EFCE']], // Light green
                        'font' => ['bold' => true],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    ]);
                }
                
                // Highlight worst teacher (last row - red)
                if ($teacherCount > 1) {
                    $worstTeacherRow = $dataEndRow;
                    $sheet->getStyle("A{$worstTeacherRow}:AE{$worstTeacherRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFC7CE']], // Light red
                        'font' => ['bold' => true],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    ]);
                }
                
                // Style totals row
                if (!empty($this->totals)) {
                    $sheet->getStyle("A{$totalsRow}:AE{$totalsRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'D9D9D9']], // Gray
                        'font' => ['bold' => true, 'size' => 11],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                
                // Add legend
                $legendRow = $totalsRow + 3;
                $sheet->setCellValue("A{$legendRow}", "Legend:");
                $sheet->getStyle("A{$legendRow}")->getFont()->setBold(true);
                
                $sheet->setCellValue("A" . ($legendRow + 1), "Best Performing Teacher");
                $sheet->getStyle("A" . ($legendRow + 1) . ":C" . ($legendRow + 1))->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C6EFCE']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                
                $sheet->setCellValue("A" . ($legendRow + 2), "Lowest Performing Teacher");
                $sheet->getStyle("A" . ($legendRow + 2) . ":C" . ($legendRow + 2))->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFC7CE']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                
                for ($row = 1; $row <= $totalsRow + 5; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}