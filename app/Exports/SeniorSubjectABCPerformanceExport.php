<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SeniorSubjectABCPerformanceExport implements FromArray, WithStyles, WithTitle, WithColumnWidths{
    protected array $subjectPerformance;
    protected $test;
    protected int $rowCounter;

    public function __construct(array $subjectPerformance, $test1){
        $this->subjectPerformance = $subjectPerformance;
        $this->test = $test1;
        $this->rowCounter = 1;
    }

    public function array(): array{
        $rows = [];

        $gradeName = $this->test->grade->name ?? 'Grade';
        if (($this->test->type ?? '') === 'CA') {
            $title = "{$gradeName} - End of " . (($this->test->name ?? null) ?: 'Month') . " Subjects Analysis";
        } else {
            $title = "{$gradeName} - End of Term Subjects Analysis";
        }

        $rows[] = [$title];
        $this->rowCounter++;

        $rows[] = [
            'Subject',
            'A*','','',
            'A','','',
            'B','','',
            'C','','',
            'ABC','','',
            'ABC%','','',
            'Students','','',
        ];
        $this->rowCounter++;

        $rows[] = [
            '',
            'M','F','T',
            'M','F','T',
            'M','F','T',
            'M','F','T',
            'M','F','T',
            'M','F','T',
            'M','F','T',
        ];
        $this->rowCounter++;

        foreach ($this->subjectPerformance as $subjectName => $counts) {
            $aS_m = (int)($counts['A*']['M'] ?? 0);
            $aS_f = (int)($counts['A*']['F'] ?? 0);
            $a_m  = (int)($counts['A']['M']  ?? 0);
            $a_f  = (int)($counts['A']['F']  ?? 0);
            $b_m  = (int)($counts['B']['M']  ?? 0);
            $b_f  = (int)($counts['B']['F']  ?? 0);
            $c_m  = (int)($counts['C']['M']  ?? 0);
            $c_f  = (int)($counts['C']['F']  ?? 0);
            $abc_m = (int)($counts['ABC']['M'] ?? 0);
            $abc_f = (int)($counts['ABC']['F'] ?? 0);
            $tot_m = (int)($counts['total']['M'] ?? 0);
            $tot_f = (int)($counts['total']['F'] ?? 0);

            $aS_t = $aS_m + $aS_f;
            $a_t  = $a_m + $a_f;
            $b_t  = $b_m + $b_f;
            $c_t  = $c_m + $c_f;
            $abc_t = $abc_m + $abc_f;
            $tot_t = $tot_m + $tot_f;

            $abcPctM = $this->toPercentFraction($counts['ABC%']['M'] ?? null, $tot_m ? ($abc_m / $tot_m) : 0);
            $abcPctF = $this->toPercentFraction($counts['ABC%']['F'] ?? null, $tot_f ? ($abc_f / $tot_f) : 0);
            $abcPctT = $tot_t ? ($abc_t / $tot_t) : 0;

            $rows[] = [
                $subjectName,
                $aS_m, $aS_f, $aS_t,
                $a_m,  $a_f,  $a_t,
                $b_m,  $b_f,  $b_t,
                $c_m,  $c_f,  $c_t,
                $abc_m, $abc_f, $abc_t,
                $abcPctM, $abcPctF, $abcPctT,
                $tot_m, $tot_f, $tot_t,
            ];
            $this->rowCounter++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet){
        $lastCol = 'V';
        $lastRow = $this->rowCounter;

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A1:{$lastCol}1")->getAlignment()->setHorizontal('left')->setVertical('center');
        $sheet->getRowDimension(1)->setRowHeight(20);

        $sheet->getStyle("A2:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("A2:{$lastCol}3")->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:D2');
        $sheet->mergeCells('E2:G2');
        $sheet->mergeCells('H2:J2');
        $sheet->mergeCells('K2:M2');
        $sheet->mergeCells('N2:P2');
        $sheet->mergeCells('Q2:S2');
        $sheet->mergeCells('T2:V2');

        $sheet->getStyle("A2:{$lastCol}{$lastRow}")
              ->applyFromArray([
                  'borders' => [
                      'allBorders' => [
                          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                      ],
                  ],
              ]);

        $sheet->getStyle("A4:A{$lastRow}")
              ->getAlignment()->setHorizontal('left')->setVertical('center');

        $sheet->getStyle("B4:{$lastCol}{$lastRow}")
              ->getAlignment()->setHorizontal('center')->setVertical('center');

        $sheet->freezePane('A4');
        $sheet->setAutoFilter("A3:{$lastCol}3");

        $sheet->getStyle("Q4:S{$lastRow}")
              ->getNumberFormat()->setFormatCode('0.0%');
    }

    public function columnWidths(): array{
        return [
            'A' => 26,
            'B' => 7, 'C' => 7, 'D' => 7,
            'E' => 7, 'F' => 7, 'G' => 7,
            'H' => 7, 'I' => 7, 'J' => 7,
            'K' => 7, 'L' => 7, 'M' => 7,
            'N' => 8, 'O' => 8, 'P' => 8,
            'Q' => 9, 'R' => 9, 'S' => 9,
            'T' => 9, 'U' => 9, 'V' => 9,
        ];
    }

    public function title(): string{
        return 'Subjects Analysis';
    }

    private function toPercentFraction($value, float $fallback): float{
        if ($value === null || $value === '') {
            return $fallback;
        }
        if (is_numeric($value)) {
            $num = (float)$value;
            return $num > 1 ? $num / 100 : $num;
        }
        $str = trim((string)$value);
        $str = rtrim($str, '%');
        if (is_numeric($str)) {
            $num = (float)$str;
            return $num > 1 ? $num / 100 : $num;
        }
        return $fallback;
    }
}
