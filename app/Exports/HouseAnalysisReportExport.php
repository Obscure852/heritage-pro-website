<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class HouseAnalysisReportExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.houses.students-house-analysis-export', $data);
    }
}
