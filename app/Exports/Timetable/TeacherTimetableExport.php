<?php

namespace App\Exports\Timetable;

class TeacherTimetableExport extends ClassTimetableExport {
    public function __construct(
        protected array $gridData,
        protected array $daySchedule,
        protected string $teacherName,
        protected string $timetableName
    ) {
        parent::__construct($gridData, $daySchedule, $teacherName, $timetableName);
    }

    /**
     * Override: teacher timetable shows class name instead of teacher initials.
     */
    protected function getDetailText(array $slot): string {
        return $slot['class_name'] ?? '';
    }
}
