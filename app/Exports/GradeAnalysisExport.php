<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GradeAnalysisExport implements WithMultipleSheets{
    protected $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function sheets(): array{
        $sheets = [
            new GradeAnalysisStudentsSheet($this->data)
        ];
        return $sheets;
    }
}