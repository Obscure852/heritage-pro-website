<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetup;
use App\Services\BECPdfParserService;
use App\Services\JCEPdfParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Smalot\PdfParser\Parser;

class PdfToExcelConverterController extends Controller{
    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->authorize('manage-assessment');
            return $next($request);
        });
    }

    public function convertPdfToExcel(Request $request){
        $schoolMode = $this->resolveSchoolMode();
        $allowedExamTypes = $this->getAllowedExamTypesForMode($schoolMode);

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
            'exam_type' => ['required', 'string', Rule::in($allowedExamTypes)],
            'exam_session' => 'required|string|max:255',
            'exam_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        try {
            $pdfFile = $request->file('pdf_file');
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfFile->getPathname());
            $text = $pdf->getText();

            $examType = strtoupper($request->exam_type);
            $parserService = $this->resolveParserService($examType);
            if (!$parserService) {
                return redirect()->back()->with('error', "PDF conversion for {$examType} is not yet supported.");
            }

            if ($parserService instanceof BECPdfParserService) {
                $studentsData = $parserService->parseBGCSEPdf($text);
            } else {
                $studentsData = $parserService->parseJCEPdf($text);
            }

            if (empty($studentsData)) {
                return redirect()->back()->with('error', 'No student data found in the PDF. Please check the file format.');
            }

            $validation = $parserService->validateParsedData($studentsData);
            
            if (!$validation['is_valid']) {
                $errorMessage = 'PDF parsing encountered errors:<br>' . implode('<br>', $validation['errors']);
                if (!empty($validation['warnings'])) {
                    $errorMessage .= '<br><br>Warnings:<br>' . implode('<br>', $validation['warnings']);
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            $headers = $this->getExcelHeadersForExamType($examType, $parserService);
            $excelFile = $this->createExcelFromStudentData($studentsData, $headers);
            $filename = 'converted_' . $request->exam_type . '_' . $request->exam_year . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            $inferredCandidates = 0;
            foreach ($studentsData as $studentRow) {
                if (!empty($studentRow['inferred_missing'])) {
                    $inferredCandidates++;
                }
            }

            $successMessage = 'PDF converted successfully! Found ' . count($studentsData) . ' students.';
            if ($inferredCandidates > 0) {
                $successMessage .= ' Recovered ' . $inferredCandidates . ' missing candidate number(s) that were not present in the PDF text layer. Please review/fill their grades in the generated Excel.';
            }
            if (!empty($validation['warnings'])) {
                $successMessage .= ' Note: ' . count($validation['warnings']) . ' warnings detected.';
            }
            
            session()->flash('conversion_success', $successMessage);
            return response()->download($excelFile, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('PDF to Excel conversion failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    protected function createExcelFromStudentData(array $studentsData, array $headers){
        $spreadsheet = new Spreadsheet();
        $sheet  = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        $row = 2;
        $subjectHeaders = array_slice($headers, 1);

        foreach ($studentsData as $student) {
            $rowData = [ $student['exam_number'] ?? '' ];
            foreach ($subjectHeaders as $sub) {
                $rowData[] = $student['subjects'][$sub] ?? '';
            }

            $sheet->fromArray($rowData, null, "A{$row}");
            $row++;
        }

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'jce_conversion_');
        (new Xlsx($spreadsheet))->save($tmp);
        return $tmp;
    }

    public function downloadSample(Request $request){
        $schoolMode = $this->resolveSchoolMode();
        $defaultExamType = $this->getDefaultExamTypeForMode($schoolMode);
        $allowedExamTypes = $this->getAllowedExamTypesForMode($schoolMode);
        $requestedExamType = strtoupper((string) $request->query('exam_type', $defaultExamType));
        $examType = in_array($requestedExamType, $allowedExamTypes, true) ? $requestedExamType : $defaultExamType;

        $parserService = $this->resolveParserService($examType);
        $headers = $this->getExcelHeadersForExamType($examType, $parserService);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        $sampleData = $this->getSampleRowsForHeaders($headers);

        $row = 2;
        foreach ($sampleData as $data) {
            $sheet->fromArray($data, null, "A{$row}");
            $row++;
        }

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'sample_external_exam_format_');
        (new Xlsx($spreadsheet))->save($tmp);
        $filename = 'sample_' . strtolower($examType) . '_import_format.xlsx';
        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    protected function resolveSchoolMode(): string{
        return SchoolSetup::normalizeType(SchoolSetup::query()->value('type')) ?? SchoolSetup::TYPE_JUNIOR;
    }

    protected function getAllowedExamTypesForMode(string $schoolMode): array{
        return match (SchoolSetup::normalizeType($schoolMode)) {
            SchoolSetup::TYPE_PRIMARY => ['PSLE'],
            SchoolSetup::TYPE_JUNIOR,
            SchoolSetup::TYPE_PRE_F3 => ['JCE'],
            SchoolSetup::TYPE_SENIOR => ['BGCSE'],
            SchoolSetup::TYPE_JUNIOR_SENIOR,
            SchoolSetup::TYPE_K12 => ['JCE', 'BGCSE'],
            default => ['JCE'],
        };
    }

    protected function getDefaultExamTypeForMode(string $schoolMode): string{
        return $this->getAllowedExamTypesForMode($schoolMode)[0];
    }

    protected function resolveParserService(string $examType){
        return match ($examType) {
            'BGCSE' => new BECPdfParserService(),
            'PSLE' => new JCEPdfParserService(),
            'JCE' => new JCEPdfParserService(),
            default => null,
        };
    }

    protected function getExcelHeadersForExamType(string $examType, $parserService): array{
        if ($examType === 'BGCSE' && $parserService instanceof BECPdfParserService) {
            return $parserService->getBGCSEExcelHeaders();
        }

        if ($examType === 'PSLE') {
            return [
                'exam_number',
                'setswana',
                'english',
                'mathematics',
                'science',
                'social_studies',
                'agriculture',
                'physical_education',
                'art',
            ];
        }

        return [
            'exam_number',
            'setswana',
            'english',
            'mathematics',
            'science',
            'social_studies',
            'agriculture',
            'design_and_technology',
            'moral_education',
            'home_economics',
            'office_procedures',
            'accounting',
            'religious_education',
            'art',
            'music',
            'physical_education',
        ];
    }

    protected function getSampleRowsForHeaders(array $headers): array{
        $subjectHeaders = array_slice($headers, 1);
        $row1 = array_fill_keys($subjectHeaders, '');
        $row2 = array_fill_keys($subjectHeaders, '');

        $row1['english'] = 'C';
        $row1['mathematics'] = 'C';
        $row1['setswana'] = 'D';
        $row1['science'] = 'D';
        $row1['science_double_award'] = 'C';
        $row1['social_studies'] = 'D';
        $row1['geography'] = 'C';
        $row1['agriculture'] = 'C';

        $row2['english'] = 'B';
        $row2['mathematics'] = 'B';
        $row2['setswana'] = 'C';
        $row2['science'] = 'B';
        $row2['science_double_award'] = 'B';
        $row2['social_studies'] = 'C';
        $row2['computer_studies'] = 'B';
        $row2['accounting'] = 'C';

        $first = ['0001'];
        $second = ['0002'];
        foreach ($subjectHeaders as $subjectHeader) {
            $first[] = $row1[$subjectHeader] ?? '';
            $second[] = $row2[$subjectHeader] ?? '';
        }

        return [$first, $second];
    }
}
