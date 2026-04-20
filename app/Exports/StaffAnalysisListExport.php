<?php

namespace App\Exports;

class StaffAnalysisListExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.staff.staff-analysis-list', $data);
    }
}