<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ParentsAnalysisListExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.sponsors.sponsors-analysis-list', $data);
    }
}