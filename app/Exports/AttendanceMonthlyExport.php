<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceMonthlyExport implements FromCollection, WithHeadings
{
    private array $headingRow;
    private Collection $dataRows;

    public function __construct(array $registerData)
    {
        $dayHeadings = array_map(fn ($d) => $d['label'] . ' ' . $d['day'], $registerData['days']);
        $this->headingRow = array_merge(['Employee'], $dayHeadings);

        $rows = [];
        foreach ($registerData['rows'] as $row) {
            $rows[] = array_merge([$row['user']->name], $row['codes']);
        }

        $this->dataRows = collect($rows);
    }

    public function headings(): array
    {
        return $this->headingRow;
    }

    public function collection(): Collection
    {
        return $this->dataRows;
    }
}
