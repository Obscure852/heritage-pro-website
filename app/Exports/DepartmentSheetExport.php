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

class DepartmentSheetExport implements FromArray, WithTitle, WithColumnWidths, WithStyles, WithEvents{
    protected string $deptName;
    protected array $deptData;
    protected $term;
    protected string $type;
    protected string $title;

    public function __construct($deptName, $deptData, $term, $type, $title){
        $this->deptName = $deptName;
        $this->deptData = $deptData;
        $this->term     = $term;
        $this->type     = $type;
        $this->title    = $title;
    }

    public function array(): array{
        $rows = [];

        $rows[] = [ $this->title . ' — ' . $this->deptName . ' Department' ];

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

        $rows[] = $hdr1;
        $rows[] = $hdr2;

        $sub = array_fill_keys(['A', 'B', 'C', 'D', 'E', 'U'], ['M' => 0, 'F' => 0, 'T' => 0]);
        $sum_ab = $sum_abc = $sum_abcd = $grandTotal = $subjectCount = 0;

        foreach ($this->deptData['subjects'] as $subject => $r) {
            $line = [$subject];
            $subjectTotal = 0;

            foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g) {
                $m = $r['grades'][$g]['M'] ?? 0;
                $f = $r['grades'][$g]['F'] ?? 0;
                $t = $m + $f;

                $line[] = $m; $line[] = $f; $line[] = $t;

                $sub[$g]['M'] += $m;
                $sub[$g]['F'] += $f;
                $sub[$g]['T'] += $t;

                $subjectTotal += $t;
            }

            $line[] = ($r['ab_percent']   ?? 0) . '%';
            $line[] = ($r['abc_percent']  ?? 0) . '%';
            $line[] = ($r['abcd_percent'] ?? 0) . '%';
            $line[] = $subjectTotal;

            $rows[] = $line;

            $sum_ab   += ($r['ab_percent']   ?? 0);
            $sum_abc  += ($r['abc_percent']  ?? 0);
            $sum_abcd += ($r['abcd_percent'] ?? 0);
            $grandTotal += $subjectTotal;
            $subjectCount++;
        }

        $line = ['Subtotal'];
        foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $g) {
            $line[] = $sub[$g]['M'];
            $line[] = $sub[$g]['F'];
            $line[] = $sub[$g]['T'];
        }
        $line[] = $subjectCount ? round($sum_ab   / $subjectCount)  . '%' : '0%';
        $line[] = $subjectCount ? round($sum_abc  / $subjectCount)  . '%' : '0%';
        $line[] = $subjectCount ? round($sum_abcd / $subjectCount)  . '%' : '0%';
        $line[] = $grandTotal;

        $rows[] = $line;

        return $rows;
    }

    public function title(): string{
        return mb_strimwidth($this->deptName, 0, 31, "");
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
                'font' => ['name' => 'Calibri', 'size' => 12, 'bold' => true],
            ],
        ];
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function (AfterSheet $ev) {
                $sh     = $ev->sheet->getDelegate();
                $maxRow = $sh->getHighestRow();
                $maxCol = $sh->getHighestColumn();

                $sh->mergeCells("A1:{$maxCol}1");
                $sh->getStyle("A1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sh->mergeCells("A2:A3");
                $gradeBands = [
                    ['start' => 'B', 'end' => 'D'], // A
                    ['start' => 'E', 'end' => 'G'], // B
                    ['start' => 'H', 'end' => 'J'], // C
                    ['start' => 'K', 'end' => 'M'], // D
                    ['start' => 'N', 'end' => 'P'], // E
                    ['start' => 'Q', 'end' => 'S'], // U
                ];
                foreach ($gradeBands as $band) {
                    $sh->mergeCells("{$band['start']}2:{$band['end']}2");
                }

                foreach (['T','U','V','W'] as $col) {
                    $sh->mergeCells("{$col}2:{$col}3");
                }

                $sh->getStyle("A2:{$maxCol}2")->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sh->getStyle("A3:{$maxCol}3")->applyFromArray([
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sh->getStyle("A2:{$maxCol}{$maxRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'CED4DA'],
                        ],
                    ],
                ]);

                $tColumns = ['D','G','J','M','P','S'];
                for ($r = 4; $r <= $maxRow; $r++) {
                    if ($sh->getCell("A{$r}")->getValue() === 'Subtotal') {
                        $sh->getStyle("A{$r}:{$maxCol}{$r}")
                            ->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFEFEF']],
                            ]);
                        continue;
                    }

                    if ($r % 2 === 0) {
                        $sh->getStyle("A{$r}:{$maxCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F7FAFC');
                    }

                    foreach ($tColumns as $col) {
                        $sh->getStyle("{$col}{$r}")
                            ->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F3F3']],
                                'font' => ['bold' => true],
                            ]);
                    }

                    $sh->getStyle("W{$r}")->getFont()->setBold(true);
                }

                $sh->getStyle("B2:{$maxCol}{$maxRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sh->getStyle("A1:A{$maxRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

                $sh->getStyle("A2:{$maxCol}{$maxRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

                $sh->freezePane('B4');
            },
        ];
    }
}
