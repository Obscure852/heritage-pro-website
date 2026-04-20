<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class StaffAnalysisByDepartmentExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.staff.staff-analysis-department', $data);
    }
}
