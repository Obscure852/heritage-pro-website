<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class HouseCreditsPerformanceExport implements FromArray, ShouldAutoSize, WithStyles, WithEvents, WithTitle {
    protected $type;
    protected $test;
    protected $sequence;
    protected $houseData;
    protected $creditCategories;
    protected $cumulativeThresholds;
    protected $totalStats;
    protected $rowCounter;
    protected $houseHeaderRows = [];
    protected $totalRowPositions = [];

    public function __construct(array $data) {
        $this->type = $data['type'] ?? null;
        $this->test = $data['test'] ?? null;
        $this->sequence = $data['sequence'] ?? null;
        $this->houseData = $data['houseData'] ?? [];
        $this->creditCategories = $data['creditCategories'] ?? [];
        $this->cumulativeThresholds = $data['cumulativeThresholds'] ?? [9, 8, 7, 6, 5, 4, 3];
        $this->totalStats = $data['totalStats'] ?? [];
        $this->rowCounter = 1;
    }

    private function bandLabel(int $th): string {
        if ($th === 9) return '9+ Credits';
        return $th . '-9 Credits';
    }

    public function array(): array {
        $rows = [];

        $title = ($this->test && ($this->test->type ?? '') === 'CA')
            ? 'End of ' . (($this->test->name ?? null) ?: 'Month') . ' House Credits Performance Analysis'
            : 'End of Term House Credits Performance Analysis';

        $rows[] = [$title];
        $this->rowCounter++;

        // Header rows
        $header1 = ['Name of House', 'Class', 'Class Size', 'No. Wrote'];
        foreach ($this->cumulativeThresholds as $th) {
            $header1[] = $this->bandLabel($th);
            $header1[] = '';
        }
        $rows[] = $header1;
        $this->rowCounter++;

        $header2 = ['', '', '', ''];
        foreach ($this->cumulativeThresholds as $th) {
            $header2[] = 'No.';
            $header2[] = '%';
        }
        $rows[] = $header2;
        $this->rowCounter++;

        // Per-house data
        foreach ($this->houseData as $houseName => $data) {
            // House header row
            $rows[] = [strtoupper($houseName) . ' HOUSE'];
            $this->houseHeaderRows[] = $this->rowCounter;
            $this->rowCounter++;

            foreach ($data['classes'] as $className => $classStats) {
                $row = [
                    '',
                    $className,
                    $classStats['classSize'] ?? 0,
                    $classStats['total'] ?? 0,
                ];
                foreach ($this->cumulativeThresholds as $th) {
                    $row[] = $classStats['cumulative']['no'][$th] ?? 0;
                    $row[] = number_format($classStats['cumulative']['pct'][$th] ?? 0, 2);
                }
                $rows[] = $row;
                $this->rowCounter++;
            }

            // House total row
            $totRow = [
                'Total',
                count($data['classes']),
                $data['stats']['classSize'] ?? 0,
                $data['stats']['total'] ?? 0,
            ];
            foreach ($this->cumulativeThresholds as $th) {
                $totRow[] = $data['stats']['cumulative']['no'][$th] ?? 0;
                $totRow[] = number_format($data['stats']['cumulative']['pct'][$th] ?? 0, 2);
            }
            $rows[] = $totRow;
            $this->totalRowPositions[] = $this->rowCounter;
            $this->rowCounter++;

            // Blank row
            $rows[] = [''];
            $this->rowCounter++;
        }

        // School overall summary
        $rows[] = ['SCHOOL OVERALL'];
        $this->houseHeaderRows[] = $this->rowCounter;
        $this->rowCounter++;

        foreach ($this->houseData as $houseName => $data) {
            $row = [
                strtoupper($houseName),
                count($data['classes']),
                $data['stats']['classSize'] ?? 0,
                $data['stats']['total'] ?? 0,
            ];
            foreach ($this->cumulativeThresholds as $th) {
                $row[] = $data['stats']['cumulative']['no'][$th] ?? 0;
                $row[] = number_format($data['stats']['cumulative']['pct'][$th] ?? 0, 2);
            }
            $rows[] = $row;
            $this->rowCounter++;
        }

        // School total
        $schoolRow = [
            'Overall',
            '',
            $this->totalStats['classSize'] ?? 0,
            $this->totalStats['total'] ?? 0,
        ];
        foreach ($this->cumulativeThresholds as $th) {
            $schoolRow[] = $this->totalStats['cumulative']['no'][$th] ?? 0;
            $schoolRow[] = number_format($this->totalStats['cumulative']['pct'][$th] ?? 0, 2);
        }
        $rows[] = $schoolRow;
        $this->totalRowPositions[] = $this->rowCounter;
        $this->rowCounter++;

        return $rows;
    }

    public function styles(Worksheet $sheet) {
        $lastCol = chr(ord('A') + 3 + count($this->cumulativeThresholds) * 2 - 1); // D + 14 cols = R
        $numCols = 4 + count($this->cumulativeThresholds) * 2;
        if ($numCols > 26) {
            // Handle columns beyond Z if needed
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numCols);
        } else {
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numCols);
        }

        // Title
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);

        // Header rows (2-3)
        $sheet->getStyle("A2:{$lastCol}3")->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle("A2:{$lastCol}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A2:{$lastCol}3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle("A2:{$lastCol}3")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Merge band header cells in row 2
        $colIndex = 5; // E
        foreach ($this->cumulativeThresholds as $th) {
            $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->mergeCells("{$startCol}2:{$endCol}2");
            $colIndex += 2;
        }

        // House header rows (blue)
        foreach ($this->houseHeaderRows as $row) {
            $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4E73DF');
        }

        // Total rows (grey)
        foreach ($this->totalRowPositions as $row) {
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE6E6E6');
        }

        // All data borders
        $lastRow = $this->rowCounter - 1;
        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Center align all cells except first column
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A1:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return $sheet;
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $ws->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $ws->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $ws->getPageSetup()->setFitToWidth(1);
                $ws->getPageSetup()->setFitToHeight(0);
                $ws->freezePane('A4');
            },
        ];
    }

    public function title(): string {
        return 'Credits Analysis';
    }
}
