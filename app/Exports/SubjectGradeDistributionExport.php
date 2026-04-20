<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SubjectGradeDistributionExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents{
    protected $subjectsData;
    protected $klass;
    protected $type;
    protected $sequence;
    protected $schoolName;

    public function __construct($subjectsData, $klass, $type, $sequence, $schoolName = ''){
        $this->subjectsData = $subjectsData;
        $this->klass = $klass;
        $this->type = $type;
        $this->sequence = $sequence;
        $this->schoolName = $schoolName;
    }

    public function collection(){
        $data = collect([
            [
                $this->schoolName,
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ],
            [
                "Class: {$this->klass->name} | Test Type: {$this->type} | Sequence: {$this->sequence}",
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ],
            [
                "Date Generated: " . now()->format('d M Y'),
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ],
            []
        ]);

        foreach ($this->subjectsData as $subject) {
            $data->push([
                'Subject' => $subject['subject'],
                'Teacher' => $subject['teacher'],
                // A grade
                'A_M' => $subject['grades']['A']['M'],
                'A_F' => $subject['grades']['A']['F'],
                'A_T' => $subject['grades']['A']['T'],
                // B grade
                'B_M' => $subject['grades']['B']['M'],
                'B_F' => $subject['grades']['B']['F'],
                'B_T' => $subject['grades']['B']['T'],
                // C grade
                'C_M' => $subject['grades']['C']['M'],
                'C_F' => $subject['grades']['C']['F'],
                'C_T' => $subject['grades']['C']['T'],
                // D grade
                'D_M' => $subject['grades']['D']['M'],
                'D_F' => $subject['grades']['D']['F'],
                'D_T' => $subject['grades']['D']['T'],
                // E grade
                'E_M' => $subject['grades']['E']['M'],
                'E_F' => $subject['grades']['E']['F'],
                'E_T' => $subject['grades']['E']['T'],
                // U grade
                'U_M' => $subject['grades']['U']['M'],
                'U_F' => $subject['grades']['U']['F'],
                'U_T' => $subject['grades']['U']['T'],
                // Total enrolled
                'Enrolled_M' => $subject['total_enrolled']['M'],
                'Enrolled_F' => $subject['total_enrolled']['F'],
                'Enrolled_T' => $subject['total_enrolled']['T'],
                // No scores
                'NoScores_M' => $subject['no_scores']['M'],
                'NoScores_F' => $subject['no_scores']['F'],
                'NoScores_T' => $subject['no_scores']['T'],
                // AB%
                'AB%_M' => $subject['percentages']['AB']['M'],
                'AB%_F' => $subject['percentages']['AB']['F'],
                'AB%_T' => $subject['percentages']['AB']['T'],
                // ABC%
                'ABC%_M' => $subject['percentages']['ABC']['M'],
                'ABC%_F' => $subject['percentages']['ABC']['F'],
                'ABC%_T' => $subject['percentages']['ABC']['T'],
                // DEU%
                'DEU%_M' => $subject['percentages']['DEU']['M'],
                'DEU%_F' => $subject['percentages']['DEU']['F'],
                'DEU%_T' => $subject['percentages']['DEU']['T'],
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Subject',
            'Teacher',
            // A grade
            'A (M)', 'A (F)', 'A (T)',
            // B grade
            'B (M)', 'B (F)', 'B (T)',
            // C grade
            'C (M)', 'C (F)', 'C (T)',
            // D grade
            'D (M)', 'D (F)', 'D (T)',
            // E grade
            'E (M)', 'E (F)', 'E (T)',
            // U grade
            'U (M)', 'U (F)', 'U (T)',
            // Total enrolled
            'Total (M)', 'Total (F)', 'Total (T)',
            // No scores
            'No Scores (M)', 'No Scores (F)', 'No Scores (T)',
            // AB%
            'AB% (M)', 'AB% (F)', 'AB% (T)',
            // ABC%
            'ABC% (M)', 'ABC% (F)', 'ABC% (T)',
            // DEU%
            'DEU% (M)', 'DEU% (F)', 'DEU% (T)',
        ];
    }

    public function title(): string
    {
        return "Subject Analysis";
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = 'AI'; // Column AI corresponds to the last column (DEU% T)
        $totalRows = count($this->subjectsData) + 5; // +5 for title rows and header row
        $dataStartRow = 5; // Data starts after title section

        // Title styles
        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->mergeCells('A3:' . $lastColumn . '3');
        
        $sheet->getStyle('A1:' . $lastColumn . '3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style for the header row
        $sheet->getStyle('A' . $dataStartRow . ':' . $lastColumn . $dataStartRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Row height for the header
        $sheet->getRowDimension($dataStartRow)->setRowHeight(30);

        // Style for all data cells
        $sheet->getStyle('A' . ($dataStartRow+1) . ':' . $lastColumn . $totalRows)->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Subject and Teacher columns left-aligned
        $sheet->getStyle('A' . ($dataStartRow+1) . ':B' . $totalRows)->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Color-code grade columns
        $gradeSections = [
            // A grade columns
            'A' => ['range' => 'C' . $dataStartRow . ':E' . $totalRows, 'color' => 'A9D08E'],
            // B grade columns
            'B' => ['range' => 'F' . $dataStartRow . ':H' . $totalRows, 'color' => '5B9BD5'],
            // C grade columns
            'C' => ['range' => 'I' . $dataStartRow . ':K' . $totalRows, 'color' => 'FFD966'],
            // D grade columns
            'D' => ['range' => 'L' . $dataStartRow . ':N' . $totalRows, 'color' => 'F4B084'],
            // E grade columns
            'E' => ['range' => 'O' . $dataStartRow . ':Q' . $totalRows, 'color' => 'ED7D31'],
            // U grade columns
            'U' => ['range' => 'R' . $dataStartRow . ':T' . $totalRows, 'color' => 'A5A5A5'],
            // Total enrolled columns
            'Total' => ['range' => 'U' . $dataStartRow . ':W' . $totalRows, 'color' => 'D9E1F2'],
            // No scores columns
            'NoScores' => ['range' => 'X' . $dataStartRow . ':Z' . $totalRows, 'color' => 'E7E6E6'],
            // AB% columns
            'AB%' => ['range' => 'AA' . $dataStartRow . ':AC' . $totalRows, 'color' => 'BDD7EE'],
            // ABC% columns
            'ABC%' => ['range' => 'AD' . $dataStartRow . ':AF' . $totalRows, 'color' => 'C6E0B4'],
            // DEU% columns
            'DEU%' => ['range' => 'AG' . $dataStartRow . ':AI' . $totalRows, 'color' => 'F8CBAD'],
        ];

        foreach ($gradeSections as $section) {
            $sheet->getStyle($section['range'])->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $section['color']],
                ],
            ]);
        }

        // Apply percentage formatting to percentage columns
        $sheet->getStyle('AA' . ($dataStartRow+1) . ':AI' . $totalRows)->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);

        // Add alternate row coloring for better readability
        for ($row = $dataStartRow + 1; $row <= $totalRows; $row += 2) {
            $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9F9F9'],
                ],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Subject
            'B' => 25, // Teacher
            // A grade
            'C' => 10, 'D' => 10, 'E' => 10,
            // B grade
            'F' => 10, 'G' => 10, 'H' => 10,
            // C grade
            'I' => 10, 'J' => 10, 'K' => 10,
            // D grade
            'L' => 10, 'M' => 10, 'N' => 10,
            // E grade
            'O' => 10, 'P' => 10, 'Q' => 10,
            // U grade
            'R' => 10, 'S' => 10, 'T' => 10,
            // Total enrolled
            'U' => 12, 'V' => 12, 'W' => 12,
            // No scores
            'X' => 12, 'Y' => 12, 'Z' => 12,
            // AB%
            'AA' => 12, 'AB' => 12, 'AC' => 12,
            // ABC%
            'AD' => 12, 'AE' => 12, 'AF' => 12,
            // DEU%
            'AG' => 12, 'AH' => 12, 'AI' => 12,
        ];
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $dataStartRow = 5;
                
                $event->sheet->getDelegate()->freezePane('C' . ($dataStartRow + 1));
                
                $lastColumn = 'AI';
                $totalRows = count($this->subjectsData) + 5;
                $event->sheet->getPageSetup()->setPrintArea('A1:' . $lastColumn . $totalRows);
                $event->sheet->getPageSetup()->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                );
                
                $event->sheet->getPageSetup()->setFitToWidth(1);
                $event->sheet->getPageSetup()->setFitToHeight(0);
                $headers = [
                    'C4' => 'A', 'F4' => 'B', 'I4' => 'C', 'L4' => 'D', 'O4' => 'E', 'R4' => 'U',
                    'U4' => 'Total Enrolled', 'X4' => 'No Scores', 
                    'AA4' => 'AB%', 'AD4' => 'ABC%', 'AG4' => 'DEU%'
                ];
                
                foreach ($headers as $cell => $value) {
                    $event->sheet->setCellValue($cell, $value);
                }
                
                $event->sheet->getStyle('C4:AI4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                $mergeCells = [
                    'C4:E4', 'F4:H4', 'I4:K4', 'L4:N4', 'O4:Q4', 'R4:T4',
                    'U4:W4', 'X4:Z4', 'AA4:AC4', 'AD4:AF4', 'AG4:AI4'
                ];
                
                foreach ($mergeCells as $range) {
                    $event->sheet->mergeCells($range);
                }
            },
        ];
    }
}