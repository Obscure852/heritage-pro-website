<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class ClassListReportExport implements FromView, ShouldAutoSize {
    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function view(): View {
        return view('exports.students.class-list-report-export', $this->data);
    }
}
