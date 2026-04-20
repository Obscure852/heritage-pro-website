<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradePerformanceStreamExport implements FromArray, WithEvents, WithTitle, ShouldAutoSize, WithStyles, WithCustomStartCell{
    protected $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function title(): string{
        $grade = $this->data['grade']->name ?? 'Grade';
        $term  = $this->data['term']->term ?? '';
        $year  = $this->data['term']->year ?? '';
        $type  = ucfirst($this->data['type'] ?? '');

        return "{$grade} {$term} {$year} - {$type}";
    }

    public function startCell(): string{
        return 'A1';
    }

    public function array(): array{
        $data = [];
        $data = [];

        $grade = $this->data['grade']->name ?? 'Grade';
        $term  = $this->data['term']->term ?? '';
        $year  = $this->data['term']->year ?? '';
        $type  = ucfirst($this->data['type'] ?? '');

        $data[] = ["Marks for {$type}, {$grade} {$term} {$year}"];
        $data[] = [''];

        $headers = ['', '', 'Merit', 'A', 'B', 'C', 'D', 'E', 'U', 'MAB%', 'MABC%', 'MABCD%', 'Total'];
        $data[] = $headers;

        $data[] = [
            'Male',
            'Output',
            $this->data['m_M'],
            $this->data['a_M'],
            $this->data['b_M'],
            $this->data['c_M'],
            $this->data['d_M'],
            $this->data['e_M'],
            $this->data['u_M'],
            $this->data['mab_M_Percentage'],
            $this->data['mabc_M_Percentage'],
            $this->data['mabcd_M_Percentage'],
            $this->data['maleCount']
        ];

        $data[] = [
            '',
            'PSLE',
            '',
            $this->data['psleA_M'],
            $this->data['psleB_M'],
            $this->data['psleC_M'],
            $this->data['psleD_M'],
            $this->data['psleE_M'],
            $this->data['psleU_M'],
            $this->data['psleAB_M_Percentage'],
            $this->data['psleABC_M_Percentage'],
            $this->data['psleABCD_M_Percentage'],
            $this->data['psleTotalM']
        ];

        $data[] = [
            'Female',
            'Output',
            $this->data['m_F'],
            $this->data['a_F'],
            $this->data['b_F'],
            $this->data['c_F'],
            $this->data['d_F'],
            $this->data['e_F'],
            $this->data['u_F'],
            $this->data['mab_F_Percentage'],
            $this->data['mabc_F_Percentage'],
            $this->data['mabcd_F_Percentage'],
            $this->data['femaleCount']
        ];

        $data[] = [
            '',
            'PSLE',
            '',
            $this->data['psleA_F'],
            $this->data['psleB_F'],
            $this->data['psleC_F'],
            $this->data['psleD_F'],
            $this->data['psleE_F'],
            $this->data['psleU_F'],
            $this->data['psleAB_F_Percentage'],
            $this->data['psleABC_F_Percentage'],
            $this->data['psleABCD_F_Percentage'],
            $this->data['psleTotalF']
        ];

        $data[] = [
            'Total',
            'Output',
            $this->data['sumM'],
            $this->data['sumA'],
            $this->data['sumB'],
            $this->data['sumC'],
            $this->data['sumD'],
            $this->data['sumE'],
            $this->data['sumU'],
            $this->data['mab_T_percentage'],
            $this->data['mabc_T_percentage'],
            $this->data['mabcd_T_percentage'],
            $this->data['totalStudents']
        ];

        $data[] = [
            '',
            'PSLE',
            '',
            $this->data['psleA_M'] + $this->data['psleA_F'],
            $this->data['psleB_M'] + $this->data['psleB_F'],
            $this->data['psleC_M'] + $this->data['psleC_F'],
            $this->data['psleD_M'] + $this->data['psleD_F'],
            $this->data['psleE_M'] + $this->data['psleE_F'],
            $this->data['psleU_M'] + $this->data['psleU_F'],
            $this->data['psleAB_T_Percentage'],
            $this->data['psleABC_T_Percentage'],
            $this->data['psleABCD_T_Percentage'],
            $this->data['totalPsleStudents']
        ];

        return $data;
    }

    public function styles(Worksheet $sheet): array{
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:M1');
                $sheet->mergeCells('A3:B3');
            },
        ];
    }
}
