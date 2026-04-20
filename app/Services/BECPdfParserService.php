<?php

namespace App\Services;

class BECPdfParserService{
    protected array $subjectCodeToHeader = [
        '0561' => 'english',
        '0562' => 'setswana',
        '0563' => 'mathematics',
        '0568' => 'science_single_award',
        '0569' => 'science_double_award',
        '0570' => 'chemistry',
        '0571' => 'physics',
        '0572' => 'biology',
        '0573' => 'human_and_social_biology',
        '0583' => 'history',
        '0584' => 'geography',
        '0585' => 'social_studies',
        '0586' => 'development_studies',
        '0587' => 'literature_in_english',
        '0588' => 'religious_education',
        '0595' => 'design_and_technology',
        '0596' => 'art',
        '0597' => 'computer_studies',
        '0598' => 'commerce',
        '0599' => 'agriculture',
        '0611' => 'food_and_nutrition',
        '0612' => 'fashion_and_fabrics',
        '0613' => 'home_management',
        '0614' => 'accounting',
        '0615' => 'business_studies',
        '0616' => 'physical_education',
        '0617' => 'music',
        '0618' => 'french',
        '1234' => 'english',
        '1235' => 'setswana',
        '1236' => 'mathematics',
        '1237' => 'add_mathematics',
        '1254' => 'hospitality_and_tourism_studies',
        '1255' => 'animal_production',
        '1256' => 'field_crop_production',
        '1257' => 'horticulture',
        '1258' => 'music',
        '1259' => 'physical_education',
        '1261' => 'art',
        '1262' => 'food_and_nutrition',
    ];

    protected array $excelHeaders = [
        'exam_number',
        'english',
        'setswana',
        'mathematics',
        'science_double_award',
        'science_single_award',
        'chemistry',
        'physics',
        'biology',
        'history',
        'geography',
        'social_studies',
        'development_studies',
        'religious_education',
        'literature_in_english',
        'design_and_technology',
        'art',
        'computer_studies',
        'commerce',
        'agriculture',
        'food_and_nutrition',
        'fashion_and_fabrics',
        'home_management',
        'accounting',
        'business_studies',
        'physical_education',
        'music',
        'french',
        'add_mathematics',
        'human_and_social_biology',
        'hospitality_and_tourism_studies',
        'animal_production',
        'field_crop_production',
        'horticulture',
    ];

    public function parseBGCSEPdf(string $pdfText): array{
        $tokens = $this->tokenize($pdfText);
        if (empty($tokens)) {
            return [];
        }

        $studentsByExamNumber = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount - 2; $i++) {
            if (!$this->isExamStart($tokens, $i)) {
                continue;
            }

            $examNumber = $tokens[$i];
            $subjects = [];
            $j = $i + 1;

            while ($j + 1 < $tokenCount && preg_match('/^\d{4}$/', $tokens[$j]) && $this->isRawGradeToken($tokens[$j + 1])) {
                $subjectCode = $tokens[$j];
                $normalizedGrade = $this->normalizeGrade($tokens[$j + 1]);

                if ($normalizedGrade !== null && isset($this->subjectCodeToHeader[$subjectCode])) {
                    $subjectHeader = $this->subjectCodeToHeader[$subjectCode];
                    if (!isset($subjects[$subjectHeader]) || $this->isBetterGrade($normalizedGrade, $subjects[$subjectHeader])) {
                        $subjects[$subjectHeader] = $normalizedGrade;
                    }
                }

                $j += 2;
            }

            if (!empty($subjects)) {
                if (!isset($studentsByExamNumber[$examNumber])) {
                    $studentsByExamNumber[$examNumber] = [
                        'exam_number' => $examNumber,
                        'subjects' => [],
                    ];
                }
                $studentsByExamNumber[$examNumber]['subjects'] = array_merge(
                    $studentsByExamNumber[$examNumber]['subjects'],
                    $subjects
                );
            }

            $i = $j - 1;
        }

