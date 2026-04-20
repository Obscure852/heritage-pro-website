<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StaffCustomReportExport extends BaseExport implements FromView, WithStyles{
    public function __construct($users, $fields, $field_headers){
        parent::__construct('exports.staff.staff-custom-report-export', compact('users', 'fields', 'field_headers'));
    }

    public function view(): View{
        return view($this->viewName, $this->data);
    }

    public function styles(Worksheet $sheet){
        return [
            1 => ['font' => ['bold' => true]],
            'A' => ['font' => ['size' => 12]],
            'B' => ['font' => ['size' => 12]],
            'C' => ['font' => ['size' => 12]],
            'D' => ['font' => ['size' => 12]],
            'E' => ['font' => ['size' => 12]],
        ];
    }
}