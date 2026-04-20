<?php

namespace App\Exports;

class StaffAnalysisByQualificationsExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.staff.staff-analysis-qualifications', $data);
    }
}