        return $this->recoverMissingSequentialCandidates($studentsByExamNumber);
    }

    public function validateParsedData(array $students): array{
        $errors = [];
        $warnings = [];
        $validGrades = ['A', 'B', 'C', 'D', 'E', 'U'];

        foreach ($students as $index => $student) {
            $rowNumber = $index + 1;
            $examNumber = $student['exam_number'] ?? null;
            $subjects = $student['subjects'] ?? [];

            if (empty($examNumber) || !preg_match('/^\d{4}$/', $examNumber)) {
                $errors[] = "Row {$rowNumber}: invalid exam number.";
            }

            if (empty($subjects)) {
                $warnings[] = "Row {$rowNumber}: no mapped subject grades found.";
                continue;
            }

            foreach ($subjects as $subject => $grade) {
                if (!in_array($grade, $validGrades, true)) {
                    $warnings[] = "Row {$rowNumber}: invalid normalized grade '{$grade}' for {$subject}.";
                }
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors),
        ];
    }

    public function getBGCSEExcelHeaders(): array{
        return $this->excelHeaders;
    }

    protected function tokenize(string $pdfText): array{
        $text = str_replace(["\r\n", "\r"], "\n", $pdfText);
        $text = preg_replace('/([A-Za-z])(\d{4})/u', '$1 $2', $text);

        $cleanedLines = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($this->isHeaderOrFooterLine($line)) {
                continue;
            }
            if ($this->isSubjectLegendLine($line)) {
                continue;
            }

            $cleanedLines[] = preg_replace('/\s+/u', ' ', $line);
        }

        $merged = trim(implode(' ', $cleanedLines));
        if ($merged === '') {
            return [];
        }

        return preg_split('/\s+/', $merged) ?: [];
    }

    protected function isExamStart(array $tokens, int $index): bool{
        if (!isset($tokens[$index], $tokens[$index + 1], $tokens[$index + 2])) {
            return false;
        }

        if (!preg_match('/^\d{4}$/', $tokens[$index])) {
            return false;
        }

        if (!preg_match('/^\d{4}$/', $tokens[$index + 1])) {
            return false;
        }

        return $this->isRawGradeToken($tokens[$index + 2]);
    }

    protected function isRawGradeToken(string $value): bool{
        $grade = strtoupper(trim($value));
        $grade = preg_replace('/[^A-Z\*]/', '', $grade);

        if ($grade === '') {
            return false;
        }

        return preg_match('/^(?:A\*|[A-Z]|[A-Z]{2})$/', $grade) === 1;
    }

    protected function normalizeGrade(string $rawGrade): ?string{
        $grade = strtoupper(trim($rawGrade));
        $grade = preg_replace('/[^A-Z\*]/', '', $grade);

        if ($grade === '') {
            return null;
        }

        if (strlen($grade) === 2 && $grade[1] === '*') {
            $grade = $grade[0];
        }

        if (strlen($grade) === 2 && $grade[0] === $grade[1]) {
            $grade = $grade[0];
        }

        if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'U'], true)) {
            return $grade;
        }

        if (in_array($grade, ['F', 'G', 'X', 'N'], true)) {
            return 'U';
        }

        if (strlen($grade) === 2) {
            $first = $grade[0];
            if (in_array($first, ['A', 'B', 'C', 'D', 'E', 'U'], true)) {
                return $first;
            }
            if (in_array($first, ['F', 'G', 'X', 'N'], true)) {
                return 'U';
            }
        }

        return null;
    }

    protected function isBetterGrade(string $incoming, string $current): bool{
        $rank = [
            'A' => 6,
            'B' => 5,
            'C' => 4,
            'D' => 3,
            'E' => 2,
            'U' => 1,
        ];

        return ($rank[$incoming] ?? 0) > ($rank[$current] ?? 0);
    }

    protected function isHeaderOrFooterLine(string $line): bool{
        $patterns = [
            '/BGCSE-.*RESULTS BROADSHEET/i',
            '/^BW\d+\s+/i',
            '/^Page \d+ of \d+/i',
            '/^Botswana Examinations Council$/i',
            '/^Tel:\s*/i',
            '/^Plot\s+\d+/i',
            '/Username/i',
            '/\d{2}\/\d{2}\/\d{4}\s+\d{1,2}:\d{2}:\d{2}(AM|PM)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    protected function isSubjectLegendLine(string $line): bool{
        if (stripos($line, '(BGCSE)') === false) {
            return false;
        }

        return preg_match('/^\d{4}/', $line) === 1;
    }

    protected function recoverMissingSequentialCandidates(array $studentsByExamNumber): array{
        if (empty($studentsByExamNumber)) {
            return [];
        }

        $examNumbers = array_values(array_filter(array_map(static function ($key) {
            return preg_match('/^\d{4}$/', (string) $key) ? (int) $key : null;
        }, array_keys($studentsByExamNumber)), static fn ($value) => $value !== null));

        if (count($examNumbers) < 10) {
            ksort($studentsByExamNumber, SORT_STRING);
            return array_values($studentsByExamNumber);
        }

        sort($examNumbers, SORT_NUMERIC);
        $min = (int) $examNumbers[0];
        $max = (int) $examNumbers[count($examNumbers) - 1];
        $span = ($max - $min) + 1;

        if ($span <= 0) {
            ksort($studentsByExamNumber, SORT_STRING);
            return array_values($studentsByExamNumber);
        }

        $coverage = count($examNumbers) / $span;
        if ($coverage < 0.90) {
            ksort($studentsByExamNumber, SORT_STRING);
            return array_values($studentsByExamNumber);
        }

        $present = array_flip($examNumbers);
        $missing = [];
        for ($candidate = $min; $candidate <= $max; $candidate++) {
            if (!isset($present[$candidate])) {
                $missing[] = $candidate;
            }
        }

        if (empty($missing)) {
            ksort($studentsByExamNumber, SORT_STRING);
            return array_values($studentsByExamNumber);
        }

        $maxRecoverableGaps = max(20, (int) floor($span * 0.10));
        if (count($missing) > $maxRecoverableGaps) {
            ksort($studentsByExamNumber, SORT_STRING);
            return array_values($studentsByExamNumber);
        }

        foreach ($missing as $examNumber) {
            $key = str_pad((string) $examNumber, 4, '0', STR_PAD_LEFT);
            $studentsByExamNumber[$key] = [
                'exam_number' => $key,
                'subjects' => [],
                'inferred_missing' => true,
            ];
        }

        ksort($studentsByExamNumber, SORT_STRING);
        return array_values($studentsByExamNumber);
    }
}
