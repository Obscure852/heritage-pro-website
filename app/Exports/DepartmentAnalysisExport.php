<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DepartmentAnalysisExport implements WithMultipleSheets
{
    protected array $viewData;

    public function __construct(array $viewData)
    {
        $this->viewData = $viewData;
    }

    public function sheets(): array
    {
        $sheets = [];

        // ---- Build the sheet title (matches your Blade logic) ----
        $test      = $this->viewData['test'] ?? null;
        $gradeName = $test && $test->grade ? ($test->grade->name ?? 'Grade') : 'Grade';

        if ($test && ($test->type ?? '') === 'CA') {
            $title = sprintf('%s - End of %s Departments Analysis', $gradeName, ($test->name ?? 'Month'));
        } else {
            $title = sprintf('%s - End of Term Departments Analysis', $gradeName);
        }

        // ---- Add one sheet per department ----
        foreach ($this->viewData['performanceData'] as $deptName => $deptData) {
            $sheets[] = new DepartmentSheetExport(
                $deptName,                 // string department name
                $deptData,                 // array data for this department
                $this->viewData['term'],   // term object
                $this->viewData['type'],   // "CA" / "Exam" (already ucfirst in your controller)
                $title                     // <-- merged/bold/left-aligned title (A1:… in sheet class)
            );
        }

        // ---- Overall totals sheet ----
        $sheets[] = new DepartmentOverallSheetExport(
            $this->viewData['totals'],   // overall totals array
            $this->viewData['term'],     // term object
            $this->viewData['type'],     // "CA" / "Exam"
            $title                       // <-- same title for consistency
        );

        return $sheets;
    }
}
