<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ClassPerformanceExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithEvents,
    WithTitle,
    WithColumnWidths
{


    public function __construct(
        protected array  $classPerformance,
        protected array  $overallTotals,
        protected string $type = 'CA'
    ) {}


    public function collection(): Collection{
        $rows = [];
        foreach ($this->classPerformance as $className => $perf) {
            $rows[] = $this->buildRow($className, $perf);
        }

        $rows[] = $this->buildRow('TOTALS', $this->overallTotals, true);

        return collect($rows);
    }

    private function buildRow(string $label, array $d, bool $isTotal = false): array{
        $row = [$label];

        foreach (['Merit','A','B','C','D','E','U'] as $g) {
            $maleCount = $d['grades'][$g]['M'] ?? 0;
            $femaleCount = $d['grades'][$g]['F'] ?? 0;
            $row[] = $maleCount;
            $row[] = $femaleCount;
            $row[] = $maleCount + $femaleCount;
        }

        foreach (['MAB%','MABC%','MABCD%','DEU%'] as $p) {
            $malePercent = $d[$p]['M'] ?? 0;
            $femalePercent = $d[$p]['F'] ?? 0;
            $row[] = $malePercent.'%';
            $row[] = $femalePercent.'%';

            $totalMale = $d['totalMale'] ?? 0;
            $totalFemale = $d['totalFemale'] ?? 0;
            $den = max($totalMale + $totalFemale, 1);
            $tot = round(
                ($malePercent * $totalMale + $femalePercent * $totalFemale) / $den,
                2
            );
            $row[] = $tot.'%';
        }

        $totalMale = $d['totalMale'] ?? 0;
        $totalFemale = $d['totalFemale'] ?? 0;
        $row[] = $totalMale;
        $row[] = $totalFemale;
        $row[] = $totalMale + $totalFemale;

        return $row;
    }


    public function headings(): array{
        $r1 = ['Class'];
        foreach (['Merit','A','B','C','D','E','U'] as $g) { $r1 = array_merge($r1, array_fill(0,3,$g)); }
        foreach (['MAB%','MABC%','MABCD%','DEU%'] as $p) { $r1 = array_merge($r1, array_fill(0,3,$p)); }
        $r1 = array_merge($r1, array_fill(0,3,'Total'));

        $r2 = [''];
        for ($i=0;$i<7+4;$i++) { $r2 = array_merge($r2,['M','F','T']); }
        $r2 = array_merge($r2,['M','F','T']);

        return [$r1, $r2];
    }


    public function columnWidths(): array{
        $widths = ['A' => 14];
        $colNum = 2;
        $totalCols = 1 + 7*3 + 4*3 + 3;
    
        for ($i = 0; $i < $totalCols - 1; $i++, $colNum++) {
            $letter = Coordinate::stringFromColumnIndex($colNum);
            $widths[$letter] = ($i < 21) ? 7 : 9;
        }
        return $widths;
    }
    

    public function styles(Worksheet $sheet){
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]],
            'alignment'=>[
                'horizontal'=>Alignment::HORIZONTAL_CENTER,
                'vertical'  =>Alignment::VERTICAL_CENTER,
                'wrapText'  =>true]
        ]);

        $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A1:A{$lastRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }


    public function registerEvents(): array{
        return [
        AfterSheet::class => function(AfterSheet $e) {

            $sheet = $e->sheet;
            $startColNum = 2;
            foreach (array_merge(
                ['Merit','A','B','C','D','E','U'],
                ['MAB%','MABC%','MABCD%','DEU%'],
                ['Total']
            ) as $label) {

                $start = Coordinate::stringFromColumnIndex($startColNum);
                $end   = Coordinate::stringFromColumnIndex($startColNum + 2);
                $sheet->mergeCells("{$start}1:{$end}1");
                $startColNum += 3;
            }

            $highestCol = $sheet->getHighestColumn();
            $sheet->getStyle("A1:{$highestCol}2")->applyFromArray([
                'fill'=>[
                    'fillType'=>Fill::FILL_SOLID,
                    'startColor'=>['rgb'=>'F2F2F2']
                ],
                'font'=>['bold'=>true]
            ]);

            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle("A{$lastRow}:{$highestCol}{$lastRow}")
                  ->getFont()->setBold(true);
        }
    ];
}



    public function title(): string{
        return 'Class Perf '.ucfirst($this->type);
    }
}
