<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class House6CTrackingSheet implements FromArray, WithTitle, WithStyles, WithEvents {
    protected array $data;
    protected int $totalRows = 0;
    protected array $totalRowIndices = [];
    protected int $grandTotalRowIndex = 0;
    protected array $houseHeaderRows = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function array(): array {
        $testPeriods = $this->data['testPeriods'];
        $housesData = $this->data['housesData'];
        $grandTotal = $this->data['grandTotal'];
        $colsPerPeriod = 7;

        $rows = [];

        // Row 1: Title
        $rows[] = [];

        // Row 2: Subtitle
        $rows[] = [];

        // Row 3: Test period labels (will be merged in AfterSheet)
        $headerRow = ['HOUSE'];
        foreach ($testPeriods as $tp) {
            $headerRow[] = $tp['label'];
            for ($i = 1; $i < $colsPerPeriod; $i++) {
                $headerRow[] = '';
            }
        }
        $rows[] = $headerRow;

        // Row 4: Sub-headers
        $subHeaderRow = [''];
        foreach ($testPeriods as $tp) {
            $subHeaderRow[] = 'CLASS';
            $subHeaderRow[] = 'Size';
            $subHeaderRow[] = 'No.Sat';
            $subHeaderRow[] = 'No.';
            $subHeaderRow[] = '%';
            $subHeaderRow[] = 'JCE%';
            $subHeaderRow[] = 'VA%';
        }
        $rows[] = $subHeaderRow;

        // Data rows per house
        foreach ($housesData as $houseName => $periodData) {
            $maxClasses = 0;
            foreach ($testPeriods as $tpIdx => $tp) {
                $classCount = isset($periodData[$tpIdx]) ? count($periodData[$tpIdx]['classes']) : 0;
                $maxClasses = max($maxClasses, $classCount);
            }
            if ($maxClasses === 0) $maxClasses = 1;

            $houseStartRow = count($rows) + 1;
            $this->houseHeaderRows[] = $houseStartRow;

            for ($row = 0; $row < $maxClasses; $row++) {
                $dataRow = [$row === 0 ? strtoupper($houseName) : ''];
                foreach ($testPeriods as $tpIdx => $tp) {
                    $cls = isset($periodData[$tpIdx]['classes'][$row])
                        ? $periodData[$tpIdx]['classes'][$row]
                        : null;
                    if ($cls) {
                        $dataRow[] = $cls['name'];
                        $dataRow[] = $cls['size'];
                        $dataRow[] = $cls['noSat'];
                        $dataRow[] = $cls['no6c'];
                        $dataRow[] = round($cls['pct'], 2);
                        $dataRow[] = round($cls['jcePct'], 2);
                        $dataRow[] = round($cls['vaPct'], 2);
                    } else {
                        $dataRow = array_merge($dataRow, ['-', '-', '-', '-', '-', '-', '-']);
                    }
                }
                $rows[] = $dataRow;
            }

            // House total row
            $totalRow = ['TOTAL'];
            foreach ($testPeriods as $tpIdx => $tp) {
                $ht = isset($periodData[$tpIdx]['total']) ? $periodData[$tpIdx]['total'] : null;
                if ($ht) {
                    $totalRow[] = '';
                    $totalRow[] = $ht['size'];
                    $totalRow[] = $ht['noSat'];
                    $totalRow[] = $ht['no6c'];
                    $totalRow[] = round($ht['pct'], 2);
                    $totalRow[] = round($ht['jcePct'], 2);
                    $totalRow[] = round($ht['vaPct'], 2);
                } else {
                    $totalRow = array_merge($totalRow, ['-', '-', '-', '-', '-', '-', '-']);
                }
            }
            $rows[] = $totalRow;
            $this->totalRowIndices[] = count($rows);
        }

        // Grand total row
        $grandRow = ['TOTAL'];
        foreach ($testPeriods as $tpIdx => $tp) {
            $gt = $grandTotal[$tpIdx] ?? null;
            if ($gt) {
                $grandRow[] = '';
                $grandRow[] = $gt['size'];
                $grandRow[] = $gt['noSat'];
                $grandRow[] = $gt['no6c'];
                $grandRow[] = round($gt['pct'], 2);
                $grandRow[] = round($gt['jcePct'], 2);
                $grandRow[] = round($gt['vaPct'], 2);
            } else {
                $grandRow = array_merge($grandRow, ['-', '-', '-', '-', '-', '-', '-']);
            }
        }
        $rows[] = $grandRow;
        $this->grandTotalRowIndex = count($rows);
        $this->totalRows = count($rows);

        return $rows;
    }

    public function title(): string {
        return '6Cs Tracking';
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $testPeriods = $this->data['testPeriods'];
                $colsPerPeriod = 7;
                $totalCols = 1 + (count($testPeriods) * $colsPerPeriod);
                $lastCol = $this->col($totalCols - 1);

                $schoolName = $this->data['schoolName'] ?? 'School';
                $gradeName = $this->data['gradeName'] ?? 'Grade';
                $startYear = $this->data['startYear'] ?? '';

                // Title row
                $sheet->setCellValue('A1', "{$schoolName} - YEAR {$startYear} - {$gradeName} ANALYSIS");
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true)->setSize(14);

                // Subtitle row
                $sheet->setCellValue('A2', 'Number (No.) and Percentage (%) of Students With 6C\'s or Better');
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->getStyle("A2:{$lastCol}2")->getFont()->setItalic(true)->setSize(11);

                // Merge period headers in row 3
                $colIdx = 1;
                foreach ($testPeriods as $tp) {
                    $startCol = $this->col($colIdx);
                    $endCol = $this->col($colIdx + $colsPerPeriod - 1);
                    $sheet->mergeCells("{$startCol}3:{$endCol}3");
                    $colIdx += $colsPerPeriod;
                }

                // Style period headers
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F0F0F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Style sub-headers row 4
                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F0F0F0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Borders for all data
                $sheet->getStyle("A3:{$lastCol}{$this->totalRows}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Style total rows with grey background
                foreach ($this->totalRowIndices as $rowIdx) {
                    $sheet->getStyle("A{$rowIdx}:{$lastCol}{$rowIdx}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F0F0F0']],
                    ]);
                }

                // Style grand total row
                if ($this->grandTotalRowIndex > 0) {
                    $gtr = $this->grandTotalRowIndex;
                    $sheet->getStyle("A{$gtr}:{$lastCol}{$gtr}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F0F0F0']],
                    ]);
                }

                // Center alignment for data area
                $dataStart = 5;
                $sheet->getStyle("A{$dataStart}:{$lastCol}{$this->totalRows}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Left-align house name column
                $sheet->getStyle("A{$dataStart}:A{$this->totalRows}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(18);
                for ($i = 1; $i < $totalCols; $i++) {
                    $col = $this->col($i);
                    $posInGroup = ($i - 1) % $colsPerPeriod;
                    if ($posInGroup === 0) {
                        $sheet->getColumnDimension($col)->setWidth(8); // CLASS
                    } else {
                        $sheet->getColumnDimension($col)->setWidth(8);
                    }
                }

                $sheet->freezePane('B5');
            },
        ];
    }

    public function styles(Worksheet $sheet) {
        return [];
    }

    private function col(int $zeroBasedIndex): string {
        $i = $zeroBasedIndex + 1;
        $name = '';
        while ($i > 0) {
            $rem = ($i - 1) % 26;
            $name = chr(65 + $rem) . $name;
            $i = intdiv($i - 1, 26);
        }
        return $name;
    }
}
