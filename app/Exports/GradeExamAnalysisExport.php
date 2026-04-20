<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GradeExamAnalysisExport implements 
    FromArray, 
    WithEvents, 
    WithStyles, 
    WithTitle, 
    WithCustomStartCell, 
    WithColumnWidths,
    ShouldAutoSize
{
    protected $analysisData;
    protected $gradeName;
    protected $possibleGrades;
    protected $isJunior;
    protected $lastColumnLetter;

    public function __construct(array $analysisData, string $gradeName, array $possibleGrades)
    {
        $this->analysisData = $analysisData;
        $this->gradeName = $gradeName;
        $this->possibleGrades = $possibleGrades;
        $this->isJunior = isset($analysisData[0]['is_junior']) ? $analysisData[0]['is_junior'] : false;
        // Set last column letter dynamically based on mode
        $numColumns = $this->isJunior ? 3 + 6 + 3 : 3 + 7 + 4; // fixed columns + grades + % columns
        $this->lastColumnLetter = Coordinate::stringFromColumnIndex($numColumns);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return $this->gradeName . ' Exam Analysis';
    }

    public function columnWidths(): array
    {
        // Adjust these widths for a professional, readable sheet
        return [
            'A' => 28, // Subject
            'B' => 12, // No. Enrolled
            'C' => 10, // No Score
            'D' => 16, // Avg Exam Score
            // Grades, percentages, and summary columns get 9-10
            'E' => 9, 'F' => 9, 'G' => 9, 'H' => 9, 'I' => 9, 'J' => 9, 'K' => 9, 'L' => 10, 'M' => 10, 'N' => 10, 'O' => 10, 'P' => 10, 'Q' => 10,
            // Extend as needed if you add more columns for senior
        ];
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [$this->gradeName . ' - Exam Analysis'];

        foreach ($this->analysisData as $termData) {
            $rows[] = [$termData['term_year']];
            
            // Build header
            $headers = ['Subject', 'No. Students (Enrolled)', 'No Score', 'Avg. Exam Score (%)'];
            if ($termData['is_junior'] ?? false) {
                foreach (['A','B','C','D','E','U'] as $g) $headers[] = $g;
                $headers[] = 'AB%';
                $headers[] = 'ABC%';
                $headers[] = 'DEU%';
            } else {
                foreach (['A*','A','B','C','D','E','U'] as $g) $headers[] = $g;
                $headers[] = 'A*%';
                $headers[] = 'A*AB%';
                $headers[] = 'A*ABC%';
                $headers[] = 'DEU%';
            }
            $rows[] = $headers;

            foreach ($termData['subjects_data'] as $subjectData) {
                $row = [
                    $subjectData['subject_name'],
                    $subjectData['student_count'],
                    $subjectData['no_score_count'] ?? 0,
                    $subjectData['average_percentage'] . '%',
                ];
                if ($termData['is_junior'] ?? false) {
                    foreach (['A','B','C','D','E','U'] as $g)
                        $row[] = $subjectData['grade_counts'][$g] ?? 0;
                    $row[] = $subjectData['ab_percent'] ?? 0 . '%';
                    $row[] = $subjectData['abc_percent'] ?? 0 . '%';
                    $row[] = $subjectData['deu_percent'] ?? 0 . '%';
                } else {
                    foreach (['A*','A','B','C','D','E','U'] as $g)
                        $row[] = $subjectData['grade_counts'][$g] ?? 0;
                    $row[] = $subjectData['a_star_percent'] ?? 0 . '%';
                    $row[] = $subjectData['a_star_ab_percent'] ?? 0 . '%';
                    $row[] = $subjectData['a_star_abc_percent'] ?? 0 . '%';
                    $row[] = $subjectData['deu_percent'] ?? 0 . '%';
                }
                $rows[] = $row;
            }

            // Totals row
            $totals = $termData['term_totals'] ?? [];
            $row = [
                'TOTAL',
                $totals['student_count'] ?? 0,
                $totals['no_score_count'] ?? 0,
                ($totals['average_percentage'] ?? 0) . '%',
            ];
            if ($termData['is_junior'] ?? false) {
                foreach (['A','B','C','D','E','U'] as $g)
                    $row[] = $totals['grade_counts'][$g] ?? 0;
                $row[] = $totals['ab_percent'] ?? 0 . '%';
                $row[] = $totals['abc_percent'] ?? 0 . '%';
                $row[] = $totals['deu_percent'] ?? 0 . '%';
            } else {
                foreach (['A*','A','B','C','D','E','U'] as $g)
                    $row[] = $totals['grade_counts'][$g] ?? 0;
                $row[] = $totals['a_star_percent'] ?? 0 . '%';
                $row[] = $totals['a_star_ab_percent'] ?? 0 . '%';
                $row[] = $totals['a_star_abc_percent'] ?? 0 . '%';
                $row[] = $totals['deu_percent'] ?? 0 . '%';
            }
            $rows[] = $row;
            $rows[] = [''];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Title style
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 13,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $row = 1;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Style title
                $sheet->mergeCells("A{$row}:{$highestColumn}{$row}");
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                $row++;

                // Loop through sheet and style headers/totals
                for ($r = 2; $r <= $highestRow; $r++) {
                    $valA = $sheet->getCell("A{$r}")->getValue();

                    if ($valA && stripos($valA, 'Term ') === 0) {
                        $sheet->mergeCells("A{$r}:{$highestColumn}{$r}");
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E9ECEF'],
                            ],
                        ]);
                        continue;
                    }

                    if ($valA === 'Subject' || $valA === 'TOTAL') {
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => ($valA === 'TOTAL' ? 'E9ECEF' : 'F8F9FA')],
                            ],
                        ]);
                        if ($valA === 'Subject') {
                            // Center all headers
                            $sheet->getStyle("A{$r}:{$highestColumn}{$r}")
                                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                        continue;
                    }

                    // Alternate background for data rows
                    if (($r % 2) === 1) {
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FAFAFA');
                    }
                }

                // Borders for all cells
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'B0BEC5'],
                        ],
                    ],
                ]);
            }
        ];
    }
}
