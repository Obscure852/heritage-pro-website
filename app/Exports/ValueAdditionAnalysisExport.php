<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Collection;

class ValueAdditionAnalysisExport implements WithTitle, WithStyles, WithColumnWidths, WithCustomStartCell{
    protected $data;
    protected $colors;

    public function __construct(array $data){
        $this->data = $data;
        
        $this->colors = [
            'header' => 'F2F2F2',
            'subheader' => 'E9ECEF',
            'success' => 'D4EDDA',
            'danger' => 'F8D7DA',
            'warning' => 'FFF3CD',
            'info' => 'FFEEBA',
            'light' => 'F8F9FA',
            'psle_header' => 'F2F4F6',
            'jc_header' => 'E9ECEF',
            'grade_a' => 'D4EDDA',
            'grade_b' => 'FFF3CD',
            'grade_c' => 'FFEEBA',
            'grade_deu' => 'F8D7DA',
        ];
    }

    public function collection(){
        return new Collection([]);
    }

    public function title(): string{
        $klassName = $this->data['klass']->name ?? '';
        $type = $this->data['type'] ?? '';
        return "Value Addition - {$klassName} - {$type}";
    }

    public function startCell(): string{
        return 'A1';
    }

    public function columnWidths(): array{
        $widths = [
            'A' => 20,
            'B' => 12,
        ];
        
        $col = 'C';
        foreach ($this->data['jcSubjects'] as $subject) {
            $widths[$col++] = 10;
            $widths[$col++] = 10;
        }
        
        return $widths;
    }

    public function styles(Worksheet $sheet){
        $this->applyHeaderAndSchoolInfo($sheet);
        $currentRow = $this->createSubjectGradeDistributionTable($sheet, 9);
        $currentRow = $this->createGradeShiftMatrix($sheet, $currentRow + 2);
        $currentRow = $this->createHighPsleAchieversTable($sheet, $currentRow + 2);
        $currentRow = $this->createPsleOverallGradeDistribution($sheet, $currentRow + 2);
        $currentRow = $this->createJcOverallGradeDistribution($sheet, $currentRow + 2);
        $currentRow = $this->createValueAdditionSummary($sheet, $currentRow + 2);
        
        $sheet->getStyle('A1:Z500')->getFont()->setName('Arial');
        $sheet->getStyle('A9:Z500')->getFont()->setSize(10);
        
        return $sheet;
    }
    
