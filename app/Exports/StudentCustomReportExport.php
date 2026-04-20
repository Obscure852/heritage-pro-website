<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class StudentCustomReportExport implements FromView, ShouldAutoSize {
    protected $students;
    protected $fields;
    protected $field_headers;
    protected $statistics;

    public function __construct($students, $fields, $field_headers, $statistics = null){
        $this->students = $students;
        $this->fields = $fields;
        $this->field_headers = $field_headers;
        $this->statistics = $statistics;
    }

    public function view(): View{
        return view('exports.students.students-custom-report-export', [
            'students' => $this->students,
            'fields' => $this->fields,
            'field_headers' => $this->field_headers,
            'statistics' => $this->statistics,
        ]);
    }
}
