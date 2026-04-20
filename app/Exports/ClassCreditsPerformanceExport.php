<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassCreditsPerformanceExport implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents{
    protected $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function array(): array{
        $rows = [];

        foreach ($this->data['classStats'] as $className => $stats) {
            $row = [
                $className,
                $this->data['classTeachers'][$className] ?? 'Class Teacher',
                $stats['total']
            ];

            foreach ($this->data['creditCategories'] as $credits) {
                $row[] = $stats['credits'][$credits];
            }

            $row[] = $stats['pointsGte34'];
            $row[] = $stats['pointsGte46'];

            $rows[] = $row;
        }

        $totalsRow = [
            'House / Grade Totals',
            '',
            $this->data['gradeTotals']['total']
        ];

        foreach ($this->data['creditCategories'] as $credits) {
            $totalsRow[] = $this->data['gradeTotals']['credits'][$credits];
        }

        $totalsRow[] = $this->data['gradeTotals']['pointsGte34'];
        $totalsRow[] = $this->data['gradeTotals']['pointsGte46'];

        $rows[] = $totalsRow;

        return $rows;
    }

    public function title(): string{
        return 'Class Credits Performance';
    }

    public function registerEvents(): array{
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 1);

                $test = $this->data['test'] ?? null;
                $gradeName = $test?->grade->name ?? ($this->data['gradeName'] ?? 'Grade');
                if (($test?->type ?? '') === 'CA') {
                    $when = $test?->name ?? 'Month';
                    $title = "{$gradeName} - End of {$when} Class Credits Performance Analysis";
                } else {
                    $title = "{$gradeName} - End of Term Class Credits Performance Analysis";
                }

                $sheet->setCellValue('A1', $title);

                $creditCols = count($this->data['creditCategories']);
                $totalCols  = $creditCols + 5; // Class, Teacher, Total, credits..., ≥34, ≥46
                $lastColumn = $this->getExcelColumn($totalCols - 1);

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A1:{$lastColumn}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(20);

                $sheet->insertNewRowBefore(2, 2);

                $firstCreditCol = $this->getExcelColumn(3);
                $lastCreditCol  = $this->getExcelColumn(3 + $creditCols - 1);
                $sheet->setCellValue($firstCreditCol . '2', 'Number of Credits (A*-A-B-C)');
                $sheet->mergeCells($firstCreditCol . '2:' . $lastCreditCol . '2');
                $sheet->getStyle($firstCreditCol . '2:' . $lastCreditCol . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $firstPointsCol = $this->getExcelColumn(3 + $creditCols);
                $lastPointsCol  = $this->getExcelColumn(3 + $creditCols + 1);
                $sheet->setCellValue($firstPointsCol . '2', 'Best 6 Points');
                $sheet->mergeCells($firstPointsCol . '2:' . $lastPointsCol . '2');
                $sheet->getStyle($firstPointsCol . '2:' . $lastPointsCol . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('A3', 'Class');
                $sheet->setCellValue('B3', 'Class Teacher');
                $sheet->setCellValue('C3', 'Total');

                for ($i = 0; $i < $creditCols; $i++) {
                    $column = $this->getExcelColumn(3 + $i);
                    $sheet->setCellValue($column . '3', $this->data['creditCategories'][$i]);
                }

                $sheet->setCellValue($firstPointsCol . '3', '≥34');
                $sheet->setCellValue($lastPointsCol . '3', '≥46');

                $sheet->getStyle("A2:{$lastColumn}3")->getFont()->setBold(true);
                $sheet->getStyle("A3:{$lastColumn}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $totalRowIndex = count($this->data['classStats']) + 4;
                $sheet->getStyle("A{$totalRowIndex}:{$lastColumn}{$totalRowIndex}")->getFont()->setBold(true);

                $sheet->getStyle("A1:{$lastColumn}{$totalRowIndex}")
                      ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A2:{$lastColumn}3")->getFill()
                      ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E0E0E0');

                for ($i = 4; $i < $totalRowIndex; $i++) {
                    if ($i % 2 === 0) {
                        $sheet->getStyle("A{$i}:{$lastColumn}{$i}")->getFill()
                              ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F5F5F5');
                    }
                }

                $sheet->getStyle("C4:{$lastColumn}{$totalRowIndex}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->freezePane('A4');
                $sheet->setAutoFilter('');
            },
        ];
    }

    public function styles(Worksheet $sheet){
        return [
        ];
    }

    public function columnWidths(): array{
        $widths = [
            'A' => 10,
            'B' => 25,
            'C' => 10,
        ];

        $colIndex = 3;
        foreach ($this->data['creditCategories'] as $ignored) {
            $column = $this->getExcelColumn($colIndex);
            $widths[$column] = 8;
            $colIndex++;
        }

        $widths[$this->getExcelColumn($colIndex)]     = 10; // ≥34
        $widths[$this->getExcelColumn($colIndex + 1)] = 10; // ≥46

        return $widths;
    }

    private function getExcelColumn($index): string{
        if ($index < 26) {
            return chr(65 + $index);
        }
        $dividend = $index + 1;
        $columnName = '';
        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $columnName = chr(65 + $modulo) . $columnName;
            $dividend = (int)(($dividend - $modulo) / 26);
        }
        return $columnName;
    }
}
