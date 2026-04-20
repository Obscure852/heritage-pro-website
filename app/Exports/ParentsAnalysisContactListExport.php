<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ParentsAnalysisContactListExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.sponsors.sponsors-contact-list', $data);
    }
}