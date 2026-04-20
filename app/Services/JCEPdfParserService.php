<?php

namespace App\Services;

class JCEPdfParserService{
    protected array $subjectHeaderMap = [
        '11' => 'setswana',
        '12' => 'english',
        '13' => 'mathematics',
        '14' => 'science',
        '15' => 'social_studies',
        '16' => 'agriculture',
        '17' => 'design_and_technology',
        '18' => 'moral_education',
        '21' => 'home_economics',
        '25' => 'office_procedures',
        '26' => 'accounting',
        '31' => 'religious_education',
        '33' => 'art',
        '34' => 'music',
        '35' => 'physical_education',
    ];

    public function parseJCEPdf(string $pdfText): array{
        $pdfText = preg_replace('/\p{Z}+/u', ' ', $pdfText);
        $lines   = $this->mergeWrappedLines($this->cleanPdfText($pdfText));

        $students = [];
        foreach ($lines as $l) {
            if ($s = $this->parseStudentLine($l)) {
                $students[] = $s;
            }
        }
        return $students;
    }

    protected function cleanPdfText(string $text): array{
        $out = [];
        foreach (explode("\n", $text) as $ln) {
            $ln = trim($ln);
            if (
                $ln !== '' &&
                !$this->isHeaderOrFooterLine($ln) &&
                !$this->isStatisticalLine($ln)
            ) {
                $out[] = $ln;
            }
        }
        return $out;
    }

    protected function mergeWrappedLines(array $lines): array{
        $m = [];  $buf = '';
        foreach ($lines as $ln) {
            $ln = preg_replace('/\s+/u', ' ', trim($ln));
            if ($ln === '') continue;

            $buf .= ($buf ? ' ' : '') . $ln;
            if (preg_match('/\d{3}$/', $buf)) {
                $m[] = $buf;  $buf = '';
            }
        }
        if ($buf !== '') $m[] = $buf;
        return $m;
    }

    protected function parseStudentLine(string $line): ?array{
        $overall = '(?:[ABCDEUMN]|Merit|Credit|Pass|Fail)';
        $pStart  = '/^(\d{3})\s+.+?\s+'.$overall.'\s+((?:\d+\s+[ABCDEUMN];?\s*)+)$/u';
        $pEnd    = '/^.+?\s+'.$overall.'\s+((?:\d+\s+[ABCDEUMN];?\s*)+)\s*(\d{3})$/u';

        if (preg_match($pStart, $line, $m)) {
            [, $exam, $subs] = $m;
        } elseif (preg_match($pEnd, $line, $m)) {
            [, $subs, $exam] = $m;
        } else {
            return null;
        }

        return [
            'exam_number' => $exam,
            'subjects'    => $this->parseSubjectGrades($subs),
        ];
    }

    protected function parseSubjectGrades(string $blob): array{
        $out = [];
        if (preg_match_all('/(\d+)\s+([ABCDEUMN])(?:;|\s|$)/', $blob, $m, PREG_SET_ORDER)) {
            foreach ($m as [, $code, $grade]) {
                if (isset($this->subjectHeaderMap[$code])) {
                    $out[$this->subjectHeaderMap[$code]] = $grade;
                }
            }
        }
        return $out;
    }

    protected function isHeaderOrFooterLine(string $line): bool{
        $p = [
            '/Grade Listing.*School Version/i', '/JCE-November\/\d{4}/i',
            '/Centre.*JC\d+/i',                 '/Linchwe II Junior Secondary School/i',
            '/Page \d+ of \d+/i',               '/Name.*Subject Codes.*Grades/i',
            '/Subject Codes and Students Grades/i',
            '/^Home Economics;.*Physical Education;/i',
            '/PL Pass Level/i',                 '/Subject.*Total/i',
            '/Non Back to School/i',            '/^\d+\.\d+%.*\d+\.\d+%/i',
            '/^Total.*\d+$/i',                  '/^Freq\..*Pct\./i',
            '/^Overall Grades.*Merit/i',        '/^\d+\s+\d+\s+\d+.*\d+$/i',
            '/^[ABCDEUMN]\s+[ABCDEUMN]/i',      '/\d+\/\d+\/\d+\s+\d+:\d+:\d+[AP]M/i',
            '/Username.*Mandas/i',
        ];
        foreach ($p as $re) if (preg_match($re, $line)) return true;
        return false;
    }

    protected function isStatisticalLine(string $line): bool{
        $p = [
            '/^\d+\.\d+%.*\d+\.\d+%/', '/^Total.*\d+$/',
            '/^Freq\..*Pct\./',       '/^Overall Grades.*Merit/',
            '/^\d+\s+\d+\s+\d+.*\d+$/','/^[ABCDEUMN]\s+[ABCDEUMN]/',
        ];
        foreach ($p as $re) if (preg_match($re, $line)) return true;
        return false;
    }

    public function validateParsedData(array $students): array{
        $errors = $warnings = [];
        $valid  = ['A','B','C','D','E','U','M'];

        foreach ($students as $idx => $s) {
            if (empty($s['exam_number'])) {
                $errors[] = "Row ".($idx+1).": missing Exam Number.";
            }
            if (empty($s['subjects'])) {
                $warnings[] = "Row ".($idx+1).": no subject grades found.";
            } else {
                foreach ($s['subjects'] as $sub => $g) {
                    if (!in_array($g, $valid)) {
                        $warnings[] = "Row ".($idx+1).": invalid grade '$g' for $sub.";
                    }
                }
            }
        }

        return [
            'errors'   => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors),
        ];
    }
}
