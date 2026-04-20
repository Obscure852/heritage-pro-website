<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegionalGradePerformanceReportExport implements FromView, WithStyles{
    protected $data;

    public function __construct($data){
        $this->data = $data;
    }

    public function view(): View{
        return view('exports.assessment.primary.regional-test-primary-grade-subject-analysis', $this->data);
    }

    public function styles(Worksheet $sheet){
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
        return [];
    }
}
