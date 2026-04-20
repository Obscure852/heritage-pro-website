<?php

namespace App\Exports\Timetable;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterTimetableExport implements WithMultipleSheets {
    public function __construct(
        protected array $masterData,
        protected array $daySchedule,
        protected string $timetableName
    ) {}

    public function sheets(): array {
        $sheets = [];

        // Sort classes by grade sequence then class name
        $classes = collect($this->masterData['classes'])->sortBy([
            ['grade_sequence', 'asc'],
            ['name', 'asc'],
        ]);

        foreach ($classes as $klassId => $classInfo) {
            $classGrid = $this->masterData['grids'][$klassId] ?? [];

            // Transform master grid format to class grid format
            $transformedGrid = [];
            foreach ($classGrid as $day => $periods) {
                foreach ($periods as $period => $slot) {
                    $transformedGrid[$day][$period] = [
                        'subject_name' => $slot['subject_abbrev'] ?? '?',
                        'teacher_name' => $slot['teacher_initials'] ?? '',
                        'klass_subject_id' => $slot['klass_subject_id'] ?? 0,
                        'duration' => $slot['duration'] ?? 1,
                        'block_id' => $slot['block_id'] ?? null,
                    ];
                }
            }

            $sheets[] = new ClassTimetableExport(
                $transformedGrid,
                $this->daySchedule,
                $classInfo['name'],
                $this->timetableName
            );
        }

        return $sheets;
    }
}
