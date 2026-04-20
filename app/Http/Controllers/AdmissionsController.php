<?php

namespace App\Http\Controllers;

use App\Exports\AdmissionByStatusExport;
use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Imports\SeniorAdmissionsImport;
use App\Models\Admission;
use App\Models\AdmissionAcademic;
use App\Models\AdmissionHealthInformation;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\SchoolSetup;
use App\Models\SeniorAdmissionAcademic;
use App\Models\SeniorAdmissionPlacementCriteria;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Services\SeniorAdmissionPlacementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Validation\Rule;

class AdmissionsController extends Controller{

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(){
        try {
            $terms = Term::all();
            $admissions = CacheHelper::getAdmissions();
            $currentTerm = TermHelper::getCurrentTerm();

            // Get unique statuses and grades for filters
            $statuses = $admissions->pluck('status')->unique()->filter()->sort()->values();
            $grades = $admissions->pluck('grade_applying_for')->unique()->filter()->sort()->values();

            return view('admissions.index', compact('admissions', 'terms', 'currentTerm', 'statuses', 'grades'));

        } catch (\Exception $e) {
            Log::error('Error in admissions index: ' . $e->getMessage());
            $userErrorMessage = 'A critical error occurred. Please contact support.';
            return back()->with('error', $userErrorMessage);
        }
    }

    public function settings(Request $request) {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $schoolSetup = SchoolSetup::current();
        $terms = Term::query()
            ->orderByDesc('year')
            ->orderBy('term')
            ->get();

        $currentTerm = TermHelper::getCurrentTerm();
        $templateHeaders = SeniorAdmissionsImport::templateHeaders();
        $placementCriteria = $this->placementService()->criteriaForSchool($schoolSetup);
        $summaryTermId = (int) ($request->query('summary_term_id') ?: ($currentTerm?->id ?: $terms->first()?->id));
        $summaryTerm = $terms->firstWhere('id', $summaryTermId) ?? $currentTerm ?? $terms->first();
        $placementSummary = $summaryTerm
            ? $this->placementService()->buildTermSummary($summaryTerm->id, $schoolSetup)
            : [];

        return view('admissions.settings', compact(
            'terms',
            'currentTerm',
            'templateHeaders',
            'placementCriteria',
            'summaryTerm',
            'summaryTermId',
            'placementSummary'
        ));
    }

    public function placement(Request $request)
    {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $schoolSetup = SchoolSetup::current();
        $terms = Term::query()
            ->orderByDesc('year')
            ->orderBy('term')
            ->get();
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = (int) ($request->query('term_id') ?: ($currentTerm?->id ?: $terms->first()?->id));
        $selectedTerm = $terms->firstWhere('id', $selectedTermId) ?? $currentTerm ?? $terms->first();
        $placementCriteria = $this->placementService()->criteriaForSchool($schoolSetup);
        $classesByType = $selectedTerm
            ? $this->classesForPlacementTerm($selectedTerm->id)
            : collect();
        $placementGroups = $selectedTerm
            ? $this->placementService()->buildPlacementGroups($selectedTerm->id, $schoolSetup, $classesByType->keys())
            : [];

        $missingClassTypes = [];
        foreach ($placementGroups as $group) {
            if ($group['pathway'] === 'unclassified' || $group['count'] === 0) {
                continue;
            }
            $classType = $group['class_type'];
            if ($classType && collect($classesByType->get($classType, []))->isEmpty()) {
                $missingClassTypes[$group['pathway']] = [
                    'label' => $group['label'],
                    'class_type' => $classType,
                    'count' => $group['count'],
                ];
            }
        }

        return view('admissions.placement', compact(
            'terms',
            'currentTerm',
            'selectedTerm',
            'selectedTermId',
            'placementCriteria',
            'placementGroups',
            'classesByType',
            'missingClassTypes'
        ));
    }

