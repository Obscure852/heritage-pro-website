<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassPerformanceAnalysisExport implements FromView, WithStyles{
    protected $data;

    public function __construct($data){
        $this->data = $data;
    }

    public function view(): View{
        return view('exports.assessment.primary.test-primary-class-analysis', $this->data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '4F81BD'],
            ],
        ]);

        $sheet->getDefaultColumnDimension()->setWidth(20);

        $sheet->getStyle('A:Z')->applyFromArray([
            'font' => [
                'size' => 14,
            ],
        ]);

        return [
            // Style the first row as bold and with increased font size and color
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFF']]],
            'A' => ['font' => ['size' => 14]],
            'B' => ['font' => ['size' => 14]],
            'C' => ['font' => ['size' => 14]],
            'D' => ['font' => ['size' => 14]],
            // You can add more styles for other columns as needed
        ];
    }
}
