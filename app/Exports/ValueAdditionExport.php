<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ValueAdditionExport implements FromView, ShouldAutoSize, WithStyles {
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function view(): View {
        return view('exports.assessment.senior.value-addition-report', $this->data);
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
