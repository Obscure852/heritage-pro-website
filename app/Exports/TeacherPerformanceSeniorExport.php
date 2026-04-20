<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Events\AfterSheet;

class TeacherPerformanceSeniorExport implements FromArray, ShouldAutoSize, WithStyles, WithEvents{
    public function __construct(
        protected array $teacherPerformance,
        protected $test,
        protected bool $isGrouped = false
    ) {}

    private array $grades = ['A*','A','B','C','D','E','F','G','U','NS'];
    private array $percs  = ['AB%','ABC%','ABCD%','DEFGU%'];

    public function array(): array{
        $rows = [];

        $title = ($this->test?->type === 'CA')
            ? (($this->test?->grade?->name ?? 'Grade') . ' - End of ' . ($this->test?->name ?? 'Month') . ' Subjects Analysis')
            : (($this->test?->grade?->name ?? 'Grade') . ' - End of Term Subjects Analysis');

        $rows[] = [$title];
        $rows[] = [];

        $appendHeaders = function () use (&$rows) {
            $rows[] = $this->headerRow1();
            $rows[] = $this->headerRow2();
        };

        if ($this->isGrouped) {
            foreach ($this->teacherPerformance as $subject => $perf) {
                $rows[] = [$subject . ' PERFORMANCE ANALYSIS'];
                $appendHeaders();
                foreach ($perf as $p) {
                    $rows[] = $this->teacherRow($p);
                }
                $rows[] = $this->totalsRow($this->totals($perf));
                $rows[] = [];
            }
        } else {
            $appendHeaders();
            foreach ($this->teacherPerformance as $p) {
                $rows[] = $this->teacherRow($p);
            }
            $rows[] = $this->totalsRow($this->totals($this->teacherPerformance));
        }

        return $rows;
    }

    private function headerRow1(): array{
        return Arr::flatten([
            ['Teacher','Class','Subject'],
            ...array_map(fn($g)=>[$g,'',''],  $this->grades),
            ...array_map(fn($p)=>[$p,''],  $this->percs),
            ['Total','','']
        ]);
    }

    private function headerRow2(): array{
        return Arr::flatten([
            ['','',''],
            ...array_fill(0, count($this->grades), ['M','F','T']),
            ...array_fill(0, count($this->percs), ['M','F']),
            ['M','F','T']
        ]);
    }

    private function teacherRow(array $p): array{
        return Arr::flatten([
            [$p['teacher_name'],$p['class_name'],$p['subject_name']],
            ...collect($this->grades)->flatMap(fn($g)=>[$p['grades'][$g]['M'],$p['grades'][$g]['F'],$p['grades'][$g]['T'] ?? 0])->toArray(),
            ...collect($this->percs )->flatMap(fn($k)=>[$p[$k]['M'].'%',$p[$k]['F'].'%'])->toArray(),
            [$p['totalMale'],$p['totalFemale'],$p['totalStudents'] ?? ($p['totalMale'] + $p['totalFemale'])]
        ]);
    }

    private function totalsRow(array $t): array{
        return Arr::flatten([
            ['TOTALS','',''],
            ...collect($this->grades)->flatMap(fn($g)=>[$t['grades'][$g]['M'],$t['grades'][$g]['F'],$t['grades'][$g]['T'] ?? 0])->toArray(),
            ...collect($this->percs )->flatMap(fn($k)=>[$t[$k]['M'].'%',$t[$k]['F'].'%'])->toArray(),
            [$t['totalMale'],$t['totalFemale'],$t['totalMale'] + $t['totalFemale']]
        ]);
    }