    private function applyHeaderAndSchoolInfo(Worksheet $sheet){
        $klass = $this->data['klass'];
        $term = $this->data['term'];
        $type = $this->data['type'];
        $sequenceId = $this->data['sequenceId'] ?? null;
    
        $sheet->mergeCells('A6:F6');
        $sheet->setCellValue('A6', 'Value Addition Analysis');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
        
        $sheet->mergeCells('A7:F7');
        $termInfo = ($term->term ?? 'N/A') . ', ' . ($term->year ?? 'N/A');
        $sequenceText = $sequenceId ? " (Sequence: {$sequenceId})" : "";
        $sheet->setCellValue('A7', "Class: {$klass->name} | Assessment: {$type} | Term: {$termInfo}{$sequenceText}");
        
        $sheet->getStyle('A6:F7')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    private function createSubjectGradeDistributionTable(Worksheet $sheet, int $startRow): int{
        $jcSubjects = $this->data['jcSubjects'];
        $psleGradeCategories = $this->data['psleGradeCategories'];
        $gradeCategories = $this->data['gradeCategories'];
        $gradeCounts = $this->data['gradeCounts'];
        $rankedSubjects = $this->data['rankedSubjects'];
        
        $sheet->mergeCells("A{$startRow}:F{$startRow}");
        $sheet->setCellValue("A{$startRow}", "Subject Grade Distribution (PSLE vs JC)");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $startRow++;
        
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "Grade");
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            $sheet->mergeCells("{$firstCol}{$startRow}:{$secondCol}{$startRow}");
            $sheet->setCellValue("{$firstCol}{$startRow}", $subject);
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        
        $startRow++;
        $colIndex = 2;
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            $sheet->setCellValue("{$firstCol}{$startRow}", "PSLE");
            $sheet->setCellValue("{$secondCol}{$startRow}", "JC");
            $sheet->getStyle("{$firstCol}{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($this->colors['psle_header']);
            $sheet->getStyle("{$secondCol}{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($this->colors['jc_header']);
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        
        $startRow++;
        $rowStart = $startRow;
        
        foreach ($psleGradeCategories as $grade) {
            $colIndex = 2;
            $sheet->setCellValue("A{$startRow}", $grade);
            
            foreach ($jcSubjects as $subject) {
                $firstCol = $this->getColLetter($colIndex);
                $secondCol = $this->getColLetter($colIndex + 1);
                $psleCount = $gradeCounts[$subject]['PSLE'][$grade] ?? 0;
                $jcCount = $gradeCounts[$subject]['JC'][$grade] ?? 0;
                
                $sheet->setCellValue("{$firstCol}{$startRow}", $psleCount);
                $sheet->setCellValue("{$secondCol}{$startRow}", $jcCount);
                $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $colIndex += 2;
            }
            $startRow++;
        }
        
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "M (JC Only)");
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            
            $sheet->setCellValue("{$firstCol}{$startRow}", "-");
            $sheet->setCellValue("{$secondCol}{$startRow}", $gradeCounts[$subject]['JC']['M'] ?? 0);
            
            $sheet->getStyle("{$firstCol}{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($this->colors['light']);
                
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        $startRow++;

        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "Total");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$startRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['light']);
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            
            $psleTotalForSubject = 0;
            foreach ($psleGradeCategories as $grade) {
                $psleTotalForSubject += $gradeCounts[$subject]['PSLE'][$grade] ?? 0;
            }
            
            $jcTotalForSubject = 0;
            foreach ($psleGradeCategories as $grade) {
                $jcTotalForSubject += $gradeCounts[$subject]['JC'][$grade] ?? 0;
            }
            $jcTotalForSubject += $gradeCounts[$subject]['JC']['M'] ?? 0;
            
            $sheet->setCellValue("{$firstCol}{$startRow}", $psleTotalForSubject);
            $sheet->setCellValue("{$secondCol}{$startRow}", $jcTotalForSubject);
            
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getFont()->setBold(true);
            
            $colIndex += 2;
        }
        $startRow++;
        
