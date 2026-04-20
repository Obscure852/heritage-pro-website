<?php

namespace App\Exports;

class BoardingAnalysisExport extends BaseExport {

    public function __construct($data) {
        parent::__construct('exports.students.boarding-analysis-export', $data);
    }
}
