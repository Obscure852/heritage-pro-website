<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class AdmissionByStatusExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.admissions.admisions-status-export', $data);
    }
}