        // Add Enrolled row
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "Enrolled");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            
            $psleEnrolled = $gradeCounts[$subject]['enrolled']['PSLE'] ?? 0;
            $jcEnrolled = $gradeCounts[$subject]['enrolled']['JC'] ?? 0;
            
            $sheet->setCellValue("{$firstCol}{$startRow}", $psleEnrolled);
            $sheet->setCellValue("{$secondCol}{$startRow}", $jcEnrolled);
            
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        $startRow++;
        
        // Add No Scores row
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "No Scores");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            
            $psleNoScores = $gradeCounts[$subject]['no_scores']['PSLE'] ?? 0;
            $jcNoScores = $gradeCounts[$subject]['no_scores']['JC'] ?? 0;
            
            $sheet->setCellValue("{$firstCol}{$startRow}", $psleNoScores);
            $sheet->setCellValue("{$secondCol}{$startRow}", $jcNoScores);
            
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        $startRow++;
        
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "Quality % (PSLE: A-C / JC: M-C)");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            
            $sheet->setCellValue("{$firstCol}{$startRow}", $gradeCounts[$subject]['qualityPSLE'] . '%');
            $sheet->setCellValue("{$secondCol}{$startRow}", $gradeCounts[$subject]['qualityJC'] . '%');
            
            $sheet->getStyle("{$firstCol}{$startRow}:{$secondCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        $startRow++;
        
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "Value Add (Qual. %)");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$startRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['header']);
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            $valueAddition = $gradeCounts[$subject]['valueAddition'];
            
            $sheet->mergeCells("{$firstCol}{$startRow}:{$secondCol}{$startRow}");
            $sheet->setCellValue("{$firstCol}{$startRow}", $valueAddition . '%');
            
            $fillColor = $valueAddition >= 0 ? $this->colors['success'] : $this->colors['danger'];
            $textColor = $valueAddition >= 0 ? '155724' : '721c24';
            
            $sheet->getStyle("{$firstCol}{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($fillColor);
                
            $sheet->getStyle("{$firstCol}{$startRow}")->getFont()
                ->setColor(new Color($textColor));
                
            $sheet->getStyle("{$firstCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        $startRow++;
        
        $colIndex = 2;
        $sheet->setCellValue("A{$startRow}", "Rank");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        
        foreach ($jcSubjects as $subject) {
            $firstCol = $this->getColLetter($colIndex);
            $secondCol = $this->getColLetter($colIndex + 1);
            $rank = array_search($subject, $rankedSubjects) !== false 
                ? array_search($subject, $rankedSubjects) + 1 
                : '-';
            
            $sheet->mergeCells("{$firstCol}{$startRow}:{$secondCol}{$startRow}");
            $sheet->setCellValue("{$firstCol}{$startRow}", $rank);
            $sheet->getStyle("{$firstCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex += 2;
        }
        
        $lastCol = $this->getColLetter($colIndex - 1);
        $sheet->getStyle("A" . ($rowStart - 2) . ":{$lastCol}{$startRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
            
        $sheet->getStyle("A" . ($rowStart - 2) . ":{$lastCol}" . ($rowStart - 1))->getFont()->setBold(true);
        
        return $startRow;
    }
    
    private function createGradeShiftMatrix(Worksheet $sheet, int $startRow): int{
        $psleGradeCategories = $this->data['psleGradeCategories'];
        $gradeCategories = $this->data['gradeCategories'];
        $gradeShiftMatrix = $this->data['gradeShiftMatrix'];
        $psleOverallGradeCounts = $this->data['psleOverallGradeCounts'];
        

        $lastColIndex = 3 + count($psleGradeCategories);
        $lastColLetter = $this->getColLetter($lastColIndex);
        
        $sheet->mergeCells("A{$startRow}:{$lastColLetter}{$startRow}");
        $sheet->setCellValue("A{$startRow}", "Overall Grade Shift Matrix (PSLE Overall vs JC Overall)");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $startRow++;
        
        $sheet->mergeCells("A{$startRow}:B{$startRow}");
        $sheet->setCellValue("A{$startRow}", "PSLE Overall → / JC Overall ↓");
        $sheet->getStyle("A{$startRow}:B{$startRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['light']);
        
        $colIndexStart = 3;
        $colIndex = $colIndexStart;
        
        $firstCol = $this->getColLetter($colIndex);
        $lastCol = $this->getColLetter($colIndex + count($psleGradeCategories) - 1);
        $sheet->mergeCells("{$firstCol}{$startRow}:{$lastCol}{$startRow}");
        $sheet->setCellValue("{$firstCol}{$startRow}", "PSLE Overall Grade");
        $sheet->getStyle("{$firstCol}{$startRow}:{$lastCol}{$startRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['jc_header']);
        $sheet->getStyle("{$firstCol}{$startRow}:{$lastCol}{$startRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
        $totalCol = $this->getColLetter($colIndex + count($psleGradeCategories));
        $sheet->setCellValue("{$totalCol}{$startRow}", "Total from JC");
        $sheet->getStyle("{$totalCol}{$startRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['light']);
        $sheet->getStyle("{$totalCol}{$startRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $startRow++;
        $colIndex = $colIndexStart;
        
        foreach ($psleGradeCategories as $psleGrade) {
            $col = $this->getColLetter($colIndex);
            $sheet->setCellValue("{$col}{$startRow}", $psleGrade);
            $sheet->getStyle("{$col}{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($this->colors['psle_header']);
            $sheet->getStyle("{$col}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex++;
        }
        
        $startRow++;
        $rowStart = $startRow;
        $jcRowTotals = array_fill_keys($gradeCategories, 0);
        
        $sheet->mergeCells("A{$startRow}:A" . ($startRow + count($gradeCategories) - 1));
        $sheet->getStyle("A{$startRow}:A" . ($startRow + count($gradeCategories) - 1))->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['jc_header']);
        $sheet->setCellValue("A{$startRow}", "JC Overall Grade");
        $sheet->getStyle("A{$startRow}")->getAlignment()
            ->setTextRotation(90)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        
        foreach ($gradeCategories as $jcGrade) {
            $colIndex = $colIndexStart;
            $sheet->setCellValue("B{$startRow}", $jcGrade);
            $sheet->getStyle("B{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($this->colors['psle_header']);
            $sheet->getStyle("B{$startRow}")->getFont()->setBold(true);
            $sheet->getStyle("B{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            foreach ($psleGradeCategories as $psleGrade) {
                $col = $this->getColLetter($colIndex);
                $count = $gradeShiftMatrix[$psleGrade][$jcGrade] ?? 0;
                $sheet->setCellValue("{$col}{$startRow}", $count);
                $sheet->getStyle("{$col}{$startRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $jcRowTotals[$jcGrade] += $count;
                $colIndex++;
            }
            
            $totalCol = $this->getColLetter($colIndex);
            $sheet->setCellValue("{$totalCol}{$startRow}", $jcRowTotals[$jcGrade]);
            $sheet->getStyle("{$totalCol}{$startRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($this->colors['light']);
            $sheet->getStyle("{$totalCol}{$startRow}")->getFont()->setBold(true);
            $sheet->getStyle("{$totalCol}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
            $startRow++;
        }
        
        $sheet->mergeCells("A{$startRow}:B{$startRow}");
        $sheet->setCellValue("A{$startRow}", "Total from PSLE");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$startRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($this->colors['light']);
        $sheet->getStyle("A{$startRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $colIndex = $colIndexStart;
        foreach ($psleGradeCategories as $psleGrade) {
            $col = $this->getColLetter($colIndex);
            $sheet->setCellValue("{$col}{$startRow}", $psleOverallGradeCounts[$psleGrade] ?? 0);
            $sheet->getStyle("{$col}{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $colIndex++;
        }
        
        $totalCol = $this->getColLetter($colIndex);
        $sheet->setCellValue("{$totalCol}{$startRow}", array_sum($psleOverallGradeCounts));
        $sheet->getStyle("{$totalCol}{$startRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $lastCol = $this->getColLetter($colIndex);
        $sheet->getStyle("A" . ($rowStart - 2) . ":{$lastCol}{$startRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
            
        $sheet->getStyle("A" . ($rowStart - 2) . ":{$lastCol}" . ($rowStart - 1))->getFont()->setBold(true);
        
        return $startRow;
    }
    
    private function createHighPsleAchieversTable(Worksheet $sheet, int $startRow): int{
        $highPsleAchievers = $this->data['highPsleAchievers'];
        $type = $this->data['type'];
        
        $sheet->mergeCells("A{$startRow}:D{$startRow}");
        $sheet->setCellValue("A{$startRow}", "Progression of High PSLE Achievers (Overall Grades A, B, or C in PSLE)");
        $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
        $startRow++;
        
        $sheet->setCellValue("A{$startRow}", "#");
        $sheet->setCellValue("B{$startRow}", "Student Name");
        $sheet->setCellValue("C{$startRow}", "PSLE Overall Grade");
        $sheet->setCellValue("D{$startRow}", "JC Overall Grade ({$type})");
        
        $sheet->getStyle("A{$startRow}:D{$startRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$startRow}:D{$startRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $startRow++;
        $rowStart = $startRow;
        
        if (count($highPsleAchievers) > 0) {
            foreach ($highPsleAchievers as $index => $achiever) {
                $sheet->setCellValue("A{$startRow}", $index + 1);
                $sheet->setCellValue("B{$startRow}", $achiever['name']);
                $sheet->setCellValue("C{$startRow}", $achiever['psle_grade']);
                $sheet->setCellValue("D{$startRow}", $achiever['jc_grade']);
                
                $sheet->getStyle("A{$startRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$startRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$startRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $psleFillColor = $this->getPsleGradeFillColor($achiever['psle_grade']);
                $sheet->getStyle("C{$startRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($psleFillColor);
                
                $jcFillColor = $this->getJcGradeFillColor($achiever['jc_grade']);
                $sheet->getStyle("D{$startRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($jcFillColor);
                
                $startRow++;
            }
        } else {
            $sheet->mergeCells("A{$startRow}:D{$startRow}");
            $sheet->setCellValue("A{$startRow}", "No students found with PSLE overall grades A, B, or C in this class.");
            $sheet->getStyle("A{$startRow}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $startRow++;
        }
        
        $sheet->getStyle("A" . ($rowStart - 1) . ":D" . ($startRow - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        
        return $startRow;
    }

   private function createPsleOverallGradeDistribution(Worksheet $sheet, int $startRow): int{
       $psleGradeCategories = $this->data['psleGradeCategories'];
       $psleOverallGradeCounts = $this->data['psleOverallGradeCounts'];
       
       $sheet->mergeCells("A{$startRow}:J{$startRow}");
       $sheet->setCellValue("A{$startRow}", "PSLE Overall Grade Distribution (Cohort)");
       $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
       $startRow++;
       
       $colIndex = 1;
       $headerRow = $startRow;
       
       foreach ($psleGradeCategories as $grade) {
           $col = $this->getColLetter($colIndex);
           $sheet->setCellValue("{$col}{$startRow}", $grade);
           $sheet->getStyle("{$col}{$startRow}")->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_CENTER);
           $colIndex++;
       }
       
       $totalCol = $this->getColLetter($colIndex++);
       $abCol = $this->getColLetter($colIndex++);
       $abcCol = $this->getColLetter($colIndex++);
       $deuCol = $this->getColLetter($colIndex++);
       
       $sheet->setCellValue("{$totalCol}{$startRow}", "Total");
       $sheet->setCellValue("{$abCol}{$startRow}", "AB%");
       $sheet->setCellValue("{$abcCol}{$startRow}", "ABC%");
       $sheet->setCellValue("{$deuCol}{$startRow}", "DEU%");
       
       $sheet->getStyle("A{$startRow}:{$deuCol}{$startRow}")->getFont()->setBold(true);
       $sheet->getStyle("A{$startRow}:{$deuCol}{$startRow}")->getAlignment()
           ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       
       $startRow++;
       $dataRow = $startRow;
       $colIndex = 1;
       
       $totalPSLE = array_sum(array_map(function($g) { return $g ?? 0; }, $psleOverallGradeCounts));
       $psleAB = (($psleOverallGradeCounts['A'] ?? 0) + 
                 ($psleOverallGradeCounts['B'] ?? 0)) / max($totalPSLE, 1) * 100;
       $psleABC = (($psleOverallGradeCounts['A'] ?? 0) + 
                  ($psleOverallGradeCounts['B'] ?? 0) + 
                  ($psleOverallGradeCounts['C'] ?? 0)) / max($totalPSLE, 1) * 100;
       $psleDEU = (($psleOverallGradeCounts['D'] ?? 0) + 
                  ($psleOverallGradeCounts['E'] ?? 0) + 
                  ($psleOverallGradeCounts['U'] ?? 0)) / max($totalPSLE, 1) * 100;
       
       foreach ($psleGradeCategories as $grade) {
           $col = $this->getColLetter($colIndex++);
           $sheet->setCellValue("{$col}{$startRow}", $psleOverallGradeCounts[$grade] ?? 0);
           $sheet->getStyle("{$col}{$startRow}")->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       }
       
       $totalCol = $this->getColLetter($colIndex++);
       $abCol = $this->getColLetter($colIndex++);
       $abcCol = $this->getColLetter($colIndex++);
       $deuCol = $this->getColLetter($colIndex++);
       
       $sheet->setCellValue("{$totalCol}{$startRow}", $totalPSLE);
       $sheet->setCellValue("{$abCol}{$startRow}", round($psleAB, 1) . '%');
       $sheet->setCellValue("{$abcCol}{$startRow}", round($psleABC, 1) . '%');
       $sheet->setCellValue("{$deuCol}{$startRow}", round($psleDEU, 1) . '%');
       
       $sheet->getStyle("A{$startRow}:{$deuCol}{$startRow}")->getAlignment()
           ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       
       $sheet->getStyle("A{$headerRow}:{$deuCol}{$dataRow}")->getBorders()->getAllBorders()
           ->setBorderStyle(Border::BORDER_THIN);
       
       return $startRow;
   }
   
   private function createJcOverallGradeDistribution(Worksheet $sheet, int $startRow): int{
       $gradeCategories = $this->data['gradeCategories'];
       $jcOverallGradeCounts = $this->data['jcOverallGradeCounts'];
       $type = $this->data['type'];
       
       $sheet->mergeCells("A{$startRow}:L{$startRow}");
       $sheet->setCellValue("A{$startRow}", "JC Overall Grade Distribution (Cohort for {$type})");
       $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
       $startRow++;
       
       $colIndex = 1;
       $headerRow = $startRow;
       
       foreach ($gradeCategories as $grade) {
           $col = $this->getColLetter($colIndex);
           $sheet->setCellValue("{$col}{$startRow}", $grade);
           $sheet->getStyle("{$col}{$startRow}")->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_CENTER);
           $colIndex++;
       }
       
       $totalCol = $this->getColLetter($colIndex++);
       $mabCol = $this->getColLetter($colIndex++);
       $mabcCol = $this->getColLetter($colIndex++);
       $deuCol = $this->getColLetter($colIndex++);
       
       $sheet->setCellValue("{$totalCol}{$startRow}", "Total");
       $sheet->setCellValue("{$mabCol}{$startRow}", "MAB%");
       $sheet->setCellValue("{$mabcCol}{$startRow}", "MABC%");
       $sheet->setCellValue("{$deuCol}{$startRow}", "DEU%");
       
       $sheet->getStyle("A{$startRow}:{$deuCol}{$startRow}")->getFont()->setBold(true);
       $sheet->getStyle("A{$startRow}:{$deuCol}{$startRow}")->getAlignment()
           ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       
       $startRow++;
       $dataRow = $startRow;
       $colIndex = 1;
       
       $totalJC = array_sum(array_map(function($g) { return $g ?? 0; }, $jcOverallGradeCounts));
       $jcMAB = (($jcOverallGradeCounts['M'] ?? 0) + 
                ($jcOverallGradeCounts['A'] ?? 0) + 
                ($jcOverallGradeCounts['B'] ?? 0)) / max($totalJC, 1) * 100;
       $jcMABC = (($jcOverallGradeCounts['M'] ?? 0) + 
                 ($jcOverallGradeCounts['A'] ?? 0) + 
                 ($jcOverallGradeCounts['B'] ?? 0) + 
                 ($jcOverallGradeCounts['C'] ?? 0)) / max($totalJC, 1) * 100;
       $jcDEU = (($jcOverallGradeCounts['D'] ?? 0) + 
                ($jcOverallGradeCounts['E'] ?? 0) + 
                ($jcOverallGradeCounts['U'] ?? 0)) / max($totalJC, 1) * 100;
       
       foreach ($gradeCategories as $grade) {
           $col = $this->getColLetter($colIndex++);
           $sheet->setCellValue("{$col}{$startRow}", $jcOverallGradeCounts[$grade] ?? 0);
           $sheet->getStyle("{$col}{$startRow}")->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       }
       
       $totalCol = $this->getColLetter($colIndex++);
       $mabCol = $this->getColLetter($colIndex++);
       $mabcCol = $this->getColLetter($colIndex++);
       $deuCol = $this->getColLetter($colIndex++);
       
       $sheet->setCellValue("{$totalCol}{$startRow}", $totalJC);
       $sheet->setCellValue("{$mabCol}{$startRow}", round($jcMAB, 1) . '%');
       $sheet->setCellValue("{$mabcCol}{$startRow}", round($jcMABC, 1) . '%');
       $sheet->setCellValue("{$deuCol}{$startRow}", round($jcDEU, 1) . '%');
       
       $sheet->getStyle("A{$startRow}:{$deuCol}{$startRow}")->getAlignment()
           ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       
       $sheet->getStyle("A{$headerRow}:{$deuCol}{$dataRow}")->getBorders()->getAllBorders()
           ->setBorderStyle(Border::BORDER_THIN);
       
       return $startRow;
   }
   
   private function createValueAdditionSummary(Worksheet $sheet, int $startRow): int{
       $valueAdditions = $this->data['valueAdditions'];
       
       $sheet->mergeCells("A{$startRow}:D{$startRow}");
       $sheet->setCellValue("A{$startRow}", "Value Addition Summary");
       $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
       $startRow++;
       
       $sheet->setCellValue("A{$startRow}", "Overall Value Addition (Based on Quality % M+A+B+C for JC vs A+B+C for PSLE):");
       $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
       
       $overallValue = number_format($valueAdditions['overall'] ?? 0, 0);
       $sheet->setCellValue("B{$startRow}", $overallValue . '%');
       $sheet->getStyle("B{$startRow}")->getAlignment()
           ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       
       $fillColor = $overallValue >= 0 ? $this->colors['success'] : $this->colors['danger'];
       $textColor = $overallValue >= 0 ? '155724' : '721c24';
       
       $sheet->getStyle("B{$startRow}")->getFill()
           ->setFillType(Fill::FILL_SOLID)
           ->getStartColor()->setRGB($fillColor);
           
       $sheet->getStyle("B{$startRow}")->getFont()
           ->setColor(new Color($textColor))
           ->setBold(true);
       
       $sheet->getStyle("A{$startRow}:B{$startRow}")->getBorders()->getAllBorders()
           ->setBorderStyle(Border::BORDER_THIN);
       
       $startRow += 2;
       $sheet->mergeCells("A{$startRow}:C{$startRow}");
       $sheet->setCellValue("A{$startRow}", "Value Addition by Subject (Quality %)");
       $sheet->getStyle("A{$startRow}")->getFont()->setBold(true);
       $startRow++;
       
       $sheet->setCellValue("A{$startRow}", "Subject");
       $sheet->setCellValue("B{$startRow}", "Value Addition");
       $sheet->setCellValue("C{$startRow}", "Rank");
       
       $sheet->getStyle("A{$startRow}:C{$startRow}")->getFont()->setBold(true);
       $sheet->getStyle("A{$startRow}:C{$startRow}")->getAlignment()
           ->setHorizontal(Alignment::HORIZONTAL_CENTER);
       
       $rankedSubjects = $this->data['rankedSubjects'];
       $startRow++;
       $rowStart = $startRow;
       
       foreach ($rankedSubjects as $index => $subject) {
           $valueAddition = $valueAdditions[$subject];
           
           $sheet->setCellValue("A{$startRow}", $subject);
           $sheet->setCellValue("B{$startRow}", $valueAddition . '%');
           $sheet->setCellValue("C{$startRow}", $index + 1);
           
           $sheet->getStyle("A{$startRow}:C{$startRow}")->getAlignment()
               ->setHorizontal(Alignment::HORIZONTAL_CENTER);
               
           $fillColor = $valueAddition >= 0 ? $this->colors['success'] : $this->colors['danger'];
           $textColor = $valueAddition >= 0 ? '155724' : '721c24';
           
           $sheet->getStyle("B{$startRow}")->getFill()
               ->setFillType(Fill::FILL_SOLID)
               ->getStartColor()->setRGB($fillColor);
               
           $sheet->getStyle("B{$startRow}")->getFont()
               ->setColor(new Color($textColor));
               
           $startRow++;
       }
       
       $sheet->getStyle("A" . ($rowStart - 1) . ":C" . ($startRow - 1))->getBorders()->getAllBorders()
           ->setBorderStyle(Border::BORDER_THIN);
       
       return $startRow;
   }
   
   private function getPsleGradeFillColor(string $grade): string{
       switch ($grade) {
           case 'A':
               return $this->colors['grade_a'];
           case 'B':
               return $this->colors['grade_b'];
           case 'C':
               return $this->colors['grade_c'];
           default:
               return $this->colors['grade_deu'];
       }
   }
   
   private function getJcGradeFillColor(string $grade): string{
       switch ($grade) {
           case 'M':
           case 'A':
               return $this->colors['grade_a'];
           case 'B':
               return $this->colors['grade_b'];
           case 'C':
               return $this->colors['grade_c'];
           default:
               return $this->colors['grade_deu'];
       }
   }
   

   private function getColLetter($colIndex){
       if ($colIndex <= 0) {
           return 'A';
       }
       
       $dividend = $colIndex;
       $columnName = '';
       
       while ($dividend > 0) {
           $modulo = ($dividend - 1) % 26;
           $columnName = chr(65 + $modulo) . $columnName;
           $dividend = (int)(($dividend - $modulo) / 26);
       }
       
       return $columnName;
   }
}