    public function importSeniorAdmissions(Request $request) {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $validated = $request->validate([
            'term_id' => 'required|exists:terms,id',
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'delete_existing_term_admissions' => 'nullable|boolean',
        ]);

        $fileType = $this->getFileType($request->file('file')->getClientOriginalName());
        $missingHeaders = $this->missingSeniorImportHeaders($request->file('file'), $fileType);

        if (!empty($missingHeaders)) {
            return redirect()
                ->back()
                ->withErrors(['file' => 'The file is missing required headers: ' . implode(', ', $missingHeaders)])
                ->withInput();
        }

        if (!empty($validated['delete_existing_term_admissions'])) {
            $clearResult = $this->clearAdmissionsForTerm((int) $validated['term_id']);

            if (!$clearResult['success']) {
                return redirect()->back()->with('error', $clearResult['message']);
            }
        }

        $import = new SeniorAdmissionsImport((int) $validated['term_id'], auth()->id());

        try {
            Excel::import($import, $request->file('file'), null, $fileType);

            CacheHelper::forgetAdmissions();
            CacheHelper::forgetCurrentTermAdmissions();

            $successMessage = sprintf(
                'F4 admissions import completed. Total rows processed: %d, Successful imports: %d, Rows skipped: %d.',
                $import->rowsCount,
                $import->successfulImports,
                $import->skippedRows
            );

            $redirect = redirect()->route('admissions.settings')->with('message', $successMessage);

            if (!empty($import->skippedReasons)) {
                $redirect->with('warning', implode(' ', array_slice($import->skippedReasons, 0, 5)));
            }

            if ($import->failures()->isNotEmpty()) {
                $failureMessages = [];
                foreach ($import->failures() as $failure) {
                    $failureMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                }

                $redirect->withErrors($failureMessages);
            }

            return $redirect;
        } catch (\Exception $e) {
            Log::error('F4 admissions import failed', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Error importing F4 admissions: ' . $e->getMessage());
        }
    }

    public function create(){
        try {
            $currentTerm = TermHelper::getCurrentTerm();
            $selectedTermId = session('selected_term_id', $currentTerm?->id);

            if ($selectedTermId === null) {
                Log::error('No current term available in admissions create.');
                return back()->with('error', 'There was an issue with term selection.  Please try again.');
            }

            $terms = Term::all();
            $sponsors = CacheHelper::getSponsors();
            $nationalities = CacheHelper::getNationalities();

            $grades = CacheHelper::getGrades();

            if ($terms->isEmpty() || $sponsors->isEmpty() ||  $grades->isEmpty()) {
                $errorMessage = 'Data retrieval issue in admissions create: ' .
                                ($terms->isEmpty() ? 'Terms empty. ' : '') .
                                ($sponsors->isEmpty() ? 'Sponsors empty. ' : '') .
                                ($grades->isEmpty() ? 'Grades empty. ' : '');

                Log::warning($errorMessage); 
                return back()->with('error', 'Some data could not be loaded.');
            }

            return view('admissions.admission-new', compact('terms', 'sponsors', 'nationalities', 'grades'));

        } catch (\Exception $e) {
            Log::error('Error in admissions create: ' . $e->getMessage());
            return back()->with('error', 'A critical error occurred. Please contact support.');
        }
    }

    public function store(Request $request){
        return DB::transaction(function () use ($request) {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'gender' => 'required|in:M,F',
                'date_of_birth' => [
                    'required',
                    'date_format:d/m/Y',
                ],
                'nationality' => 'required|string|max:255',
                'sponsor_id' => 'required|exists:sponsors,id',
                'phone' => [
                    'required',
                    'string',
                    'max:15',
                    'regex:/^[0-9\s]+$/'
                ],
                'id_number' => [
                    'required',
                    'string',
                    'max:15',
                    Rule::unique('admissions', 'id_number')->whereNull('deleted_at')
                ],
                'grade_applying_for' => 'required|string|max:255',
                'application_date' => [
                    'required',
                    'date_format:d/m/Y',
                ],
                'status' => [
                    'required',
                    'string',
                    'max:255',
                    'in:Current,Left,To Join,Deleted'
                ],
                'last_updated_by' => 'required|integer',
                'term_id' => 'required|exists:terms,id',
                'year' => [
                    'required',
                    'integer',
                    'min:' . date('Y'),
                    'max:' . (date('Y') + 3)
                ],
            ], [
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'gender.in' => 'Gender must be either Male or Female',
                'date_of_birth.before' => 'Student must be at least 2 years old',
                'date_of_birth.after' => 'Student cannot be more than 30 years old',
                'phone.regex' => 'Phone number can only contain numbers and spaces',
                'id_number.unique' => 'This ID/Passport number is already registered',
                'sponsor_id.required' => 'Please select a parent/sponsor',
                'sponsor_id.exists' => 'Selected parent/sponsor is invalid',
                'application_date.before_or_equal' => 'Application date cannot be in the future',
                'year.min' => 'Year cannot be less than current year',
                'year.max' => 'Year cannot be more than 3 years in the future',
                'term_id.required' => 'Please select a term',
                'term_id.exists' => 'Selected term is invalid'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $request->merge([
                'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
                'application_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->application_date)->format('Y-m-d'),
            ]);

            try {
                $data = $request->all();
                $data['id_number'] = preg_replace('/\s+/', '', $request->id_number);
                $data['phone'] = preg_replace('/\s+/', '', $request->phone);

                $admission = Admission::create($data);
                CacheHelper::forgetAdmissions();

                Log::info('New admission created', [
                    'admission_id' => $admission->id,
                    'created_by' => auth()->id() ?? $request->last_updated_by,
                    'student_name' => $admission->first_name . ' ' . $admission->last_name
                ]);

                return redirect()->route('admissions.admissions-view',$admission->id)->with('message', 'Admission created successfully.');

            } catch (\Exception $e) {
                Log::error('Error creating admission: ' . $e->getMessage(), [
                    'request_data' => $request->except(['_token']),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'An error occurred while creating the admission. Please try again.')
                    ->withInput();
            }
        });
    }

    public function show($id){
        try {
            $admission = Admission::findOrFail($id);
            $this->syncAdmissionSponsorByConnectId($admission);

            $admission->load([
                'admissionAcademics',
                'admissionMedicals',
                'onlineAttachments',
                'seniorAdmissionAcademic',
            ]);
            $nationalities = CacheHelper::getNationalities();
            $terms = StudentController::terms();
            $sponsors = CacheHelper::getSponsors();
            $grades = CacheHelper::getGrades();

            $grade = Grade::where('active', 1)
                        ->where('name', $admission->grade_applying_for)
                        ->first();

            if (!$grade) {
                Log::warning("Grade not found for admission {$admission->id} (grade_applying_for: {$admission->grade_applying_for})");
                return back()->with('error', 'The grade associated with this admission could not be found.');
            }

            $classes = Klass::where('grade_id', $grade->id)
                ->orderBy('name')
                ->get();
            $placementRecommendation = null;
            $recommendedClasses = $classes;
            $alternativeClasses = collect();
            $hasRecommendedClassMatch = false;

            if (SchoolSetup::isSeniorSchool()) {
                $placementRecommendation = $this->placementService()->recommendForAdmission($admission);
                $classSplit = $this->placementService()->splitClassesByRecommendation($classes, $placementRecommendation);
                $recommendedClasses = $classSplit['recommended'];
                $alternativeClasses = $classSplit['alternatives'];
                $hasRecommendedClassMatch = $classSplit['has_exact_match'];
            }

            if ($nationalities === null || $terms === null || $sponsors->isEmpty()) {
                Log::warning('Data retrieval issue in admissions show: ' .
                            ($nationalities === null ? 'Nationalities null. ' : '') .
                            ($terms === null ? 'Terms null. ' : '') .
                            ($sponsors->isEmpty() ? 'Sponsors empty. ' : '')
                );
            }
            
            return view('admissions.admissions-view', compact(
                'admission',
                'nationalities',
                'terms',
                'sponsors',
                'classes',
                'grades',
                'placementRecommendation',
                'recommendedClasses',
                'alternativeClasses',
                'hasRecommendedClassMatch'
            ));

        } catch (\Exception $e) {
            Log::error("Error in Admission show (id: {$id}): " . $e->getMessage());
            return back()->with('error','An error occurred while retrieving the admission details.');
        }
    }

    public function deleteAdmission($id){
        try {
            $admission = Admission::findOrFail($id);
            $admission->delete();
            CacheHelper::forgetAdmissions();
            
            return back()->with('message', 'Admission deleted successfully!');
            
        } catch (ModelNotFoundException $e) {
            Log::warning("Attempt to delete non-existent admission (ID: {$id})");
            return back()->with('error', 'Admission not found.');
            
        } catch (\Exception $e) {
            Log::error("Error deleting admission (ID: {$id}): " . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the admission. Please try again later.');
        }
    }

    public function update(Request $request, $id){
        return DB::transaction(function () use ($request, $id) {
            $admission = Admission::find($id);
            if (!$admission) {
                return redirect()
                    ->back()
                    ->with('error', 'Admission record not found.')
                    ->withInput();
            }

            $this->syncAdmissionSponsorByConnectId($admission);

            if (!$request->filled('sponsor_id')) {
                $resolvedSponsorId = $this->resolveSponsorIdFromConnectId($admission->connect_id);
                if ($resolvedSponsorId !== null) {
                    $request->merge(['sponsor_id' => $resolvedSponsorId]);
                }
            }

            $sponsorRule = $admission->connect_id ? 'nullable|exists:sponsors,id' : 'required|exists:sponsors,id';
            $phoneRule = $admission->connect_id
                ? ['nullable', 'string', 'max:20', 'regex:/^[0-9\\s]+$/']
                : ['required', 'string', 'max:20', 'regex:/^[0-9\\s]+$/'];

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'gender' => 'required|in:M,F',
                'date_of_birth' => [
                    'required',
                    'date_format:d/m/Y',
                ],
                'nationality' => 'required|string|max:255',
                'sponsor_id' => $sponsorRule,
                'phone' => $phoneRule,
                'id_number' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('admissions')->ignore($id)->whereNull('deleted_at')
                ],
                'grade_applying_for' => 'required|string|max:255',
                'application_date' => [
                    'required',
                    'date_format:d/m/Y',
                ],
                'status' => [
                    'required',
                    'string',
                    'in:Current,Offer Accepted,New online,Pending,Left,To Join,Deleted'
                ],
                'last_updated_by' => 'required|integer',
                'term_id' => 'required|exists:terms,id',
                'year' => [
                    'required',
                    'integer',
                    'min:' . date('Y'),
                    'max:' . (date('Y') + 3),
                ],
            ], [
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'gender.in' => 'Gender must be either Male or Female',
                'date_of_birth.before' => 'Student must be at least 2 years old',
                'date_of_birth.after' => 'Student cannot be more than 30 years old',
                'phone.regex' => 'Phone number can only contain numbers and spaces',
                'id_number.unique' => 'This ID/Passport number is already in use by another student',
                'sponsor_id.required' => 'Please select a parent/sponsor',
                'sponsor_id.exists' => 'Selected parent/sponsor is invalid',
                'application_date.before_or_equal' => 'Application date cannot be in the future',
                'year.min' => 'Year cannot be less than current year',
                'year.max' => 'Year cannot be more than 5 years in the future',
                'term_id.required' => 'Please select a term',
                'term_id.exists' => 'Selected term is invalid'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $request->merge([
                'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
                'application_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->application_date)->format('Y-m-d'),
            ]);

            try {
                $oldStatus = $admission->status;
                
                $data = $request->all();
                $data['id_number'] = preg_replace('/\s+/', '', $request->id_number);
                $data['phone'] = $request->filled('phone')
                    ? preg_replace('/\s+/', '', $request->phone)
                    : null;
                
                $admission->update($data);
                CacheHelper::forgetAdmissions();

                Log::info('Admission updated', [
                    'admission_id' => $id,
                    'updated_by' => auth()->id() ?? $request->last_updated_by,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'student_name' => $admission->first_name . ' ' . $admission->last_name
                ]);

                return redirect()->back()->with('message', 'Admission updated successfully.');

            } catch (ModelNotFoundException $e) {
                Log::error('Admission not found', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                
                return redirect()
                    ->back()
                    ->with('error', 'Admission record not found.')
                    ->withInput();

            } catch (\Exception $e) {
                Log::error("Error updating admission (ID: {$id})", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->except(['_token'])
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'An error occurred while updating the admission. Please try again.')
                    ->withInput();
            }
        });
    }

    public function addAcademic($id){
        $user = User::find($id);
        return view('admissions.add-academic-information',['user' => $user]);
    }

    public function enrollAdmission(Request $request, $id){
        $validated = $request->validate([
            'klass_id' => 'required|exists:klasses,id',
        ]);

        try {
            $capacityWarnings = [];
            $classTermId = null;

            DB::transaction(function () use ($validated, $id, &$capacityWarnings, &$classTermId) {
                $admission = Admission::findOrFail($id);
                $class = Klass::findOrFail($validated['klass_id']);
                $classTermId = (int) $class->term_id;

                if (SchoolSetup::isSeniorSchool()) {
                    $placementClasses = $this->classesForPlacementTerm((int) $admission->term_id)->flatten()->keyBy('id');
                    $class = $placementClasses->get((int) $validated['klass_id']);

                    if (!$class) {
                        throw new \RuntimeException('Select a valid F4 class for this admission term.');
                    }

                    $recommendation = $this->placementService()->recommendForAdmission($admission);
                    $allocationError = $this->allocationEligibilityError($admission, $recommendation);
                    if ($allocationError !== null) {
                        throw new \RuntimeException($allocationError);
                    }
                }

                if ($this->classIsAtOrOverCapacity($class)) {
                    $capacityWarnings[] = $this->formatCapacityWarning($admission->full_name, $class->name);
                }

                $student = $this->createStudentFromAdmission($admission, $class);
                $this->enrollStudentInClass($student, $class);
            });

            CacheHelper::forgetAdmissions();
            if ($classTermId !== null) {
                CacheHelper::forgetStudentsCount($classTermId);
                CacheHelper::forgetStudentsTermData($classTermId);
            }
            CacheHelper::forgetStudentsData();

            $redirect = redirect()
                ->route('admissions.index')
                ->with('message', 'Student enrolled successfully!');

            if (!empty($capacityWarnings)) {
                $redirect->with('warning', $this->summarizeCapacityWarnings($capacityWarnings));
            }

            return $redirect;
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->withErrors(['message' => 'Admission or Class not found.']);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::info('Error occured' . $e->getMessage());
            return redirect()->back()->withErrors(['message' => 'An unexpected error occurred.']);
        }
    }

    private function createStudentFromAdmission(Admission $admission, Klass $class){
        $this->syncAdmissionSponsorByConnectId($admission);

        $student = new Student();
        $this->mapAdmissionToStudent($admission, $student);
        $student->status = 'Current';
        $student->year = $class->year;
        $student->last_updated_by = auth()->user()->fullName;
        $student->save();


        $admission->status = 'Enrolled';
        $admission->save();

        return $student;
    }

    private function enrollStudentInClass(Student $student, Klass $class){
        DB::table('student_term')->insert([
            'student_id' => $student->id,
            'term_id' => $class->term_id,
            'grade_id' => $class->grade_id,
            'year' => $class->year,
            'status' => $student->status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('klass_student')->insert([
            'klass_id' => $class->id,
            'student_id' => $student->id,
            'active' => 1,
            'term_id' => $class->term_id,
            'grade_id' => $class->grade_id,
            'year' => $class->year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    private function mapAdmissionToStudent(Admission $admission, Student $student){
        $fields = ['sponsor_id', 'connect_id', 'first_name', 'last_name', 'middle_name', 'gender', 'date_of_birth', 'nationality'];
        foreach ($fields as $field) {
            $student->$field = $admission->$field;
        }

        do {
            $idNumber = (string) random_int(10000000, 99999999);
        } while (Student::withTrashed()->where('id_number', $idNumber)->exists());

        $student->id_number = $idNumber;
        $student->password = bcrypt($idNumber);
    }
    
    public function insertOrUpdateAcademics(Request $request){
        $request->validate([
            'admission_id' => 'exists:admissions,id',
            'science' => 'nullable|string|regex:/^[A-F]+[+-]?$/',
            'mathematics' => 'nullable|string|regex:/^[A-F]+[+-]?$/',
            'english' => 'nullable|string|regex:/^[A-F]+[+-]?$/',
        ]);

        $data = $request->only(['admission_id', 'science', 'mathematics', 'english']);

        AdmissionAcademic::updateOrInsert(
            ['admission_id' => $request->input('admission_id')],
            $data
        );
        return redirect()->back()->with('message', 'Record added/updated successfully!');
    }

    public function insertOrUpdateSeniorAcademics(Request $request){
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $subjectFields = [
            'mathematics',
            'english',
            'science',
            'setswana',
            'design_and_technology',
            'home_economics',
            'agriculture',
            'social_studies',
            'moral_education',
            'music',
            'physical_education',
            'religious_education',
            'art',
            'office_procedures',
            'accounting',
            'french',
            'private_agriculture',
        ];

        $rules = [
            'admission_id' => 'required|exists:admissions,id',
            'overall' => 'nullable|in:A,B,C,D,M',
        ];

        foreach ($subjectFields as $field) {
            $rules[$field] = 'nullable|in:A,B,C,D,E,U';
        }

        $validated = $request->validate($rules);

        $data = collect($validated)
            ->except('admission_id')
            ->map(fn($value) => $value === '' ? null : $value)
            ->all();

        SeniorAdmissionAcademic::updateOrCreate(
            ['admission_id' => $validated['admission_id']],
            $data
        );

        return redirect()->back()->with('message', 'Senior academic grades saved successfully!');
    }

    public function allocatePlacementRecommendations(Request $request)
    {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $validated = $request->validate([
            'term_id' => 'required|exists:terms,id',
            'pathway' => 'required|string|in:triple,double,single',
            'selected_admissions' => 'required|array|min:1',
            'selected_admissions.*' => 'integer|exists:admissions,id',
            'allocations' => 'nullable|array',
            'allocations.*.klass_id' => 'nullable|integer|exists:klasses,id',
        ], [
            'selected_admissions.required' => 'Select at least one admission to allocate.',
            'pathway.required' => 'A pathway must be specified for allocation.',
        ]);

        $selectedIds = collect($validated['selected_admissions'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $termId = (int) $validated['term_id'];
        $schoolSetup = SchoolSetup::current();
        $classesByType = $this->classesForPlacementTerm($termId);
        $allF4Classes = $classesByType->flatten()->keyBy('id');
        $placementGroups = $this->placementService()->buildPlacementGroups($termId, $schoolSetup, $classesByType->keys());
        $group = collect($placementGroups)->firstWhere('pathway', $validated['pathway']);

        if (!$group || $group['pathway'] === 'unclassified') {
            return redirect()
                ->route('admissions.placement', ['term_id' => $termId])
                ->with('error', 'The selected pathway cannot be allocated.');
        }

        $groupStudentsById = collect($group['students'])->keyBy(fn(array $row) => (int) data_get($row, 'admission.id'));
        $allocations = $validated['allocations'] ?? [];
        $successCount = 0;
        $failures = [];
        $capacityWarnings = [];
        $manualAssignments = [];
        $pendingAutoRows = collect();

        foreach ($selectedIds as $admissionId) {
            $row = $groupStudentsById->get($admissionId);

            if (!$row) {
                $failures[] = "Admission {$admissionId} is not available in the selected {$group['label']} group.";
                continue;
            }

            /** @var Admission $admission */
            $admission = $row['admission'];
            $recommendation = $row['recommendation'] ?? $this->placementService()->recommendForAdmission($admission);
            $eligibilityError = $this->allocationEligibilityError($admission, $recommendation);
            if ($eligibilityError !== null) {
                $failures[] = $eligibilityError;
                continue;
            }

            $klassId = (int) data_get($allocations, "{$admissionId}.klass_id");
            if ($klassId > 0) {
                $class = $allF4Classes->get($klassId);
                if (!$class) {
                    $failures[] = "Select a valid F4 class in the selected term for {$admission->full_name}.";
                    continue;
                }

                $manualAssignments[$admissionId] = $class;
                continue;
            }

            $pendingAutoRows->push($row);
        }

        $projectedCounts = $allF4Classes->mapWithKeys(function ($class) {
            return [$class->id => (int) ($class->students_count ?? 0)];
        })->all();

        foreach ($manualAssignments as $class) {
            $projectedCounts[$class->id] = ($projectedCounts[$class->id] ?? 0) + 1;
        }

        $autoAssignments = [];
        if ($pendingAutoRows->isNotEmpty()) {
            $classType = $group['class_type'];
            $classesForType = collect($classesByType->get($classType, []))
                ->map(function ($klass) use ($projectedCounts) {
                    $clone = clone $klass;
                    $clone->students_count = $projectedCounts[$klass->id] ?? (int) ($klass->students_count ?? 0);

                    return $clone;
                })
                ->values();

            if ($classesForType->isEmpty()) {
                foreach ($pendingAutoRows as $row) {
                    $failures[] = "No {$classType} classes are available for {$row['admission']->full_name}. Choose an F4 class to override, or create a {$classType} class.";
                }
            } else {
                $distribution = $this->placementService()->autoDistributeToClasses($pendingAutoRows, $classesForType);
                $autoAssignments = $distribution['mapping'] ?? [];

                foreach ($pendingAutoRows as $row) {
                    $admissionId = (int) data_get($row, 'admission.id');
                    if (!isset($autoAssignments[$admissionId])) {
                        $failures[] = "{$row['admission']->full_name} could not be auto-assigned to a {$classType} class.";
                    }
                }
            }
        }

        $resolvedAssignments = [];
        foreach ($selectedIds as $admissionId) {
            if (isset($manualAssignments[$admissionId])) {
                $resolvedAssignments[$admissionId] = [
                    'class' => $manualAssignments[$admissionId],
                    'is_override' => true,
                ];
                continue;
            }

            if (isset($autoAssignments[$admissionId])) {
                $class = $allF4Classes->get((int) $autoAssignments[$admissionId]);
                if ($class) {
                    $resolvedAssignments[$admissionId] = [
                        'class' => $class,
                        'is_override' => false,
                    ];
                }
            }
        }

        $capacityCounts = $allF4Classes->mapWithKeys(function ($class) {
            return [$class->id => (int) ($class->students_count ?? 0)];
        })->all();

        foreach ($selectedIds as $admissionId) {
            $assignment = $resolvedAssignments[$admissionId] ?? null;
            $row = $groupStudentsById->get($admissionId);

            if (!$assignment || !$row) {
                continue;
            }

            /** @var Admission $admission */
            $admission = $row['admission'];
            /** @var Klass $class */
            $class = $assignment['class'];
            $currentCount = $capacityCounts[$class->id] ?? (int) ($class->students_count ?? 0);

            if ($this->classIsAtOrOverCapacity($class, $currentCount)) {
                $capacityWarnings[] = $this->formatCapacityWarning($admission->full_name, $class->name);
            }

            try {
                DB::transaction(function () use ($admission, $class) {
                    $student = $this->createStudentFromAdmission($admission, $class);
                    $this->enrollStudentInClass($student, $class);
                });

                $successCount++;
                $capacityCounts[$class->id] = $currentCount + 1;
            } catch (\Throwable $e) {
                Log::error('Failed to allocate placement recommendation', [
                    'admission_id' => $admission->id,
                    'klass_id' => $class->id,
                    'message' => $e->getMessage(),
                ]);

                $failures[] = "{$admission->full_name} could not be allocated: {$e->getMessage()}";
            }
        }

        if ($successCount > 0) {
            CacheHelper::forgetAdmissions();
            CacheHelper::forgetCurrentTermAdmissions();
            CacheHelper::forgetStudentsCount($termId);
            CacheHelper::forgetStudentsTermData($termId);
            CacheHelper::forgetStudentsData();
        }

        $redirect = redirect()->route('admissions.placement', ['term_id' => $termId]);

        if ($successCount > 0) {
            $pathwayLabel = $this->placementService()->labelForPathway($validated['pathway']);
            $redirect->with('message', "{$successCount} {$pathwayLabel} admissions allocated successfully.");
        }

        if (!empty($capacityWarnings)) {
            $redirect->with('warning', $this->summarizeCapacityWarnings($capacityWarnings));
        }

        if (!empty($failures)) {
            $redirect->withErrors($failures);
            $redirect->withInput();
        }

        if ($successCount === 0 && empty($failures)) {
            $redirect->with('error', 'No admissions were allocated.');
        }

        return $redirect;
    }

    public function storePlacementCriteria(Request $request)
    {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $schoolSetup = SchoolSetup::current();
        abort_unless($schoolSetup !== null, 404);

        $validator = Validator::make($request->all(), $this->placementCriteriaRules(), [
            'criteria.*.target_count.min' => 'Target count cannot be negative.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ([
                SeniorAdmissionPlacementCriteria::PATHWAY_TRIPLE,
                SeniorAdmissionPlacementCriteria::PATHWAY_DOUBLE,
            ] as $pathway) {
                $scienceBest = $request->input("criteria.{$pathway}.science_best_grade");
                $scienceWorst = $request->input("criteria.{$pathway}.science_worst_grade");
                $mathBest = $request->input("criteria.{$pathway}.mathematics_best_grade");
                $mathWorst = $request->input("criteria.{$pathway}.mathematics_worst_grade");

                if (!$this->placementService()->isBandOrdered($scienceBest, $scienceWorst)) {
                    $validator->errors()->add("criteria.{$pathway}.science_best_grade", 'Science best grade must be equal to or better than the science worst grade.');
                }

                if (!$this->placementService()->isBandOrdered($mathBest, $mathWorst)) {
                    $validator->errors()->add("criteria.{$pathway}.mathematics_best_grade", 'Mathematics best grade must be equal to or better than the mathematics worst grade.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('admissions.settings', ['summary_term_id' => $request->input('summary_term_id')])
                ->withErrors($validator)
                ->withInput();
        }

        $criteria = collect(SeniorAdmissionPlacementCriteria::PATHWAYS)
            ->mapWithKeys(function (string $pathway) use ($request) {
                return [$pathway => [
                    'science_best_grade' => $request->input("criteria.{$pathway}.science_best_grade"),
                    'science_worst_grade' => $request->input("criteria.{$pathway}.science_worst_grade"),
                    'mathematics_best_grade' => $request->input("criteria.{$pathway}.mathematics_best_grade"),
                    'mathematics_worst_grade' => $request->input("criteria.{$pathway}.mathematics_worst_grade"),
                    'science_ceiling_grade' => $request->input("criteria.{$pathway}.science_ceiling_grade"),
                    'promotion_pathway' => $request->input("criteria.{$pathway}.promotion_pathway"),
                    'target_count' => $request->input("criteria.{$pathway}.target_count", 0),
                    'is_active' => $request->boolean("criteria.{$pathway}.is_active"),
                ]];
            })
            ->all();

        $this->placementService()->persistCriteria($schoolSetup, $criteria);

        return redirect()
            ->route('admissions.settings', ['summary_term_id' => $request->input('summary_term_id')])
            ->with('message', 'Placement criteria saved successfully.');
    }

    public function resetPlacementCriteria(Request $request)
    {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $schoolSetup = SchoolSetup::current();
        abort_unless($schoolSetup !== null, 404);

        $this->placementService()->resetCriteria($schoolSetup);

        return redirect()
            ->route('admissions.settings', ['summary_term_id' => $request->input('summary_term_id')])
            ->with('message', 'Placement criteria reset to the default science placement rules.');
    }

    public function insertOrUpdateMedicals(Request $request){
        $request->validate([
            'admission_id' => 'exists:admissions,id', 
            'health_history' => 'nullable|string|max:255',
            'immunization_records' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'other_allergies' => 'nullable|string|max:255',
            'other_disabilities' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:255',
        ], [
            'immunization_records.max' => 'The immunization records file size must not exceed 10MB.'
        ]);
    
        $data = $request->except('_token', 'immunization_records');
    
        try {
            if ($request->hasFile('immunization_records')) {
                $file = $request->file('immunization_records');
                $fileName = 'immunization-' . time() . '.' . $file->extension();
                $filePath = $file->storeAs('public/immunization', $fileName);
                $data['immunization_records'] = $filePath;
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['immunization_records' => 'Failed to upload the file. Please ensure the file size is less than 10MB and try again.']);
        }
    
        $booleanFields = [
            'peanuts', 'red_meat', 'vegetarian',
            'left_leg', 'right_leg', 'left_hand', 'right_hand',
            'left_eye', 'right_eye', 'left_ear', 'right_ear'
        ];
    
        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field);
        }
    
        AdmissionHealthInformation::updateOrInsert(
            ['admission_id' => $request->input('admission_id')],
            $data
        );
    
        return redirect()->back()->with('message', 'Medical information added/updated successfully!');
    }
    


    public function admissionsByStatus(){
        $school_data = SchoolSetup::first();
        $admissionsReport = Admission::select('status', DB::raw('count(*) as total'))
                              ->groupBy('status')
                              ->orderBy('total', 'desc')
                              ->get();
        return view('admissions.admissions-statistical-grade',['admissionsReport' => $admissionsReport,'school_data' => $school_data]);
    }

    public function statusAndNames(){
        $admissionsByStatus = Admission::select('first_name', 'last_name','gender','date_of_birth','status','year')
                                    ->orderBy('status')
                                    ->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->get()
                                    ->groupBy('status');
        $school_data = SchoolSetup::first();
        return view('admissions.admissions-status-grade',['admissionsByStatus' => $admissionsByStatus,'school_data' => $school_data]);
    }


    public function statusAndNamesExport(){
        try {
            $data = Admission::select('first_name', 'last_name', 'gender', 'date_of_birth', 'status', 'year')
                            ->orderBy('status')
                            ->orderBy('last_name')
                            ->orderBy('first_name')
                            ->get()
                            ->groupBy('status');
            return Excel::download(new AdmissionByStatusExport($data), 'admissions-status-export.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Error occurred: ' . $e->getMessage());
        }
    }
    

    public function getGradeAnalysisByGender(){
        $gradeGenderCounts = Admission::select('grade_applying_for', 'gender', DB::raw('count(*) as total'))
                              ->groupBy('grade_applying_for', 'gender')->orderBy('grade_applying_for')
                              ->get();
        $school_data = SchoolSetup::first();
        $gradesAnalysis = [];
        $pieChartData = [];

        foreach ($gradeGenderCounts as $item) {
            $grade = $item->grade_applying_for;
            $gender = strtoupper(trim($item->gender));
            $count = $item->total;

            if (!isset($gradesAnalysis[$grade])) {
                $gradesAnalysis[$grade] = ['total' => 0, 'boys' => 0, 'girls' => 0];
            }
        
            $gradesAnalysis[$grade]['total'] += $count;
        
            if ($gender === 'M') {
                $gradesAnalysis[$grade]['boys'] += $count;
            } elseif ($gender === 'F') {
                $gradesAnalysis[$grade]['girls'] += $count;
            }
        }
        
        foreach ($gradesAnalysis as $grade => $data) {
            $pieChartData[] = [
                'label' => $grade . ' Boys',
                'count' => $data['boys']
            ];
            $pieChartData[] = [
                'label' => $grade . ' Girls',
                'count' => $data['girls']
            ];
        }
        return view('admissions.admissions-analysis-grade',[
            'gradesAnalysis' => $gradesAnalysis,
            'school_data' => $school_data,
            'pieChartData' => $pieChartData,
        ]);
    }

    private function missingSeniorImportHeaders($file, string $fileType): array {
        $headerRows = (new HeadingRowImport())->toArray($file, null, $fileType);
        $headers = collect($headerRows[0][0] ?? [])
            ->filter(fn($header) => is_string($header) && $header !== '')
            ->values()
            ->all();

        return array_values(array_diff(SeniorAdmissionsImport::templateHeaders(), $headers));
    }

    private function clearAdmissionsForTerm(int $termId): array {
        DB::beginTransaction();

        try {
            Admission::withTrashed()
                ->where('term_id', $termId)
                ->get()
                ->each(fn(Admission $admission) => $admission->forceDelete());

            DB::commit();
            CacheHelper::forgetAdmissions();
            CacheHelper::forgetCurrentTermAdmissions();

            return [
                'success' => true,
                'message' => 'Admissions for the selected term were cleared successfully.',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to clear admissions for term', [
                'term_id' => $termId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error clearing admissions for the selected term: ' . $e->getMessage(),
            ];
        }
    }

    private function getFileType(string $filename): string {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            default => throw new \InvalidArgumentException("Invalid file type for {$filename}"),
        };
    }

    private function syncAdmissionSponsorByConnectId(Admission $admission): void {
        if ($admission->sponsor_id || !$admission->connect_id) {
            return;
        }

        $resolvedSponsorId = $this->resolveSponsorIdFromConnectId($admission->connect_id);
        if ($resolvedSponsorId === null) {
            return;
        }

        $admission->forceFill(['sponsor_id' => $resolvedSponsorId])->saveQuietly();
    }

    private function resolveSponsorIdFromConnectId(?string $connectId): ?int {
        if (!$connectId) {
            return null;
        }

        return Sponsor::query()
            ->where('connect_id', $connectId)
            ->value('id');
    }

    private function placementService(): SeniorAdmissionPlacementService
    {
        return app(SeniorAdmissionPlacementService::class);
    }

    private function classesForPlacementTerm(int $termId)
    {
        return Klass::query()
            ->where('term_id', $termId)
            ->whereIn('type', Klass::TYPES)
            ->with('grade')
            ->withCount(['students' => fn($q) => $q->where('klass_student.active', 1)])
            ->whereHas('grade', function ($query) {
                $query->where('name', 'F4');
            })
            ->orderBy('name')
            ->get()
            ->groupBy('type');
    }

    private function allocationEligibilityError(Admission $admission, array $recommendation): ?string
    {
        if (in_array($admission->status, ['Enrolled', 'Deleted'], true)) {
            return "{$admission->full_name} cannot be allocated because the admission status is {$admission->status}.";
        }

        if (($recommendation['pathway'] ?? null) === 'unclassified') {
            return "{$admission->full_name} cannot be allocated until Science and Mathematics grades are complete.";
        }

        return null;
    }

    private function classIsAtOrOverCapacity(Klass $class, ?int $currentCount = null): bool
    {
        if ($class->max_students === null) {
            return false;
        }

        $currentCount ??= (int) ($class->students_count ?? 0);

        return $currentCount >= (int) $class->max_students;
    }

    private function formatCapacityWarning(string $studentName, string $className): string
    {
        return "{$studentName} was placed into {$className}, which is already at or above its Max Students setting.";
    }

    private function summarizeCapacityWarnings(array $warnings): string
    {
        $warnings = array_values(array_unique($warnings));
        $count = count($warnings);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return 'Capacity warning: ' . $warnings[0];
        }

        $preview = implode(' ', array_slice($warnings, 0, 2));

        return "Capacity warning: {$count} placements were made into classes already at or above their Max Students setting. {$preview}";
    }

    public function updateClassCapacity(Request $request)
    {
        abort_unless(SchoolSetup::isSeniorSchool(), 404);

        $validated = $request->validate([
            'capacities' => 'required|array',
            'capacities.*.klass_id' => 'required|integer|exists:klasses,id',
            'capacities.*.max_students' => 'nullable|integer|min:0',
            'term_id' => 'required|exists:terms,id',
        ]);

        $updated = 0;

        DB::transaction(function () use ($validated, &$updated) {
            foreach ($validated['capacities'] as $entry) {
                $klass = Klass::find($entry['klass_id']);
                if ($klass) {
                    $klass->update(['max_students' => $entry['max_students']]);
                    $updated++;
                }
            }
        });

        return redirect()
            ->route('admissions.placement', ['term_id' => $validated['term_id']])
            ->with('message', "{$updated} class capacities updated.");
    }

    private function placementCriteriaRules(): array
    {
        $gradeRule = 'nullable|in:A,B,C,D,E,U';
        $requiredGradeRule = 'required|in:A,B,C,D,E,U';
        $pathwayRule = 'nullable|in:' . implode(',', SeniorAdmissionPlacementCriteria::PATHWAYS);
        $rules = [
            'summary_term_id' => 'nullable|exists:terms,id',
        ];

        foreach (SeniorAdmissionPlacementCriteria::PATHWAYS as $pathway) {
            $isFallbackPathway = $pathway === SeniorAdmissionPlacementCriteria::PATHWAY_SINGLE;
            $rules["criteria.{$pathway}.science_best_grade"] = $isFallbackPathway ? $gradeRule : $requiredGradeRule;
            $rules["criteria.{$pathway}.science_worst_grade"] = $isFallbackPathway ? $gradeRule : $requiredGradeRule;
            $rules["criteria.{$pathway}.mathematics_best_grade"] = $isFallbackPathway ? $gradeRule : $requiredGradeRule;
            $rules["criteria.{$pathway}.mathematics_worst_grade"] = $isFallbackPathway ? $gradeRule : $requiredGradeRule;
            $rules["criteria.{$pathway}.science_ceiling_grade"] = $gradeRule;
            $rules["criteria.{$pathway}.promotion_pathway"] = $pathwayRule;
            $rules["criteria.{$pathway}.target_count"] = 'required|integer|min:0';
            $rules["criteria.{$pathway}.is_active"] = 'nullable|boolean';
        }

        return $rules;
    }

}
