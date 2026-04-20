<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HouseAwardAnalysisExport implements WithMultipleSheets {
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function sheets(): array {
        $sheets = [];
        foreach ($this->data['housesData'] as $houseName => $classesData) {
            $sheets[] = new HouseAwardAnalysisSheet([
                'houseName' => $houseName,
                'classesData' => $classesData,
                'allSubjects' => $this->data['allSubjects'],
                'gradeName' => $this->data['gradeName'],
                'type' => $this->data['type'],
                'test' => $this->data['test'],
            ]);
        }
        return $sheets;
    }
}
