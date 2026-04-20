<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassCreditsSummaryExport implements FromArray, WithStyles, WithTitle{
    protected array $summary;
    protected $test;
    protected int $rowCounter;

    public function __construct($summary, $test){
        $this->summary    = is_array($summary) ? $summary : [];
        $this->test       = $test;
        $this->rowCounter = 1;
    }

    public function array(): array{
        $rows = [];

        $gradeName = $this->test?->grade->name ?? 'Grade';
        if (($this->test?->type ?? '') === 'CA') {
            $when  = $this->test?->name ?? 'Month';
            $title = "{$gradeName} - End of {$when} Grade Credits Analysis";
        } else {
            $title = "{$gradeName} - End of Term Grade Credits Analysis";
        }

        $rows[] = [$title];
        $this->rowCounter++;

        $rows[] = [
            'Class',
            'Students',
            '>=6 A-C',
            '%',
            'Male %',
            'Female %',
            '>=5 A-C',
            '%',
            'Male %',
            'Female %',
        ];
        $this->rowCounter++;

        foreach ($this->summary as $data) {
            $students     = (int)($data['students'] ?? 0);
            $maleCount    = (int)($data['male_count'] ?? 0);
            $femaleCount  = (int)($data['female_count'] ?? 0);
            $gte6         = (int)($data['gte_6_credits'] ?? 0);
            $gte5         = (int)($data['gte_5_credits'] ?? 0);
            $maleGte6     = (int)($data['male_gte_6'] ?? 0);
            $femaleGte6   = (int)($data['female_gte_6'] ?? 0);
            $maleGte5     = (int)($data['male_gte_5'] ?? 0);
            $femaleGte5   = (int)($data['female_gte_5'] ?? 0);

            $pct6        = $students > 0 ? $gte6 / $students : 0.0;
            $pct6Male    = $maleCount > 0 ? $maleGte6 / $maleCount : 0.0;
            $pct6Female  = $femaleCount > 0 ? $femaleGte6 / $femaleCount : 0.0;

            $pct5        = $students > 0 ? $gte5 / $students : 0.0;
            $pct5Male    = $maleCount > 0 ? $maleGte5 / $maleCount : 0.0;
            $pct5Female  = $femaleCount > 0 ? $femaleGte5 / $femaleCount : 0.0;

            $rows[] = [
                (string)($data['name'] ?? 'N/A'),
                $students,
                $gte6,
                $pct6,
                $pct6Male,
                $pct6Female,
                $gte5,
                $pct5,
                $pct5Male,
                $pct5Female,
            ];
            $this->rowCounter++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $lastRow = $this->rowCounter;
        $lastCol = 'J';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal('left')->setVertical('center');
        $sheet->getRowDimension(1)->setRowHeight(20);

        $sheet->getStyle('A2:J2')->getFont()->setBold(true);
        $sheet->getStyle('A2:J2')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("A2:J{$lastRow}")->getBorders()->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(9);
        $sheet->getColumnDimension('E')->setWidth(11);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(9);
        $sheet->getColumnDimension('I')->setWidth(11);
        $sheet->getColumnDimension('J')->setWidth(12);

        if ($lastRow >= 3) {
            $sheet->getStyle("D3:F{$lastRow}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("H3:J{$lastRow}")->getNumberFormat()->setFormatCode('0.0%');
        }

        $sheet->getStyle("A3:A{$lastRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("B3:J{$lastRow}")->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->freezePane('A3');
        $sheet->setAutoFilter('');
    }

    public function title(): string{
        return 'Class Credits Summary';
    }
}
