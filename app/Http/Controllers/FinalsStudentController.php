<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFinalsContext;
use App\Http\Controllers\Controller;
use App\Models\FinalStudent;
use App\Models\ExternalExam;
use App\Models\ExternalExamResult;
use App\Models\ExternalExamSubjectResult;
use App\Models\FinalGradeSubject;
use App\Models\Student;
use App\Models\Term;
use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Log;

class FinalsStudentController extends Controller{
    use InteractsWithFinalsContext;

    public function __construct(){}

    public function index(Request $request){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::findOrFail($selectedTermId);
        $selectedYear = (int) $request->query('year', $selectedTerm->year);
        $finalsDefinition = $this->finalsDefinition($request);

        $earliestYear = Term::min('year');
        $currentYear = TermHelper::getCurrentTerm()->year;
        $futureYear = $currentYear + 2;
        
        $availableYears = [];
        if ($earliestYear) {
            for ($year = $futureYear; $year >= $earliestYear; $year--) {
                $availableYears[] = $year;
            }
        }

        $stats = $this->buildBadgeData($selectedYear, $finalsDefinition);

        $schoolModeResolver = $this->schoolModeResolver();
        $finalsContext = $schoolModeResolver->currentFinalsContext($request->query('finals_context'));
        $reportMenu = $this->finalsReportMenu($finalsDefinition, 'students', [
            'year' => $selectedYear,
        ]);

        return view('finals.index', compact(
            'availableYears',
            'selectedYear',
            'schoolModeResolver',
            'finalsContext',
            'finalsDefinition',
            'reportMenu',
        ))->with($stats);
    }

    public function getData(Request $request){
        $year = $request->get('year');
        $finalsDefinition = $this->finalsDefinition($request);
        
        $query = FinalStudent::with([
            'finalKlasses',
            'graduationTerm',
            'graduationGrade',
            'externalExamResults' => function($q) use ($finalsDefinition) {
                // Eager load externalExam for calculated_overall_grade accessor
                $this->scopeFinalsQuery($q, 'external_exam_results', $finalsDefinition);
                $q->latest()->with('externalExam');
            }
        ]);
        $this->scopeFinalsQuery($query, 'final_students', $finalsDefinition);

        if ($year) {
            $query->where('graduation_year', $year);
        }
        $students = $query->orderBy('first_name')->orderBy('last_name')->get();
        return view('finals.partial.students-partial', compact('students', 'finalsDefinition'))->render();
    }

