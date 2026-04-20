<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class House6CTrackingExport implements WithMultipleSheets {
    protected array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function sheets(): array {
        return [
            new House6CTrackingSheet($this->data),
        ];
    }
}
