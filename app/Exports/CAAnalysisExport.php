<?php

namespace App\Exports;

use App\Exports\Sheets\CAClassGradeAnalysisSheet;
use App\Exports\Sheets\CAPSLEAnalysisSheet;
use App\Exports\Sheets\CASubjectsAnalysisSheet;
use App\Exports\Sheets\CAStudentsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CAAnalysisExport implements WithMultipleSheets{
    protected $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function sheets(): array{
        return [
            'Student Listing'       => new CAStudentsSheet($this->data),
            'Class Grade Analysis' => new CAClassGradeAnalysisSheet($this->data),
            'PSLE Performance'     => new CAPSLEAnalysisSheet($this->data),
            'Subjects Analysis'     => new CASubjectsAnalysisSheet($this->data),
        ];
    }
}
