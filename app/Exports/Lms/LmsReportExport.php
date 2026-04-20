<?php

namespace App\Exports\Lms;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LmsReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle {
    protected const DEFAULT_COLUMNS = [
        'course_progress' => [
            'student_name' => 'Student Name',
            'email' => 'Email',
            'course' => 'Course',
            'enrolled_at' => 'Enrolled Date',
            'progress' => 'Progress',
            'status' => 'Status',
            'completed_at' => 'Completed Date',
        ],
        'engagement' => [
            'student_name' => 'Student Name',
            'email' => 'Email',
            'content_views' => 'Content Views',
            'quiz_attempts' => 'Quiz Attempts',
            'total_time' => 'Total Time',
            'last_activity' => 'Last Activity',
        ],
        'grades' => [
            'student_name' => 'Student Name',
            'email' => 'Email',
            'quiz_title' => 'Quiz',
            'score' => 'Score',
            'max_score' => 'Max Score',
            'percentage' => 'Percentage',
            'grade_letter' => 'Grade',
            'passed' => 'Passed',
            'submitted_at' => 'Submitted At',
        ],
        'completion' => [
            'student_name' => 'Student Name',
            'email' => 'Email',
            'course' => 'Course',
            'enrolled_at' => 'Enrolled Date',
            'progress' => 'Progress',
            'modules_completed' => 'Modules Done',
            'total_modules' => 'Total Modules',
            'status' => 'Status',
            'completed_at' => 'Completed Date',
        ],
        'quiz_performance' => [
            'quiz_title' => 'Quiz',
            'total_attempts' => 'Total Attempts',
            'completions' => 'Completions',
            'passes' => 'Passes',
            'pass_rate' => 'Pass Rate',
            'avg_score' => 'Avg Score',
        ],
        'content_usage' => [
            'module' => 'Module',
            'content_title' => 'Content',
            'content_type' => 'Type',
            'total_views' => 'Total Views',
            'unique_viewers' => 'Unique Viewers',
            'avg_time' => 'Avg Time',
            'completion_rate' => 'Completion Rate',
        ],
        'time_tracking' => [
            'student_name' => 'Student Name',
            'email' => 'Email',
            'total_time' => 'Total Time',
            'avg_session' => 'Avg Session',
            'active_days' => 'Active Days',
            'last_access' => 'Last Access',
        ],
        'at_risk' => [
            'student_name' => 'Student Name',
            'email' => 'Email',
            'course' => 'Course',
            'risk_type' => 'Risk Type',
            'severity' => 'Severity',
            'description' => 'Description',
            'generated_at' => 'Detected On',
        ],
        'custom' => [],
    ];

    protected array $data;
    protected string $reportType;
    protected ?array $columns;
    protected string $title;

    public function __construct(array $data, string $reportType, ?array $columns = null, string $title = 'LMS Report') {
        $this->data = $data;
        $this->reportType = $reportType;
        $this->columns = $columns;
        $this->title = $title;
    }

    public function array(): array {
        $columns = $this->getColumns();

        return collect($this->data)->map(function ($row) use ($columns) {
            $formatted = [];

            foreach (array_keys($columns) as $key) {
                $value = $row[$key] ?? '';

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $formatted[] = $value;
            }

            return $formatted;
        })->toArray();
    }

    public function headings(): array {
        return array_values($this->getColumns());
    }

    protected function getColumns(): array {
        if ($this->columns) {
            $result = [];
            foreach ($this->columns as $column) {
                $result[$column] = $this->formatColumnLabel($column);
            }
            return $result;
        }

        // For custom reports, derive columns from data
        if ($this->reportType === 'custom' && !empty($this->data)) {
            $firstRow = $this->data[0] ?? [];
            $result = [];
            foreach (array_keys($firstRow) as $key) {
                $result[$key] = $this->formatColumnLabel($key);
            }
            return $result;
        }

        return self::DEFAULT_COLUMNS[$this->reportType] ?? [];
    }

    protected function formatColumnLabel(string $column): string {
        return ucwords(str_replace('_', ' ', $column));
    }

    public function title(): string {
        return substr($this->title, 0, 31);
    }

    public function styles(Worksheet $sheet): array {
        $lastColumn = $this->getLastColumn();
        $lastRow = count($this->data) + 1;

        // Header styles
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Data cells with borders
        if ($lastRow > 1) {
            $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D9D9D9'],
                    ],
                ],
            ]);

            // Alternating row colors
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2'],
                        ],
                    ]);
                }
            }
        }

        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    protected function getLastColumn(): string {
        $columnCount = count($this->getColumns());

        if ($columnCount <= 26) {
            return chr(64 + $columnCount);
        }

        $firstLetter = chr(64 + floor(($columnCount - 1) / 26));
        $secondLetter = chr(65 + (($columnCount - 1) % 26));

        return $firstLetter . $secondLetter;
    }
}
