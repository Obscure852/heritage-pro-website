<?php

namespace App\Exports;

class StudentClassStatisticalAnalysisExport extends BaseExport{

    public function __construct($data){
        parent::__construct('exports.students.students-statistical-analysis-term',$data);
    }
}