    public function eligibleStudents(Request $request){
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'search' => 'nullable|string|max:100',
        ]);
        $finalsDefinition = $this->finalsDefinition($request);
        $eligibleGrade = strtoupper($finalsDefinition->eligiblePriorYearGrade);

        $graduationYear = (int) $validated['year'];
        $previousYearTerm = Term::query()
            ->where('year', $graduationYear - 1)
            ->where('term', 3)
            ->first();

        if (!$previousYearTerm) {
            return response()->json([
                'students' => [],
                'message' => "Term 3 for year " . ($graduationYear - 1) . " was not found.",
            ]);
        }

        $search = trim((string) ($validated['search'] ?? ''));

        $query = DB::table('students')
            ->join('student_term', function ($join) use ($previousYearTerm) {
                $join->on('student_term.student_id', '=', 'students.id')
                    ->where('student_term.term_id', '=', $previousYearTerm->id)
                    ->where('student_term.status', '=', 'Current')
                    ->whereNull('student_term.deleted_at');
            })
            ->join('grades', function ($join) {
                $join->on('grades.id', '=', 'student_term.grade_id')
                    ->whereNull('grades.deleted_at');
            })
            ->leftJoin('final_students', function ($join) use ($graduationYear) {
                $join->on('final_students.original_student_id', '=', 'students.id')
                    ->where('final_students.graduation_year', '=', $graduationYear)
                    ->whereNull('final_students.deleted_at');
            })
            ->leftJoin('klass_student', function ($join) use ($previousYearTerm) {
                $join->on('klass_student.student_id', '=', 'students.id')
                    ->where('klass_student.term_id', '=', $previousYearTerm->id)
                    ->whereNull('klass_student.deleted_at');
            })
            ->leftJoin('klasses', 'klasses.id', '=', 'klass_student.klass_id')
            ->whereNull('students.deleted_at')
            ->whereRaw('UPPER(TRIM(grades.name)) = ?', [$eligibleGrade])
            ->whereNull('final_students.id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('students.first_name', 'like', "%{$search}%")
                    ->orWhere('students.last_name', 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(students.first_name, ' ', students.last_name)"), 'like', "%{$search}%")
                    ->orWhere('students.id_number', 'like', "%{$search}%")
                    ->orWhere('students.exam_number', 'like', "%{$search}%");
            });
        }

        $rows = $query
            ->groupBy(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.id_number',
                'students.exam_number'
            )
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->selectRaw("
                students.id,
                students.first_name,
                students.last_name,
                students.id_number,
                students.exam_number,
                MAX(klasses.name) as class_name
            ")
            ->limit(100)
            ->get();

        $students = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'name' => trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')),
                'id_number' => $row->id_number,
                'exam_number' => $row->exam_number,
                'class_name' => $row->class_name,
            ];
        })->values();

        return response()->json([
            'students' => $students,
            'message' => $students->isEmpty()
                ? "No eligible {$eligibleGrade} students found."
                : null,
        ]);
    }

    public function addFromStudentsModule(Request $request){
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'graduation_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
        ]);
        $finalsDefinition = $this->finalsDefinition($request);
        $eligibleGrade = strtoupper($finalsDefinition->eligiblePriorYearGrade);

        $graduationYear = (int) $validated['graduation_year'];
        $previousYearTerm = Term::query()
            ->where('year', $graduationYear - 1)
            ->where('term', 3)
            ->first();

        if (!$previousYearTerm) {
            return redirect()->back()->with('error', "Cannot add student: Term 3 for year " . ($graduationYear - 1) . " was not found.");
        }

        $student = Student::query()->where('id', $validated['student_id'])->whereNull('deleted_at')->first();
        if (!$student) {
            return redirect()->back()->with('error', 'Student was not found in the Students module.');
        }

        $studentTerm = DB::table('student_term')
            ->join('grades', function ($join) {
                $join->on('grades.id', '=', 'student_term.grade_id')
                    ->whereNull('grades.deleted_at');
            })
            ->where('student_term.student_id', $student->id)
            ->where('student_term.term_id', $previousYearTerm->id)
            ->where('student_term.status', 'Current')
            ->whereNull('student_term.deleted_at')
            ->whereRaw('UPPER(TRIM(grades.name)) = ?', [$eligibleGrade])
            ->select('student_term.*', 'grades.name as grade_name')
            ->first();

        if (!$studentTerm) {
            return redirect()->back()->with('error', "This student is not eligible. Only students who were in grade {$eligibleGrade} during Term 3 of the previous year can be added.");
        }

        $alreadyExists = FinalStudent::query()
            ->where('original_student_id', $student->id)
            ->where('graduation_year', $graduationYear)
            ->exists();

        if ($alreadyExists) {
            return redirect()->back()->with('error', 'This student already exists in Finals for the selected graduation year.');
        }

        try {
            DB::transaction(function () use ($student, $studentTerm, $previousYearTerm, $graduationYear) {
                $finalStudent = FinalStudent::create([
                    'original_student_id' => $student->id,
                    'connect_id' => $student->connect_id,
                    'sponsor_id' => $student->sponsor_id,
                    'photo_path' => $student->photo_path,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'exam_number' => $student->exam_number,
                    'gender' => $student->gender,
                    'date_of_birth' => $student->date_of_birth,
                    'email' => $student->email,
                    'nationality' => $student->nationality,
                    'id_number' => $student->id_number,
                    'status' => 'Alumni',
                    'credit' => $student->credit,
                    'parent_is_staff' => $student->parent_is_staff,
                    'student_filter_id' => $student->student_filter_id,
                    'student_type_id' => $student->student_type_id,
                    'graduation_term_id' => $previousYearTerm->id,
                    'graduation_year' => $graduationYear,
                    'graduation_grade_id' => $studentTerm->grade_id,
                ]);

                $klassAllocation = DB::table('klass_student')
                    ->where('student_id', $student->id)
                    ->where('term_id', $previousYearTerm->id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('updated_at')
                    ->first();

                $finalKlassId = null;
                if ($klassAllocation) {
                    $finalKlassId = DB::table('final_klasses')
                        ->where('original_klass_id', $klassAllocation->klass_id)
                        ->where('graduation_term_id', $previousYearTerm->id)
                        ->where('graduation_year', $graduationYear)
                        ->value('id');

                    if ($finalKlassId) {
                        DB::table('final_student_klass')->insert([
                            'final_student_id' => $finalStudent->id,
                            'final_klass_id' => $finalKlassId,
                            'graduation_term_id' => $previousYearTerm->id,
                            'graduation_year' => $graduationYear,
                            'grade_id' => $klassAllocation->grade_id ?? $studentTerm->grade_id,
                            'active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($finalKlassId) {
                    $optionalSelections = DB::table('student_optional_subjects')
                        ->where('student_id', $student->id)
                        ->where('term_id', $previousYearTerm->id)
                        ->get();

                    foreach ($optionalSelections as $selection) {
                        $finalOptionalSubjectId = DB::table('final_optional_subjects')
                            ->where('original_optional_subject_id', $selection->optional_subject_id)
                            ->where('graduation_term_id', $previousYearTerm->id)
                            ->where('graduation_year', $graduationYear)
                            ->value('id');

                        if (!$finalOptionalSubjectId) {
                            continue;
                        }

                        DB::table('final_student_optional_subjects')->insertOrIgnore([
                            'final_student_id' => $finalStudent->id,
                            'final_optional_subject_id' => $finalOptionalSubjectId,
                            'graduation_term_id' => $previousYearTerm->id,
                            'final_klass_id' => $finalKlassId,
                            'graduation_year' => $graduationYear,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $studentHouse = DB::table('student_house')
                    ->where('student_id', $student->id)
                    ->where('term_id', $previousYearTerm->id)
                    ->first();

                if ($studentHouse) {
                    $finalHouseId = DB::table('final_houses')
                        ->where('original_house_id', $studentHouse->house_id)
                        ->where('graduation_term_id', $previousYearTerm->id)
                        ->where('graduation_year', $graduationYear)
                        ->value('id');

                    if ($finalHouseId) {
                        DB::table('final_student_houses')->insertOrIgnore([
                            'final_student_id' => $finalStudent->id,
                            'final_house_id' => $finalHouseId,
                            'graduation_term_id' => $previousYearTerm->id,
                            'graduation_year' => $graduationYear,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });

            return redirect()->back()->with('message', 'Student added to Finals successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to manually add student to finals', [
                'student_id' => $student->id,
                'graduation_year' => $graduationYear,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to add student to Finals: ' . $e->getMessage());
        }
    }


    public function assignExamNumbers($className = '3S', $prefix = '3S'){
        try {
            DB::beginTransaction();
            $students = DB::table('final_students as fs')
                ->join('final_student_klass as fsk', 'fs.id', '=', 'fsk.final_student_id')
                ->join('final_klasses as fk', 'fsk.final_klass_id', '=', 'fk.id')
                ->where('fk.name', $className)
                ->select('fs.id', 'fs.first_name', 'fs.last_name', 'fs.exam_number')
                ->orderBy('fs.last_name')
                ->orderBy('fs.first_name')
                ->get();

            if ($students->isEmpty()) {
                Log::warning("No students found in class: {$className}");
                return [
                    'success' => false,
                    'message' => "No students found in class '{$className}'",
                    'count' => 0,
                    'students' => []
                ];
            }

            $updated = [];
            $counter = 1;

            foreach ($students as $student) {
                $examNumber = $prefix . str_pad($counter, 3, '0', STR_PAD_LEFT);
                DB::table('final_students')
                    ->where('id', $student->id)
                    ->update(['exam_number' => $examNumber]);

                $updated[] = [
                    'id' => $student->id,
                    'name' => trim($student->first_name . ' ' . $student->last_name),
                    'old_exam_number' => $student->exam_number,
                    'new_exam_number' => $examNumber
                ];

                $counter++;
            }

            DB::commit();

            Log::info("Exam numbers assigned successfully", [
                'class' => $className,
                'prefix' => $prefix,
                'count' => count($updated),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Successfully assigned exam number",
                'count' => count($updated),
                'students' => $updated
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error assigning exam numbers', [
                'class' => $className,
                'prefix' => $prefix,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to assign exam numbers: ' . $e->getMessage(),
                'count' => 0,
                'students' => []
            ];
        }
    }

    public function updateTermIdToTwo(){
        try {
            DB::beginTransaction();
            $termClosed = DB::table('terms')->where('id', 1)->update(['closed' => 1]);
            DB::commit();
            Log::info("Deleted student_term records with term_id=1, updated optional_subjects to term_id=2. Closed term 1 of 2025.");
            return [
                'term_closed' => $termClosed
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating term_id to 2: " . $e->getMessage());
            throw $e;
        }
    }

    public function getBadgeData(Request $request){
        $year = $request->get('year');
        $finalsDefinition = $this->finalsDefinition($request);
        $data = $this->buildBadgeData($year ? (int) $year : null, $finalsDefinition);

        if ($request->ajax()) {
            return response()->json($data);
        }
        return $data;
    }

    public function noCandidateNumber(Request $request){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::findOrFail($selectedTermId);
        $selectedYear = $selectedTerm->year;
        $finalsDefinition = $this->finalsDefinition($request);

        $studentsQuery = FinalStudent::whereNull('exam_number')
            ->where('graduation_year', $selectedYear)
            ->with(['finalKlasses', 'graduationGrade'])
            ->orderBy('first_name')
            ->orderBy('last_name');
        $this->scopeFinalsQuery($studentsQuery, 'final_students', $finalsDefinition);
        $students = $studentsQuery->get();

        return view('finals.no-candidate-students', compact('students', 'selectedYear', 'finalsDefinition'));
    }

    public function show(Request $request, FinalStudent $student){
        $finalsDefinition = $this->finalsDefinition($request);
        $this->ensureStudentMatchesContext($student, $finalsDefinition);

        $student->load([
            'originalStudent',
            'sponsor',
            'graduationTerm',
            'graduationGrade',
            'filter',
            'type',
            'finalKlasses.originalKlass',
            'finalKlasses.finalKlassSubjects.finalGradeSubject.subject',
            'finalOptionalSubjects.finalGradeSubject.subject',
            'externalExamResults.externalExam',
            'externalExamResults.subjectResults.finalGradeSubject.subject'
        ]);

        $latestExamResult = $student->externalExamResults
            ->filter(fn ($result) => $this->matchesExamType($result, $finalsDefinition->examType))
            ->sortByDesc('id')
            ->first();
        $examSubjectResults = collect([]);
        if ($latestExamResult) {
            $examSubjectResults = $latestExamResult->subjectResults()->with('finalGradeSubject.subject')->get()->keyBy('final_grade_subject_id');
        }

        $allSubjects = collect([]);
        foreach ($student->finalKlasses as $klass) {
            foreach ($klass->finalKlassSubjects as $klassSubject) {
                if ($klassSubject->finalGradeSubject && $klassSubject->finalGradeSubject->subject) {
                    $subject = $klassSubject->finalGradeSubject->subject;
                    $allSubjects->push([
                        'subject' => $subject,
                        'type' => 'mandatory',
                        'subject_code' => $subject->code ?? $subject->name,
                        'subject_name' => $subject->name,
                        'final_grade_subject_id' => $klassSubject->finalGradeSubject->id
                    ]);
                }
            }
        }
        
        foreach ($student->finalOptionalSubjects as $optionalSubject) {
            if ($optionalSubject->finalGradeSubject && $optionalSubject->finalGradeSubject->subject) {
                $subject = $optionalSubject->finalGradeSubject->subject;
                $allSubjects->push([
                    'subject' => $subject,
                    'type' => 'optional',
                    'subject_code' => $subject->code ?? $subject->name,
                    'subject_name' => $subject->name,
                    'final_grade_subject_id' => $optionalSubject->finalGradeSubject->id
                ]);
            }
        }
        
        $allSubjects = $allSubjects->unique('subject_code');
        $subjectResults = $allSubjects->map(function ($subjectData) use ($examSubjectResults) {
            $finalGradeSubjectId = $subjectData['final_grade_subject_id'];
            $examResult = $examSubjectResults->get($finalGradeSubjectId);
            
            if ($examResult) {
                return (object) [
                    'subject_code' => $subjectData['subject_code'],
                    'subject_name' => $examResult->subject_name ?? $subjectData['subject_name'],
                    'grade' => $examResult->grade,
                    'grade_points' => $examResult->grade_points,
                    'is_pass' => $examResult->is_pass,
                    'has_result' => true,
                    'type' => $subjectData['type'],
                    'final_grade_subject_id' => $finalGradeSubjectId,
                    'finalGradeSubject' => $examResult->finalGradeSubject ?? null
                ];
            } else {
                return (object) [
                    'subject_code' => $subjectData['subject_code'],
                    'subject_name' => $subjectData['subject_name'],
                    'grade' => null,
                    'grade_points' => null,
                    'is_pass' => null,
                    'has_result' => false,
                    'type' => $subjectData['type'],
                    'final_grade_subject_id' => $finalGradeSubjectId,
                    'finalGradeSubject' => null
                ];
            }
        })->sortBy('subject_name');

        $subjectsWithResults = $subjectResults->where('has_result', true);
        $stats = [
            'total_subjects' => $allSubjects->count(),
            'subjects_with_results' => $subjectsWithResults->count(),
            'subjects_without_results' => $allSubjects->count() - $subjectsWithResults->count(),
            'passed_subjects' => $subjectsWithResults->where('is_pass', true)->count(),
            'failed_subjects' => $subjectsWithResults->where('is_pass', false)->count(),
            'total_points' => $subjectsWithResults->sum('grade_points'),
            'average_points' => $subjectsWithResults->count() > 0 ? round($subjectsWithResults->avg('grade_points'), 2) : 0,
        ];

        return view('finals.student-view', compact('student', 'latestExamResult', 'subjectResults', 'stats', 'finalsDefinition'));
    }

    public function edit(FinalStudent $student){
        $student->load([
            'graduationTerm',
            'graduationGrade',
            'finalKlasses'
        ]);

        $genders = [
            'M' => 'Male',
            'F' => 'Female'
        ];

        $statuses = ['Alumni', 'Current', 'Past'];
        return view('finals.edit', compact('student', 'genders', 'statuses'));
    }

    public function update(Request $request, FinalStudent $student){
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'exam_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('final_students', 'exam_number')
                    ->ignore($student->id)
                    ->where(function ($query) use ($student) {
                        return $query->where('graduation_year', $student->graduation_year);
                    }),
            ],
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'nullable|date',
            'email' => 'sometimes|nullable|email|max:255',
            'nationality' => 'sometimes|nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'status' => 'sometimes|required|string|max:50',
            'credit' => 'sometimes|nullable|numeric|min:0',
            'parent_is_staff' => 'sometimes|boolean',
            'photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $data = $validated;
            if (array_key_exists('exam_number', $data)) {
                $examNumber = trim((string) ($data['exam_number'] ?? ''));
                $data['exam_number'] = $examNumber === '' ? null : strtoupper($examNumber);
            }

            if ($request->hasFile('photo_path')) {
                if ($student->photo_path && \Storage::disk('public')->exists($student->photo_path)) {
                    Storage::disk('public')->delete($student->photo_path);
                }
                
                $path = $request->file('photo_path')->store('students/photos', 'public');
                $data['photo_path'] = $path;
            }

            DB::transaction(function () use ($student, $data) {
                $student->update($data);
                if (array_key_exists('exam_number', $data)) {
                    $student->externalExamResults()->update(['exam_number' => $data['exam_number']]);
                }
            });

            return redirect()
                ->route('finals.students.show', ['student' => $student, 'finals_context' => $this->finalsContext($request)])
                ->with('message', 'Student updated successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }

    public function updateExamNumber(Request $request, FinalStudent $student){
        $validated = $request->validate([
            'exam_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('final_students', 'exam_number')
                    ->ignore($student->id)
                    ->where(function ($query) use ($student) {
                        return $query->where('graduation_year', $student->graduation_year);
                    }),
            ],
        ]);

        try {
            $examNumber = trim((string) ($validated['exam_number'] ?? ''));
            $examNumber = $examNumber === '' ? null : strtoupper($examNumber);

            DB::transaction(function () use ($student, $examNumber) {
                $student->update(['exam_number' => $examNumber]);
                $student->externalExamResults()->update(['exam_number' => $examNumber]);
            });

            $response = [
                'success' => true,
                'message' => $examNumber
                    ? 'Exam number updated successfully.'
                    : 'Exam number removed successfully.',
                'exam_number' => $examNumber,
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return back()->with('message', $response['message']);
        } catch (\Exception $e) {
            Log::error('Error updating exam number', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update exam number: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to update exam number.');
        }
    }

    public function getStudentTranscript(Request $request, $studentId){
        try {
            $finalsDefinition = $this->finalsDefinition($request);
            $finalStudent = FinalStudent::with([
                'externalExamResults' => function($query) {
                    $query->with(['externalExam', 'subjectResults' => function($subQuery) {
                        $subQuery->orderBy('subject_name');
                    }]);
                },
                'originalStudent.psle',
                'finalKlasses.grade',
                'graduationTerm',
                'graduationGrade'
            ])->findOrFail($studentId);
            $this->ensureStudentMatchesContext($finalStudent, $finalsDefinition);

            $latestExamResult = $finalStudent->externalExamResults
                ->filter(fn ($result) => $this->matchesExamType($result, $finalsDefinition->examType))
                ->sortByDesc('id')
                ->first();
            if (!$latestExamResult) {
                throw new \Exception('No external exam results found for this student.');
            }
    
            $subjectResults = $latestExamResult->subjectResults->map(function($result) {
                return [
                    'subject_name' => $result->subject_name,
                    'subject_code' => $result->subject_code,
                    'grade' => $result->grade,
                    'grade_points' => $result->grade_points,
                    'is_pass' => $result->is_pass,
                    'is_mapped' => $result->is_mapped
                ];
            });
    
            $totalSubjects = $subjectResults->count();
            $passedSubjects = $subjectResults->where('is_pass', true)->count();
            $totalPoints = $subjectResults->sum('grade_points');
            $averagePoints = $totalSubjects > 0 ? round($totalPoints / $totalSubjects, 1) : 0;
            $studentClass = $finalStudent->finalKlasses->first();
            
            // Use calculated_overall_grade as fallback when overall_grade is null/empty
            $overallGrade = $latestExamResult->overall_grade ?: $latestExamResult->calculated_overall_grade;
    
            $transcriptData = [
                'student' => [
                    'id' => $finalStudent->id,
                    'first_name' => $finalStudent->first_name,
                    'last_name' => $finalStudent->last_name,
                    'full_name' => $finalStudent->full_name,
                    'exam_number' => $finalStudent->exam_number,
                    'gender' => $finalStudent->gender,
                    'gender_full' => $finalStudent->gender === 'M' ? 'Male' : 'Female',
                    'id_number' => $finalStudent->id_number,
                    'formatted_id_number' => $finalStudent->formatted_id_number,
                    'date_of_birth' => $finalStudent->date_of_birth,
                    'nationality' => $finalStudent->nationality,
                    'graduation_year' => $finalStudent->graduation_year,
                    'class_name' => $studentClass ? $studentClass->name : 'N/A',
                    'grade_name' => $studentClass ? ($studentClass->grade->name ?? 'N/A') : 'N/A',
                    'psle_grade' => $finalStudent->originalStudent && $finalStudent->originalStudent->psle 
                        ? $finalStudent->originalStudent->psle->overall_grade 
                        : null
                ],
                'exam_info' => [
                    'exam_type' => $latestExamResult->externalExam->exam_type,
                    'exam_session' => $latestExamResult->externalExam->exam_session,
                    'exam_year' => $latestExamResult->externalExam->exam_year,
                    'centre_code' => $latestExamResult->externalExam->centre_code,
                    'centre_name' => $latestExamResult->externalExam->centre_name,
                    'overall_grade' => $overallGrade,
                    'overall_points' => $latestExamResult->overall_points,
                    'is_pass' => $finalsDefinition->isOverallPass($overallGrade)
                ],
                'subjects' => $subjectResults->toArray(),
                'summary' => [
                    'total_subjects' => $totalSubjects,
                    'passed_subjects' => $passedSubjects,
                    'failed_subjects' => $totalSubjects - $passedSubjects,
                    'total_points' => $totalPoints,
                    'average_points' => $averagePoints,
                    'pass_percentage' => $totalSubjects > 0 ? round(($passedSubjects / $totalSubjects) * 100, 1) : 0
                ]
            ];
    
            Log::info('Student transcript generated', [
                'student_id' => $studentId,
                'student_name' => $finalStudent->full_name,
                'exam_type' => $latestExamResult->externalExam->exam_type,
                'total_subjects' => $totalSubjects,
                'overall_grade' => $overallGrade
            ]);
    
            $school_data = SchoolSetup::first();
            return view('finals.analysis.student-transcript', compact('transcriptData', 'school_data', 'finalsDefinition'));
        } catch (\Exception $e) {
            Log::error('Error generating student transcript', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function addSubjectResult(Request $request, FinalStudent $student){
        $finalsDefinition = $this->finalsDefinition($request);
        $this->ensureStudentMatchesContext($student, $finalsDefinition);

        $request->validate([
            'final_grade_subject_id' => 'required|exists:final_grade_subjects,id',
            'grade' => 'required|in:A,B,C,D,E,U',
        ]);

        try {
            return DB::transaction(function () use ($request, $student) {
                $latestExamResult = $student->externalExamResults()->latest()->first();

                if (!$latestExamResult) {
                    $exam = ExternalExam::firstOrCreate(
                        [
                            'exam_type' => $finalsDefinition->examType,
                            'graduation_year' => $student->graduation_year,
                            'graduation_term_id' => $student->graduation_term_id,
                        ],
                        [
                            'exam_year' => $student->graduation_year - 1,
                            'exam_session' => 'Manual',
                            'centre_code' => 'MANUAL',
                            'centre_name' => $finalsDefinition->examLabel . ' Manual Entry',
                            'import_date' => now(),
                            'imported_by' => auth()->id(),
                        ]
                    );

                    $latestExamResult = ExternalExamResult::create([
                        'external_exam_id' => $exam->id,
                        'final_student_id' => $student->id,
                        'exam_number' => $student->exam_number,
                        'total_subjects' => 0,
                        'passes' => 0,
                        'pass_percentage' => 0,
                    ]);
                }

                $finalGradeSubject = FinalGradeSubject::with('subject')->findOrFail($request->final_grade_subject_id);

                $subjectResult = ExternalExamSubjectResult::updateOrCreate(
                    [
                        'external_exam_result_id' => $latestExamResult->id,
                        'final_grade_subject_id' => $request->final_grade_subject_id,
                    ],
                    [
                        'subject_code' => $finalGradeSubject->subject->code ?? 'MANUAL',
                        'subject_name' => $finalGradeSubject->subject->name ?? 'Unknown',
                        'grade' => $request->grade,
                        'was_taken' => true,
                        'is_mapped' => true,
                    ]
                );

                $this->refreshExamResultSubjectSummary($latestExamResult);

                return response()->json([
                    'success' => true,
                    'message' => 'Grade saved successfully.',
                    'grade' => $subjectResult->grade,
                    'grade_points' => $subjectResult->grade_points,
                    'is_pass' => $subjectResult->is_pass,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error adding subject result', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save grade: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateOverallResult(Request $request, FinalStudent $student){
        $finalsDefinition = $this->finalsDefinition($request);
        $this->ensureStudentMatchesContext($student, $finalsDefinition);

        $request->validate([
            'overall_points' => 'required|numeric|min:0|max:63',
            'overall_grade' => ['required', 'string', Rule::in($finalsDefinition->overallGradeScale)],
        ]);

        try {
            return DB::transaction(function () use ($request, $student) {
                $latestExamResult = $student->externalExamResults()->latest()->first();

                if (!$latestExamResult) {
                    $exam = ExternalExam::firstOrCreate(
                        [
                            'exam_type' => $finalsDefinition->examType,
                            'graduation_year' => $student->graduation_year,
                            'graduation_term_id' => $student->graduation_term_id,
                        ],
                        [
                            'exam_year' => $student->graduation_year - 1,
                            'exam_session' => 'Manual',
                            'centre_code' => 'MANUAL',
                            'centre_name' => $finalsDefinition->examLabel . ' Manual Entry',
                            'import_date' => now(),
                            'imported_by' => auth()->id(),
                        ]
                    );

                    $latestExamResult = ExternalExamResult::create([
                        'external_exam_id' => $exam->id,
                        'final_student_id' => $student->id,
                        'exam_number' => $student->exam_number,
                        'total_subjects' => 0,
                        'passes' => 0,
                        'pass_percentage' => 0,
                    ]);
                }

                $latestExamResult->update([
                    'overall_points' => $request->overall_points,
                    'overall_grade' => $request->overall_grade,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Overall result updated successfully.',
                    'overall_points' => $latestExamResult->overall_points,
                    'overall_grade' => $latestExamResult->overall_grade,
                    'is_pass' => $finalsDefinition->isOverallPass($request->overall_grade),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error updating overall result', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update overall result: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function refreshExamResultSubjectSummary(ExternalExamResult $examResult): void{
        $takenSubjectsQuery = $examResult->subjectResults()->where('was_taken', true);
        $totalSubjects = (clone $takenSubjectsQuery)->count();
        $passes = (clone $takenSubjectsQuery)->where('is_pass', true)->count();

        $examResult->update([
            'total_subjects' => $totalSubjects,
            'passes' => $passes,
            'pass_percentage' => $totalSubjects > 0 ? round(($passes / $totalSubjects) * 100, 2) : 0,
        ]);
    }

    public function destroy(Request $request, FinalStudent $student){
        try {
            DB::transaction(function () use ($student) {
                $student->finalKlasses()->detach();
                $student->finalOptionalSubjects()->detach();
                $student->finalHouses()->detach();

                foreach ($student->externalExamResults as $examResult) {
                    $examResult->subjectResults()->delete();
                }
                $student->externalExamResults()->delete();

                $student->delete();
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student deleted successfully.',
                ]);
            }

            return redirect()->route('finals.students.index')
                ->with('message', 'Student deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting final student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete student: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('finals.students.index')
                ->with('error', 'Failed to delete student.');
        }
    }

    public function bulkDestroy(Request $request){
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:final_students,id',
        ]);

        try {
            $count = 0;
            DB::transaction(function () use ($request, &$count) {
                $students = FinalStudent::whereIn('id', $request->student_ids)->get();

                foreach ($students as $student) {
                    $student->finalKlasses()->detach();
                    $student->finalOptionalSubjects()->detach();
                    $student->finalHouses()->detach();

                    foreach ($student->externalExamResults as $examResult) {
                        $examResult->subjectResults()->delete();
                    }
                    $student->externalExamResults()->delete();

                    $student->delete();
                    $count++;
                }
            });

            return response()->json([
                'success' => true,
                'message' => "{$count} student(s) deleted successfully.",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk deleting final students', [
                'student_ids' => $request->student_ids,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete students: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function buildBadgeData(?int $year, \App\Services\Finals\FinalsContextDefinition $finalsDefinition): array
    {
        $query = FinalStudent::query();
        $this->scopeFinalsQuery($query, 'final_students', $finalsDefinition);

        if ($year) {
            $query->where('graduation_year', $year);
        }

        $totalStudents = (clone $query)->count();
        $studentsWithResults = (clone $query)->whereHas('externalExamResults', function ($examQuery) use ($finalsDefinition) {
            $this->scopeFinalsQuery($examQuery, 'external_exam_results', $finalsDefinition);
        })->count();
        $noCandidateNumber = (clone $query)->whereNull('exam_number')->count();
        $studentsPending = (clone $query)
            ->whereNotNull('exam_number')
            ->whereDoesntHave('externalExamResults', function ($examQuery) use ($finalsDefinition) {
                $this->scopeFinalsQuery($examQuery, 'external_exam_results', $finalsDefinition);
            })
            ->count();
        $passedStudents = (clone $query)->whereHas('externalExamResults', function ($examQuery) use ($finalsDefinition) {
            $this->scopeFinalsQuery($examQuery, 'external_exam_results', $finalsDefinition)
                ->whereIn('overall_grade', $finalsDefinition->passGradeSet);
        })->count();

        return [
            'totalStudents' => $totalStudents,
            'studentsWithResults' => $studentsWithResults,
            'noCandidateNumber' => $noCandidateNumber,
            'studentsPending' => $studentsPending,
            'passRate' => $studentsWithResults > 0 ? round(($passedStudents / $studentsWithResults) * 100, 1) : 0,
        ];
    }

    private function matchesExamType(ExternalExamResult $result, string $examType): bool
    {
        return strtoupper((string) optional($result->externalExam)->exam_type) === strtoupper($examType);
    }

    private function ensureStudentMatchesContext(FinalStudent $student, \App\Services\Finals\FinalsContextDefinition $finalsDefinition): void
    {
        $student->loadMissing('graduationGrade');

        if (!$finalsDefinition->matchesGradeName(optional($student->graduationGrade)->name)) {
            abort(404);
        }
    }
}
