<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class GradeValueAnalysisExport implements WithTitle, WithStyles, WithColumnWidths, WithCustomStartCell
{
    protected array $data;
    protected array $colors;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->colors = [
            'header'      => 'F2F2F2',
            'psle_header' => 'F2F4F6',
            'jc_header'   => 'E9ECEF',
            'light'       => 'F8F9FA',
            'grade_a'     => 'D4EDDA',
            'grade_b'     => 'FFF3CD',
            'grade_c'     => 'FFEEBA',
            'grade_deu'   => 'F8D7DA',
        ];
    }

    public function title(): string
    {
        $gradeName = $this->data['grade']->name ?? '';
        $type      = $this->data['type'] ?? '';
        return "Value Addition - Grade {$gradeName} - {$type}";
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function columnWidths(): array
    {
        $widths = ['A'=>12,'B'=>30,'C'=>12,'D'=>12];
        $col = 'E';
        foreach ($this->data['jcSubjects'] as $sub) {
            $widths[$col++] = 12;
            $widths[$col++] = 12;
        }
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $this->applyHeader($sheet);
        $row = 8;
        $row = $this->createSubjectDistribution($sheet, $row);
        $row += 2;
        $row = $this->createGradeShiftMatrix($sheet, $row);
        $row += 2;
        $row = $this->createHighAchievers($sheet, $row);
        $row += 2;
        $row = $this->createPsleDistribution($sheet, $row);
        $row += 2;
        $row = $this->createJcDistribution($sheet, $row);
        $row += 2;
        $row = $this->createValueSummary($sheet, $row);
        $sheet->getStyle('A1:Z500')->getFont()->setName('Arial')->setSize(10);
        return $sheet;
    }

    private function applyHeader(Worksheet $sheet)
    {
        $grade    = $this->data['grade'];
        $term     = $this->data['term'];
        $type     = $this->data['type'];
        $seq      = $this->data['sequenceId'] ?? null;
        $termInfo = ($term->term ?? 'N/A') . ' ' . ($term->year ?? 'N/A');
        $seqText  = $seq ? " (Seq: {$seq})" : '';

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'Value Addition Analysis');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A4:D4');
        $sheet->setCellValue('A4', "Grade: {$grade->name} | Assessment: {$type} | Term: {$termInfo}{$seqText}");
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function createSubjectDistribution(Worksheet $sheet, int $r): int{
        $subs     = $this->data['jcSubjects'];
        $psleCats = $this->data['psleGradeCategories'];
        $cnts     = $this->data['gradeCounts'];

        $sheet->mergeCells("A{$r}:D{$r}");
        $sheet->setCellValue("A{$r}", 'Subject Grade Distribution (PSLE vs JC)');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        $r++;

        $sheet->setCellValue("A{$r}", 'Grade');
        $c = 2;
        foreach ($subs as $sub) {
            $c1 = $this->col($c++);
            $c2 = $this->col($c++);
            $sheet->mergeCells("{$c1}{$r}:{$c2}{$r}");
            $sheet->setCellValue("{$c1}{$r}", $sub);
            $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $r++;

        $c = 2;
        foreach ($subs as $sub) {
            $c1 = $this->col($c++);
            $c2 = $this->col($c++);
            $sheet->setCellValue("{$c1}{$r}", 'PSLE');
            $sheet->setCellValue("{$c2}{$r}", 'JC');
            $sheet->getStyle("{$c1}{$r}")->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($this->colors['psle_header']);
            $sheet->getStyle("{$c2}{$r}")->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($this->colors['jc_header']);
            $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $r++;

        foreach ($psleCats as $g) {
            $c = 2;
            $sheet->setCellValue("A{$r}", $g);
            foreach ($subs as $sub) {
                $c1 = $this->col($c++);
                $c2 = $this->col($c++);
                $sheet->setCellValue("{$c1}{$r}", $cnts[$sub]['PSLE'][$g] ?? 0);
                $sheet->setCellValue("{$c2}{$r}", $cnts[$sub]['JC'][$g] ?? 0);
                $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $r++;
        }

        $c = 2;
        $sheet->setCellValue("A{$r}", 'M (JC Only)');
        foreach ($subs as $sub) {
            $c1 = $this->col($c++);
            $c2 = $this->col($c++);
            $sheet->setCellValue("{$c1}{$r}", '-');
            $sheet->setCellValue("{$c2}{$r}", $cnts[$sub]['JC']['M'] ?? 0);
            $sheet->getStyle("{$c1}{$r}")
                  ->getFill()->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($this->colors['light']);
            $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $c = 2;
        $sheet->setCellValue("A{$r}", 'Total');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        foreach ($subs as $sub) {
            $c1 = $this->col($c++);
            $c2 = $this->col($c++);
            $sheet->setCellValue("{$c1}{$r}", $cnts[$sub]['totalPSLE']);
            $sheet->setCellValue("{$c2}{$r}", $cnts[$sub]['totalJC']);
            $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $lastCol = $this->col($c-1);
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E6F7FF');

        $r++;

        return $r;
    }

    private function createGradeShiftMatrix(Worksheet $sheet, int $r): int
    {
        $psleCats = $this->data['psleGradeCategories'];
        $jcCats   = $this->data['gradeCategories'];
        $matrix   = $this->data['gradeShiftMatrix'];

        // Title
        $sheet->mergeCells("A{$r}:D{$r}");
        $sheet->setCellValue("A{$r}", 'Overall Grade Shift Matrix');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        $r++;

        // Header row
        $sheet->mergeCells("A{$r}:B{$r}");
        $sheet->setCellValue("A{$r}", 'JC \ PSLE');
        $sheet->getStyle("A{$r}")
              ->getFill()->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setRGB($this->colors['light']);
        $c = 3;
        foreach ($psleCats as $cat) {
            $col = $this->col($c++);
            $sheet->setCellValue("{$col}{$r}", $cat);
            // Set center alignment
            $sheet->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // Set fill color separately
            $sheet->getStyle("{$col}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($this->colors['psle_header']);
        }
        $totalCol = $this->col($c);
        $sheet->setCellValue("{$totalCol}{$r}", 'Total');
        // Set fill color
        $sheet->getStyle("{$totalCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setRGB($this->colors['light']);
        // Set center alignment separately
        $sheet->getStyle("{$totalCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r++;

        // Data rows
        foreach ($jcCats as $jc) {
            $sheet->setCellValue("A{$r}", $jc);
            // Set fill color
            $sheet->getStyle("A{$r}")->getFill()->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB($this->colors['jc_header']);
            // Set center alignment separately
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col = 3;
            $rowTotal = 0;
            foreach ($psleCats as $psle) {
                $colLetter = $this->col($col++);
                $val = $matrix[$psle][$jc] ?? 0;
                $sheet->setCellValue("{$colLetter}{$r}", $val);
                $sheet->getStyle("{$colLetter}{$r}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $rowTotal += $val;
            }
            $sheet->setCellValue("{$totalCol}{$r}", $rowTotal);
            // Set bold font
            $sheet->getStyle("{$totalCol}{$r}")->getFont()->setBold(true);
            // Set center alignment separately
            $sheet->getStyle("{$totalCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }

        return $r;
    }

    private function createHighAchievers(Worksheet $sheet, int $r): int
    {
        $achievers = $this->data['highPsleAchievers'];
        $type      = $this->data['type'];

        $sheet->mergeCells("A{$r}:D{$r}");
        $sheet->setCellValue("A{$r}", 'Progression of High PSLE Achievers');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        $r++;

        // Header
        $sheet->setCellValue("A{$r}", '#');
        $sheet->setCellValue("B{$r}", 'Student Name');
        $sheet->setCellValue("C{$r}", 'PSLE Grade');
        $sheet->setCellValue("D{$r}", "JC Grade ({$type})");
        // Set bold font
        $sheet->getStyle("A{$r}:D{$r}")->getFont()->setBold(true);
        // Set center alignment separately
        $sheet->getStyle("A{$r}:D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $r++;

        // Data rows
        foreach ($achievers as $i => $a) {
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $a['name']);
            $sheet->setCellValue("C{$r}", $a['psle_grade']);
            $sheet->setCellValue("D{$r}", "{$a['jc_grade']} ({$a['jc_points']} pts)");
            $sheet->getStyle("A{$r}:D{$r}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;
        }

        return $r;
    }

    private function createPsleDistribution(Worksheet $sheet, int $r): int
{
    $cats = $this->data['psleGradeCategories'];
    $cnts = $this->data['psleOverallGradeCounts'];

    $sheet->mergeCells("A{$r}:D{$r}");
    $sheet->setCellValue("A{$r}", 'PSLE Overall Grade Distribution');
    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
    $r++;

    // Create headers
    $col = 1;
    foreach ($cats as $cat) {
        $cell = $this->col($col++) . $r;
        $sheet->setCellValue($cell, $cat);
        $sheet->getStyle($cell)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    // Add "Total", "A-C%", "D-U%" headers
    $sheet->setCellValue($this->col($col++) . $r, 'Total');
    $sheet->setCellValue($this->col($col++) . $r, 'A-C%');
    $sheet->setCellValue($this->col($col) . $r, 'DEU%');
    $sheet->getStyle("A{$r}:" . $this->col($col) . "{$r}")
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $r++;

    // Now create the data row with actual values
    $col = 1;
    $total = 0;
    foreach ($cats as $cat) {
        $count = $cnts[$cat] ?? 0;
        $total += $count;
        $cell = $this->col($col++) . $r;
        $sheet->setCellValue($cell, $count);
        $sheet->getStyle($cell)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    // Calculate percentages
    $total = max($total, 1); // Avoid division by zero
    $abc = ($cnts['A'] ?? 0) + ($cnts['B'] ?? 0) + ($cnts['C'] ?? 0);
    $abcPercent = round(($abc / $total) * 100, 0) . '%';
    $deu = ($cnts['D'] ?? 0) + ($cnts['E'] ?? 0) + ($cnts['U'] ?? 0);
    $deuPercent = round(($deu / $total) * 100, 0) . '%';
    
    // Add the totals and percentages
    $sheet->setCellValue($this->col($col++) . $r, $total);
    $sheet->setCellValue($this->col($col++) . $r, $abcPercent);
    $sheet->setCellValue($this->col($col) . $r, $deuPercent);
    $sheet->getStyle("A{$r}:" . $this->col($col) . "{$r}")
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $r++;

    return $r;
}

private function createJcDistribution(Worksheet $sheet, int $r): int
{
    $cats = $this->data['gradeCategories'];
    $cnts = $this->data['jcOverallGradeCounts'];

    $sheet->mergeCells("A{$r}:D{$r}");
    $sheet->setCellValue("A{$r}", 'JC Overall Grade Distribution');
    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
    $r++;

    // Create headers
    $col = 1;
    foreach ($cats as $cat) {
        $cell = $this->col($col++) . $r;
        $sheet->setCellValue($cell, $cat);
        $sheet->getStyle($cell)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    // Add "Total", "M-C%", "D-U%" headers
    $sheet->setCellValue($this->col($col++) . $r, 'Total');
    $sheet->setCellValue($this->col($col++) . $r, 'M-C%');
    $sheet->setCellValue($this->col($col) . $r, 'DEU%');
    $sheet->getStyle("A{$r}:" . $this->col($col) . "{$r}")
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $r++;

    // Now create the data row with actual values
    $col = 1;
    $total = 0;
    foreach ($cats as $cat) {
        $count = $cnts[$cat] ?? 0;
        $total += $count;
        $cell = $this->col($col++) . $r;
        $sheet->setCellValue($cell, $count);
        $sheet->getStyle($cell)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    // Calculate percentages
    $total = max($total, 1); // Avoid division by zero
    $mabc = ($cnts['M'] ?? 0) + ($cnts['A'] ?? 0) + ($cnts['B'] ?? 0) + ($cnts['C'] ?? 0);
    $mabcPercent = round(($mabc / $total) * 100, 0) . '%';
    $deu = ($cnts['D'] ?? 0) + ($cnts['E'] ?? 0) + ($cnts['U'] ?? 0);
    $deuPercent = round(($deu / $total) * 100, 0) . '%';
    
    // Add the totals and percentages
    $sheet->setCellValue($this->col($col++) . $r, $total);
    $sheet->setCellValue($this->col($col++) . $r, $mabcPercent);
    $sheet->setCellValue($this->col($col) . $r, $deuPercent);
    $sheet->getStyle("A{$r}:" . $this->col($col) . "{$r}")
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $r++;

    return $r;
}

    private function createValueSummary(Worksheet $sheet, int $r): int
    {
        $val = round($this->data['valueAdditions']['overall'] ?? 0, 0);
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", 'Overall Value Addition:');
        $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        $cell = $this->col(4) . $r;
        $sheet->setCellValue($cell, $val . '%');
        $sheet->getStyle($cell)
              ->getFill()->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setRGB($val >= 0 ? $this->colors['grade_a'] : $this->colors['grade_deu']);
        $sheet->getStyle($cell)
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $r;
    }

    private function col(int $i): string
    {
        $div = $i;
        $col = '';
        while ($div > 0) {
            $mod = ($div - 1) % 26;
            $col = chr(65 + $mod) . $col;
            $div = intval(($div - $mod) / 26);
        }
        return $col;
    }
}
