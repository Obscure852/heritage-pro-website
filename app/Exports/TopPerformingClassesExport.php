<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopPerformingClassesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['classes']);
    }

    public function headings(): array
    {
        return [
            'Class Name',
            'Teacher',
            'Grade',
            'Total Students',
            
            // Merit grades
            'Merit (M)', 'Merit (F)', 'Merit (T)',
            
            // A grades
            'A (M)', 'A (F)', 'A (T)',
            
            // B grades
            'B (M)', 'B (F)', 'B (T)',
            
            // C grades
            'C (M)', 'C (F)', 'C (T)',
            
            // D grades
            'D (M)', 'D (F)', 'D (T)',
            
            // E grades
            'E (M)', 'E (F)', 'E (T)',
            
            // U grades
            'U (M)', 'U (F)', 'U (T)',
            
            // Percentage categories - Fixed to match controller
            'MAB% (M)', 'MAB% (F)', 'MAB% (T)',
            'ABC% (M)', 'ABC% (F)', 'ABC% (T)',
            'DEU% (M)', 'DEU% (F)', 'DEU% (T)',
        ];
    }

    public function map($class): array
    {
        return [
            $class['name'],
            $class['teacher'],
            $class['grade_name'] ?? 'N/A', // Added null coalescing since this might be missing
            $class['total_with_results'],
            
            // Merit grades
            $class['grade_analysis']['Merit']['M'],
            $class['grade_analysis']['Merit']['F'],
            $class['grade_analysis']['Merit']['T'],
            
            // A grades
            $class['grade_analysis']['A']['M'],
            $class['grade_analysis']['A']['F'],
            $class['grade_analysis']['A']['T'],
            
            // B grades
            $class['grade_analysis']['B']['M'],
            $class['grade_analysis']['B']['F'],
            $class['grade_analysis']['B']['T'],
            
            // C grades
            $class['grade_analysis']['C']['M'],
            $class['grade_analysis']['C']['F'],
            $class['grade_analysis']['C']['T'],
            
            // D grades
            $class['grade_analysis']['D']['M'],
            $class['grade_analysis']['D']['F'],
            $class['grade_analysis']['D']['T'],
            
            // E grades
            $class['grade_analysis']['E']['M'],
            $class['grade_analysis']['E']['F'],
            $class['grade_analysis']['E']['T'],
            
            // U grades
            $class['grade_analysis']['U']['M'],
            $class['grade_analysis']['U']['F'],
            $class['grade_analysis']['U']['T'],
            
            // Percentage categories - Fixed to match controller structure
            $class['percentage_analysis']['MAB']['M'] . '%',
            $class['percentage_analysis']['MAB']['F'] . '%',
            $class['percentage_analysis']['MAB']['T'] . '%',
            $class['percentage_analysis']['ABC']['M'] . '%',
            $class['percentage_analysis']['ABC']['F'] . '%',
            $class['percentage_analysis']['ABC']['T'] . '%',
            $class['percentage_analysis']['DEU']['M'] . '%',
            $class['percentage_analysis']['DEU']['F'] . '%',
            $class['percentage_analysis']['DEU']['T'] . '%',
        ];
    }

    public function title(): string
    {
        return 'Top Performing Classes ' . ($this->data['year'] ?? 'All Years');
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E2E8F0']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // Auto-fit columns
            'A:AE' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]
        ];
    }
}