    private function totals(array $rows): array{
        $total = [
            'grades'=>array_fill_keys($this->grades, ['M'=>0,'F'=>0,'T'=>0]),
            'totalMale'=>0,'totalFemale'=>0
        ] + array_fill_keys($this->percs, ['M'=>0,'F'=>0]);

        foreach ($rows as $r) {
            foreach ($this->grades as $g) {
                $total['grades'][$g]['M'] += $r['grades'][$g]['M'] ?? 0;
                $total['grades'][$g]['F'] += $r['grades'][$g]['F'] ?? 0;
                $total['grades'][$g]['T'] += $r['grades'][$g]['T'] ?? 0;
            }
            foreach ($this->percs as $k) {
                $total[$k]['M'] += $r[$k]['M'] ?? 0;
                $total[$k]['F'] += $r[$k]['F'] ?? 0;
            }
            $total['totalMale']   += $r['totalMale'];
            $total['totalFemale'] += $r['totalFemale'];
        }
        $div = max(count($rows),1);
        foreach ($this->percs as $k) {
            $total[$k]['M'] = round($total[$k]['M'] / $div, 2);
            $total[$k]['F'] = round($total[$k]['F'] / $div, 2);
        }
        return $total;
    }

    public function styles(Worksheet $sheet){
        $endCol = $sheet->getHighestColumn();
        $endRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$endCol}{$endRow}")->applyFromArray([
            'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]],
            'alignment'=>[
                'horizontal'=>Alignment::HORIZONTAL_CENTER,
                'vertical'  =>Alignment::VERTICAL_CENTER
            ]
        ]);

        $sheet->getStyle("A1:A{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function (AfterSheet $e) {

                $ws      = $e->sheet->getDelegate();
                $lastCol = $ws->getHighestColumn();
                $lastRow = $ws->getHighestRow();
                $groups  = array_merge($this->grades, $this->percs, ['Total']);

                $ws->mergeCells("A1:{$lastCol}1");
                $ws->getStyle("A1")->applyFromArray([
                    'font'=>['bold'=>true,'size'=>14],
                    'alignment'=>[
                        'horizontal'=>Alignment::HORIZONTAL_LEFT,
                        'vertical'  =>Alignment::VERTICAL_CENTER
                    ]
                ]);

                for ($row = 1; $row <= $lastRow; $row++) {
                    $firstCell = trim((string) $ws->getCell("A{$row}")->getValue());

                    if (str_ends_with($firstCell, 'Performance Analysis')) {
                        $ws->mergeCells("A{$row}:{$lastCol}{$row}");
                        $ws->getStyle("A{$row}")->applyFromArray([
                            'font'=>['bold'=>true,'size'=>13],
                            'alignment'=>[
                                'horizontal'=>Alignment::HORIZONTAL_LEFT,
                                'vertical'  =>Alignment::VERTICAL_CENTER
                            ],
                            'fill'=>[
                                'fillType'=>Fill::FILL_SOLID,
                                'startColor'=>['rgb'=>'F2F2F2']
                            ]
                        ]);
                        continue;
                    }

                    if ($firstCell === 'Teacher') {
                        $header1 = $row;
                        $header2 = $row + 1;

                        $colNum = 4;
                        foreach ($groups as $g) {
                            $start = Coordinate::stringFromColumnIndex($colNum);
                            $end   = Coordinate::stringFromColumnIndex($colNum + 1);
                            $ws->mergeCells("{$start}{$header1}:{$end}{$header1}");
                            $colNum += 2;
                        }

                        $ws->getStyle("A{$header1}:{$lastCol}{$header2}")->applyFromArray([
                            'font'=>['bold'=>true],
                            'fill'=>[
                                'fillType'=>Fill::FILL_SOLID,
                                'startColor'=>['rgb'=>'D9E1F2']
                            ]
                        ]);

                        $ws->getStyle("A{$header1}:{$lastCol}{$header2}")
                           ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        $row++;
                        continue;
                    }

                    if ($firstCell === 'Totals') {
                        $ws->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'font'=>['bold'=>true],
                            'fill'=>[
                                'fillType'=>Fill::FILL_SOLID,
                                'startColor'=>['rgb'=>'F2F2F2']
                            ]
                        ]);
                    }
                }
            }
        ];
    }
}
