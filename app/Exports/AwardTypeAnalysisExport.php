<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AwardTypeAnalysisExport implements WithMultipleSheets {
    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function sheets(): array {
        return [
            new AwardTypeAnalysisSheet($this->data),
        ];
    }
}
