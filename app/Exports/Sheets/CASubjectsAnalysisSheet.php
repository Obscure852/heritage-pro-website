<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CASubjectsAnalysisSheet implements FromView, ShouldAutoSize, WithStyles{
    protected $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function view(): View{
        return view('exports.assessment.junior.ca-class-analysis-subjects', $this->data);
    }

    public function styles(Worksheet $sheet){
        return [
            1 => ['font' => ['bold' => true]]
        ];
    }
}
