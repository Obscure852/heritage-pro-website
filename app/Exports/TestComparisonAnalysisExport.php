<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TestComparisonAnalysisExport implements FromArray, WithHeadings, WithEvents, WithTitle{
    protected array $data;

    public function __construct(array $data){
        $this->data = $data;
    }
    
    public function array(): array{
        $subjects       = $this->data['subjects'];
        $gradeCounts    = $this->data['gradeCounts'];
        $prevTotals     = $this->data['prevGradeCounts'];
        $currTotals     = $this->data['currGradeCounts'];
        $valueAdditions = $this->data['valueAdditions'];
        $rankedSubjects = $this->data['rankedSubjects'];

        $rows = [];

        $header1 = ['Grade'];
        foreach ($subjects as $sub) {
            $header1[] = $sub;
            $header1[] = '';
        }
        $rows[] = $header1;

        $header2 = [''];
        foreach ($subjects as $sub) {
            $header2[] = 'Prev';
            $header2[] = 'Curr';
        }
        $rows[] = $header2;

        $grades = ['A','B','C','D','E','U'];
        foreach ($grades as $g) {
            $row = [$g];
            foreach ($subjects as $sub) {
                $row[] = $gradeCounts[$sub]['prev'][$g] ?? 0;
                $row[] = $gradeCounts[$sub]['curr'][$g] ?? 0;
            }
            $rows[] = $row;
        }

        $rows[] = array_merge(['Quality'], array_reduce($subjects, function($carry, $sub) use ($gradeCounts) {
            $carry[] = $gradeCounts[$sub]['qualityPrev'] . '%';
            $carry[] = $gradeCounts[$sub]['qualityCurr'] . '%';
            return $carry;
        }, []));

        $rows[] = array_merge(['Quantity'], array_reduce($subjects, function($carry, $sub) use ($gradeCounts) {
            $carry[] = $gradeCounts[$sub]['quantityPrev'] . '%';
            $carry[] = $gradeCounts[$sub]['quantityCurr'] . '%';
            return $carry;
        }, []));

        $rows[] = array_merge(['Value Addition'], array_reduce($subjects, function($carry, $sub) use ($gradeCounts) {
            $carry[] = $gradeCounts[$sub]['valueAddition'];
            $carry[] = '';
            return $carry;
        }, []));

        $rows[] = array_merge(['Rank'], array_reduce($subjects, function($carry, $sub) use ($rankedSubjects) {
            $rank = array_search($sub, $rankedSubjects, true);
            $carry[] = $rank !== false ? $rank + 1 : '-';
            $carry[] = '';
            return $carry;
        }, []));

        $rows[] = [];

        $totalPrev = array_sum($prevTotals);
        $abPrev    = (($prevTotals['A'] ?? 0) + ($prevTotals['B'] ?? 0)) / max($totalPrev,1) * 100;
        $abcPrev   = (($prevTotals['A'] ?? 0) + ($prevTotals['B'] ?? 0) + ($prevTotals['C'] ?? 0)) / max($totalPrev,1) * 100;
        $deuPrev   = (($prevTotals['D'] ?? 0) + ($prevTotals['E'] ?? 0) + ($prevTotals['U'] ?? 0)) / max($totalPrev,1) * 100;
        $rows[] = array_merge(['Previous Overall'], array_values($prevTotals), [$totalPrev, round($abPrev,2) . '%', round($abcPrev,2) . '%', round($deuPrev,2) . '%']);

        $totalCurr = array_sum($currTotals);
        $abCurr    = (($currTotals['A'] ?? 0) + ($currTotals['B'] ?? 0)) / max($totalCurr,1) * 100;
        $abcCurr   = (($currTotals['A'] ?? 0) + ($currTotals['B'] ?? 0) + ($currTotals['C'] ?? 0)) / max($totalCurr,1) * 100;
        $deuCurr   = (($currTotals['D'] ?? 0) + ($currTotals['E'] ?? 0) + ($currTotals['U'] ?? 0)) / max($totalCurr,1) * 100;
        $rows[] = array_merge(['Current Overall'], array_values($currTotals), [$totalCurr, round($abCurr,2) . '%', round($abcCurr,2) . '%', round($deuCurr,2) . '%']);

        $rows[] = ['', '', 'Overall Value Addition', round($valueAdditions['overall'],2) . '%'];

        return $rows;
    }

    public function headings(): array{
        return [];
    }

    public function title(): string{
        return 'Test Comparison';
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIdx = Coordinate::columnIndexFromString($highestColumn);
                $highestRow = $sheet->getHighestRow();

                $colIndex = 2;
                foreach ($this->data['subjects'] as $sub) {
                    $start = Coordinate::stringFromColumnIndex($colIndex);
                    $end = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->mergeCells("{$start}1:{$end}1");
                    $colIndex += 2;
                }

                $sheet->getStyle("A1:{$highestColumn}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
                ]);

                $startDataRow = 3 + count(['A','B','C','D','E','U']);
                foreach (range($startDataRow, $startDataRow + 3) as $row) {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
                    ]);
                }

                $sheet->getStyle("A{$highestRow}:{$highestColumn}{$highestRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFBDD7EE']],
                ]);

                for ($col = 1; $col <= $highestColumnIdx; $col++) {
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($letter)->setWidth($col === 1 ? 15 : 12);
                }
            }
        ];
    }
}
