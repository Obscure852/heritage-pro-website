<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class DepartmentOverallSheetExport implements FromArray, WithTitle, WithColumnWidths, WithStyles, WithEvents{
    protected array $totals;
    protected $term;
    protected $type;

    public function __construct($totals, $term, $type){
        $this->totals = $totals;
        $this->term = $term;
        $this->type = $type;
    }

    public function array(): array{
        $hdr1 = ['Subject'];
        foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g) {
            $hdr1[] = $g; $hdr1[] = ''; $hdr1[] = '';
        }
        $hdr1[] = 'AB%'; $hdr1[] = 'ABC%'; $hdr1[] = 'ABCD%'; $hdr1[] = 'Total';

        $hdr2 = [''];
        foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g) {
            $hdr2[] = 'M'; $hdr2[] = 'F'; $hdr2[] = 'T';
        }
        $hdr2[] = ''; $hdr2[] = ''; $hdr2[] = ''; $hdr2[] = '';

        $rows = [];
        $rows[] = [ 'Overall Totals (All Departments) - ' . $this->term->term . ' ' . $this->term->year . ' (' . ucfirst($this->type) . ')' ];
        $rows[] = $hdr1;
        $rows[] = $hdr2;

        $final = ['Total'];
        $overallTotal = 0;
        foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g) {
            $maleCount = $this->totals['gender'][$g]['M'] ?? 0;
            $femaleCount = $this->totals['gender'][$g]['F'] ?? 0;
            $totalCount = $maleCount + $femaleCount;
            $final[] = $maleCount;
            $final[] = $femaleCount;
            $final[] = $totalCount;
            $overallTotal += $totalCount;
        }
        $final[] = ($this->totals['ab_percent'] ?? 0) . '%';
        $final[] = ($this->totals['abc_percent'] ?? 0) . '%';
        $final[] = ($this->totals['abcd_percent'] ?? 0) . '%';
        $final[] = $overallTotal;
        $rows[] = $final;

        return $rows;
    }

    public function title(): string{
        return "Overall Totals";
    }

    public function columnWidths(): array{
        return [
            'A' => 30,
            'B' => 7,  'C' => 7,  'D' => 7,
            'E' => 7,  'F' => 7,  'G' => 7,
            'H' => 7,  'I' => 7,  'J' => 7,
            'K' => 7,  'L' => 7,  'M' => 7,
            'N' => 7,  'O' => 7,  'P' => 7,
            'Q' => 7,  'R' => 7,  'S' => 7,
            'T' => 9,  'U' => 9,  'V' => 9,
            'W' => 10,
        ];
    }

    public function styles(Worksheet $sheet): array{
        return [
            1 => [
                'font' => ['name' => 'Calibri', 'size' => 11],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CED4DA'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function (AfterSheet $ev) {
                $sh = $ev->sheet->getDelegate();
                $max = $sh->getHighestRow();
                $maxCol = $sh->getHighestColumn();

                if ($max >= 1) {
                    $sh->mergeCells("A1:{$maxCol}1");
                    $sh->getStyle("A1")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']]
                    ]);
                }
                if ($max >= 3) {
                    $sh->mergeCells("A2:A3");
                    $gradeColumns = [
                        'B' => 'D', 'E' => 'G', 'H' => 'J',
                        'K' => 'M', 'N' => 'P', 'Q' => 'S'
                    ];
                    foreach ($gradeColumns as $start => $end) {
                        $sh->mergeCells("{$start}2:{$end}2");
                    }
                    $sh->mergeCells("T2:T3");
                    $sh->mergeCells("U2:U3");
                    $sh->mergeCells("V2:V3");
                    $sh->mergeCells("W2:W3");
                    $sh->getStyle("A2:{$maxCol}2")
                        ->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    $sh->getStyle("A3:{$maxCol}3")
                        ->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                }
                $totalRow = $max;
                $sh->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")
                    ->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFEFEF']],
                    ]);
                $sh->getStyle("B1:{$maxCol}{$max}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sh->getStyle("A1:A{$max}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sh->getStyle("A1:{$maxCol}{$max}")
                    ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
                $sh->freezePane('B4');
            },
        ];
    }
}
