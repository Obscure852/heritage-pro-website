<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentPerformanceExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles{
    protected $tests;
    public function __construct(array $tests){
        $this->tests = $tests;
    }

    public function headings(): array{
        return [
            'Test Key',
            'Department',
            'Department Head',
            'Subjects',
            'A(M)', 'A(F)',
            'B(M)', 'B(F)',
            'C(M)', 'C(F)',
            'D(M)', 'D(F)',
            'E(M)', 'E(F)',
            'U(M)', 'U(F)',
            'AB%(M)', 'AB%(F)',
            'ABC%(M)', 'ABC%(F)',
            'ABCD%(M)', 'ABCD%(F)',
            'DEU%(M)', 'DEU%(F)',
            'Total Students',
        ];
    }

    public function array(): array{
        $rows = [];
        foreach ($this->tests as $testKey => $departments) {
            foreach ($departments as $department => $data) {
                $subjectsList = '';
                if (isset($data['subjects'])) {
                    $subjectsList = implode(", ", $data['subjects']);
                }

                $rows[] = [
                    $testKey,
                    $department,
                    $data['departmentHead'] ?? 'N/A',
                    $subjectsList,
                    $data['A']['M'] ?? 0, $data['A']['F'] ?? 0,
                    $data['B']['M'] ?? 0, $data['B']['F'] ?? 0,
                    $data['C']['M'] ?? 0, $data['C']['F'] ?? 0,
                    $data['D']['M'] ?? 0, $data['D']['F'] ?? 0,
                    $data['E']['M'] ?? 0, $data['E']['F'] ?? 0,
                    $data['U']['M'] ?? 0, $data['U']['F'] ?? 0,
                    number_format($data['AB%']['M'] ?? 0, 2) . '%', number_format($data['AB%']['F'] ?? 0, 2) . '%',
                    number_format($data['ABC%']['M'] ?? 0, 2) . '%', number_format($data['ABC%']['F'] ?? 0, 2) . '%',
                    number_format($data['ABCD%']['M'] ?? 0, 2) . '%', number_format($data['ABCD%']['F'] ?? 0, 2) . '%',
                    number_format($data['DEU%']['M'] ?? 0, 2) . '%', number_format($data['DEU%']['F'] ?? 0, 2) . '%',
                    $data['total_students'] ?? 0,
                ];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'D9E1F2']
            ],
            'alignment' => [
                'horizontal' => 'center',
            ]
        ]);

        $sheet->getStyle('A1:Z1')->getBorders()->getAllBorders()->setBorderStyle('thin');
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A2:Z' . $highestRow)->getAlignment()->setHorizontal('center');
        return [];
    }
}