<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Events\AfterSheet;

class SubjectAnalysisExport implements FromArray, ShouldAutoSize, WithEvents, WithColumnWidths {
    private array $perf;
    private array $totals;

    public function __construct(array $subjectPerformance, array $subjectTotals){
        $this->perf   = $subjectPerformance;
        $this->totals = $subjectTotals;
    }

    public function array(): array{
        $rows   = [];
        $grades = ['A','B','C','D','E','U'];
        $percs  = ['AB%','ABC%','ABCD%','DEU%'];

        $rows[] = Arr::flatten([
            ['Subject'],
            ...array_map(fn($g)=>[$g,'',''], $grades),
            ...array_map(fn($p)=>[$p,'',''], $percs),
        ]);

        $rows[] = Arr::flatten([
            [''],
            ...array_fill(0, count($grades)+count($percs), ['M','F','T'])
        ]);

        foreach ($this->perf as $subj => $c) {
            $rows[] = Arr::flatten([
                [$subj],
                ...collect($grades)->flatMap(fn($g)=>[
                    $c[$g]['M'],
                    $c[$g]['F'],
                    $c[$g]['M'] + $c[$g]['F']
                ])->toArray(),
                ...collect($percs)->flatMap(fn($p)=>[
                    $c[$p]['M'].'%',
                    $c[$p]['F'].'%',
                    round(($c[$p]['M'] + $c[$p]['F']) / 2).'%' 
                ])->toArray(),
            ]);
        }

        $rows[] = Arr::flatten([
            ['Totals'],
            ...collect($grades)->flatMap(fn($g)=>[
                $this->totals[$g]['M'],
                $this->totals[$g]['F'],
                $this->totals[$g]['M'] + $this->totals[$g]['F']
            ]),
            ...collect($percs)->flatMap(fn($p)=>[
                $this->totals[$p]['M'].'%',
                $this->totals[$p]['F'].'%',
                round(($this->totals[$p]['M'] + $this->totals[$p]['F']) / 2).'%'
            ]),
        ]);

        return $rows;
    }

    public function columnWidths(): array{
        $widths = [];
        
        $widths['A'] = 25;
        $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 
                   'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF'];
        
        foreach ($columns as $col) {
            $widths[$col] = 8;
        }
        
        return $widths;
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $ws      = $e->sheet->getDelegate();
                $lastCol = $ws->getHighestColumn();
                $lastRow = $ws->getHighestRow();

                $grades = ['A','B','C','D','E','U'];
                $percs  = ['AB%','ABC%','ABCD%','DEU%'];
                $groups = array_merge($grades, $percs);

                $colNum = 2;
                foreach ($groups as $_) {
                    $start = Coordinate::stringFromColumnIndex($colNum);
                    $end   = Coordinate::stringFromColumnIndex($colNum + 2);
                    $ws->mergeCells("{$start}1:{$end}1");
                    $colNum += 3;
                }

                $ws->getStyle("A1:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                
                $ws->getRowDimension(1)->setRowHeight(24);
                $ws->getRowDimension(2)->setRowHeight(18);

                $ws->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                $ws->getRowDimension($lastRow)->setRowHeight(20);

                $ws->getStyle("A1:{$lastCol}{$lastRow}")
                   ->getBorders()
                   ->getAllBorders()
                   ->setBorderStyle(Border::BORDER_THIN);
                   
                $ws->getStyle("A1:{$lastCol}{$lastRow}")
                   ->getBorders()
                   ->getOutline()
                   ->setBorderStyle(Border::BORDER_MEDIUM);

                $ws->getStyle("A1:A{$lastRow}")
                   ->getAlignment()
                   ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                   
                $ws->getStyle("B3:{$lastCol}{$lastRow}")
                   ->getAlignment()
                   ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                   
                $colNum = 4;
                for ($i = 0; $i < count($groups); $i++) {
                    $column = Coordinate::stringFromColumnIndex($colNum);
                    $ws->getStyle("{$column}3:{$column}{$lastRow}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F8F8']
                        ],
                        'font' => ['bold' => true]
                    ]);
                    $colNum += 3;
                }
                
                $ws->freezePane('B3'); 
                $ws->insertNewRowBefore(1);
                $ws->mergeCells("A1:{$lastCol}1");
                $ws->setCellValue('A1', 'Subject Performance Analysis');
                $ws->getStyle("A1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                $ws->getRowDimension(1)->setRowHeight(30);
            },
        ];
    }
}