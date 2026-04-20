<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SubjectGradePerformanceReportExport{
    protected $data;

    public function __construct($data){
        $this->data = $data;
    }

    public function export(){
        $data = $this->data['subjectPerformance']; // Your data for export

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Short Title'); // Set a short title within the 31 characters limit

        // Set headers
        $headers = ['Subject'];
        foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade) {
            $headers[] = $grade . ' M';
            $headers[] = $grade . ' F';
            $headers[] = $grade . '% M / F';
        }
        $sheet->fromArray($headers, null, 'A1');

        // Style the headers
        $headerStyleArray = [
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => Color::COLOR_BLACK],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => Color::COLOR_WHITE],
            ],
        ];

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyleArray);
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->getFont()->setSize(14);

        // Populate the spreadsheet with data
        $row = 2;
        foreach ($data as $subjectName => $values) {
            $sheet->setCellValue('A' . $row, $subjectName);

            $col = 'B';
            foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade) {
                $sheet->setCellValue($col . $row, $values[$grade]['M']);
                $col++;
                $sheet->setCellValue($col . $row, $values[$grade]['F']);
                $col++;
                $sheet->setCellValue($col . $row, round($values[$grade . '%']['M'], 1) . '% / ' . round($values[$grade . '%']['F'], 1) . '%');
                $col++;
            }

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, 'subject-performance.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
