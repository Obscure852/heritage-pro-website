<?php

namespace App\Http\Controllers;

use App\Exports\ClassGroupingExport;
use App\Exports\HouseAnalysisReportExport;
use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use App\Http\Requests\Houses\RemoveHouseUsersRequest;
use App\Http\Requests\Houses\SaveHouseRequest;
use App\Http\Requests\Houses\SyncHouseUsersRequest;
use App\Models\Grade;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Klass;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\Term;
use App\Models\Test;
use App\Models\ValueAdditionSubjectMapping;
use App\Services\HouseMembershipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class HouseController extends Controller{

    public function __construct(private readonly HouseMembershipService $houseMembershipService) {
        $this->middleware('auth');
    }
    
    public function index(){

        $currentYear = Carbon::now()->year;
        $currentTerm = Term::where('year', $currentYear)->orderBy('id', 'desc')->first();

        $terms = StudentController::terms();
        $currentTerm = TermHelper::getCurrentTerm();
        return view('houses.index',['currentTerm' => $currentTerm,'terms' => $terms]);
    }

    /**
     * Fix students incorrectly assigned to old-term CHAMPIONS houses for Term 3, 2025.
     * These students were assigned to House ID 1 (Term 1 CHAMPIONS) or House ID 4 (Term 2 CHAMPIONS)
     * with pivot term_id=3, instead of House ID 7 (Term 3 CHAMPIONS).
     */
    private function fixWrongTermChampionsAssignments()
    {
        $term3Id = 3; // Term 3, 2025
        $correctChampionsHouseId = 7; // CHAMPIONS for Term 3, 2025
        $wrongHouseIds = [1, 4]; // Old CHAMPIONS houses from Term 1 and Term 2

        // Check if there are any wrong assignments to fix
        $wrongAssignments = DB::table('student_house')
            ->where('term_id', $term3Id)
            ->whereIn('house_id', $wrongHouseIds)
            ->count();

        if ($wrongAssignments === 0) {
            return; // No wrong assignments to fix
        }

        DB::transaction(function () use ($term3Id, $correctChampionsHouseId, $wrongHouseIds) {
            // Get student IDs with wrong assignments
            $studentsToFix = DB::table('student_house')
                ->where('term_id', $term3Id)
                ->whereIn('house_id', $wrongHouseIds)
                ->pluck('student_id')
                ->unique()
                ->toArray();

            // Check which students already have a correct Term 3 house assignment
            $studentsWithCorrectAssignment = DB::table('student_house')
                ->where('term_id', $term3Id)
                ->whereIn('house_id', [7, 8, 9]) // Term 3 houses
                ->whereIn('student_id', $studentsToFix)
                ->pluck('student_id')
                ->unique()
                ->toArray();

            // Students who need a new correct assignment (only have wrong assignments)
            $studentsNeedingNewAssignment = array_diff($studentsToFix, $studentsWithCorrectAssignment);

            // Delete all wrong assignments for Term 3
            DB::table('student_house')
                ->where('term_id', $term3Id)
                ->whereIn('house_id', $wrongHouseIds)
                ->delete();

            // Create correct assignments for students who don't have one
            $now = now();
            $newAssignments = [];
            foreach ($studentsNeedingNewAssignment as $studentId) {
                $newAssignments[] = [
                    'student_id' => $studentId,
                    'house_id' => $correctChampionsHouseId,
                    'term_id' => $term3Id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($newAssignments)) {
                DB::table('student_house')->insert($newAssignments);
            }

            Log::info('Fixed wrong-term CHAMPIONS house assignments', [
                'term_id' => $term3Id,
                'deleted_wrong_assignments' => count($studentsToFix),
                'students_already_had_correct' => count($studentsWithCorrectAssignment),
                'new_assignments_created' => count($studentsNeedingNewAssignment),
            ]);
        });
    }

    /**
     * Fix students incorrectly assigned to old-term WINNERS houses for Term 3, 2025.
     * These students were assigned to House ID 2 (Term 1 WINNERS) or House ID 5 (Term 2 WINNERS)
     * with pivot term_id=3, instead of House ID 8 (Term 3 WINNERS).
     *
     * Actions:
     * - Delete duplicate wrong assignments (students who have both wrong and correct)
     * - Fix invisible students (students who only have wrong assignments)
     */
    private function fixWrongTermWinnersAssignments()
    {
        $term3Id = 3; // Term 3, 2025
        $correctWinnersHouseId = 8; // WINNERS for Term 3, 2025
        $wrongHouseIds = [2, 5]; // Old WINNERS houses from Term 1 and Term 2

        // Check if there are any wrong assignments to fix
        $wrongAssignments = DB::table('student_house')
            ->where('term_id', $term3Id)
            ->whereIn('house_id', $wrongHouseIds)
            ->count();

        if ($wrongAssignments === 0) {
            return; // No wrong assignments to fix
        }

        DB::transaction(function () use ($term3Id, $correctWinnersHouseId, $wrongHouseIds) {
            // Get student IDs with wrong assignments
            $studentsToFix = DB::table('student_house')
                ->where('term_id', $term3Id)
                ->whereIn('house_id', $wrongHouseIds)
                ->pluck('student_id')
                ->unique()
                ->toArray();

            // Check which students already have a correct Term 3 house assignment
            $studentsWithCorrectAssignment = DB::table('student_house')
                ->where('term_id', $term3Id)
                ->whereIn('house_id', [7, 8, 9]) // Term 3 houses (CHAMPIONS, WINNERS, TITANS)
                ->whereIn('student_id', $studentsToFix)
                ->pluck('student_id')
                ->unique()
                ->toArray();

            // Students who need a new correct assignment (only have wrong assignments)
            $studentsNeedingNewAssignment = array_diff($studentsToFix, $studentsWithCorrectAssignment);

            // Delete all wrong assignments for Term 3
            DB::table('student_house')
                ->where('term_id', $term3Id)
                ->whereIn('house_id', $wrongHouseIds)
                ->delete();

            // Create correct assignments for students who don't have one
            $now = now();
            $newAssignments = [];
            foreach ($studentsNeedingNewAssignment as $studentId) {
                $newAssignments[] = [
                    'student_id' => $studentId,
                    'house_id' => $correctWinnersHouseId,
                    'term_id' => $term3Id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($newAssignments)) {
                DB::table('student_house')->insert($newAssignments);
            }

            Log::info('Fixed wrong-term WINNERS house assignments', [
                'term_id' => $term3Id,
                'deleted_wrong_assignments' => count($studentsToFix),
                'students_already_had_correct' => count($studentsWithCorrectAssignment),
                'new_assignments_created' => count($studentsNeedingNewAssignment),
            ]);
        });
    }

    private function selectedTermId(): int
    {
        return (int) session('selected_term_id', TermHelper::getCurrentTerm()->id);
    }

    private function buildHouseClassSummaries($houses)
    {
        return $houses->map(function ($house) {
            $classes = $house->students
                ->pluck('currentClassRelation')
                ->flatten()
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();

            return [
                'house' => $house,
                'classes' => $classes,
            ];
        });
    }

    private function houseListingQuery(int $termId)
    {
        return House::query()
            ->withCount(['students', 'users'])
            ->with([
                'houseHead:id,firstname,lastname',
                'houseAssistant:id,firstname,lastname',
                'students' => function ($query) use ($termId) {
                    $query->whereHas('studentTerms', function ($studentTermsQuery) use ($termId) {
                        $studentTermsQuery->where('term_id', $termId)
                            ->where('status', 'Current');
                    })->with([
                        'currentClassRelation' => function ($classQuery) {
                            $classQuery->select('klasses.id', 'klasses.name', 'klasses.type');
                        },
                    ]);
                },
            ])
            ->where('term_id', $termId)
            ->orderBy('name');
    }


    public function getTermData(){
        $termId = $this->selectedTermId();
        $houses = $this->houseListingQuery($termId)->get();
        $housesWithClasses = $this->buildHouseClassSummaries($houses);

        return view('houses.house-term',['houses' => $houses, 'housesWithClasses' => $housesWithClasses]);
    }

    public function show(){
        $teachers = CacheHelper::getUsers();
        return view('houses.add-new-house',['users' => $teachers]);
    }

    public function store(SaveHouseRequest $request){
        try {
            $validatedData = $request->validated();

            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return redirect()->back()->with('error', 'Current term not found. Please configure the current term before proceeding.');
            }

            $house = new House;
            $house->name = $validatedData['name'];
            $house->color_code = $validatedData['color_code'];
            $house->head = $validatedData['head'];
            $house->assistant = $validatedData['assistant'];
            $house->term_id = $currentTerm->id;
            $house->year = $validatedData['year'];
            $house->save();

            return redirect()->back()->with('message', 'House created successfully.');

        } catch (ValidationException $e) {
            Log::error('Validation error while creating house', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while creating a house', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred. Could not create the house. Please try again later.');
        }
    }


    public function updateHouse(SaveHouseRequest $request, $houseId){
        try {
            $validatedData = $request->validated();

            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                return redirect()->back()->with('error', 'Current term not found. Please configure the current term before proceeding.');
            }

            $house = House::find($houseId);
            if (!$house) {
                return redirect()->back()->with('error', 'House not found!');
            }

            $house->name = $validatedData['name'];
            $house->color_code = $validatedData['color_code'];
            $house->head = $validatedData['head'];
            $house->assistant = $validatedData['assistant'];
            $house->term_id = $currentTerm->id;
            $house->year = $validatedData['year'];
            $house->save();

            return redirect()->back()->with('message', 'House updated successfully.');

        } catch (ValidationException $e) {
            Log::error('Validation error while updating house', [
                'errors' => $e->errors(),
                'house_id' => $houseId,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while updating a house', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'house_id' => $houseId,
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'An unexpected error occurred. Could not update the house. Please try again later.');
        }
    }

    function allocateStudents($id) {
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $house = House::find($id);

        if (!$house) {
            return redirect()->route('house.index')->with('error', 'House not found.');
        }

        // Validate: The house should belong to the selected term
        // If not, try to find the correct house for this term with the same name
        if ($house->term_id !== $termId) {
            $correctHouse = House::where('name', $house->name)
                ->where('term_id', $termId)
                ->first();

            if ($correctHouse) {
                // Redirect to the correct house for this term
                return redirect()->route('house.open-house', ['id' => $correctHouse->id]);
            } else {
                return redirect()->route('house.index')->with('error',
                    "The house '{$house->name}' does not exist for the selected term. " .
                    "Please create the house for this term first or select a different term."
                );
            }
        }

        $students = CacheHelper::getUnallocatedHouseStudents($termId);
        return view('houses.house-list', [
            'students' => $students,
            'house' => $house
        ]);
    }

    public function allocateUsers($id)
    {
        $termId = $this->selectedTermId();
        $house = House::find($id);

        if (!$house) {
            return redirect()->route('house.index')->with('error', 'House not found.');
        }

        if ($house->term_id !== $termId) {
            $correctHouse = House::where('name', $house->name)
                ->where('term_id', $termId)
                ->first();

            if ($correctHouse) {
                return redirect()->route('house.open-house-users', ['id' => $correctHouse->id]);
            }

            return redirect()->route('house.index')->with('error',
                "The house '{$house->name}' does not exist for the selected term. Please create the house for this term first or select a different term."
            );
        }

        $users = CacheHelper::getUnallocatedHouseUsers($termId);

        return view('houses.allocate-users-to-house', [
            'users' => $users,
            'house' => $house,
        ]);
    }

    public function moveStudents(Request $request, $id){
        $studentsIds = array_filter($request->input('students', []), function ($value) {
            return $value !== '0';
        });

        if (empty($studentsIds)) {
            return redirect()->back()->withErrors(['error' => 'No students were selected for allocation. Please select at least one student.']);
        }

        // Use the selected term from session, not the current term
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::find($selectedTermId);

        if (!$selectedTerm) {
            return redirect()->back()->with('error', 'The selected term not found. Please select a valid term.');
        }

        $house = House::find($id);
        if (!$house) {
            return redirect()->back()->with('error', 'House not found, Sorry!');
        }

        // Validate: The house must belong to the selected term
        if ($house->term_id !== $selectedTermId) {
            Log::warning('House allocation mismatch detected', [
                'house_id' => $id,
                'house_term_id' => $house->term_id,
                'selected_term_id' => $selectedTermId,
                'user_id' => auth()->id(),
            ]);

            // Find the correct house for this term with the same name
            $correctHouse = House::where('name', $house->name)
                ->where('term_id', $selectedTermId)
                ->first();

            if ($correctHouse) {
                $house = $correctHouse;
                Log::info('Auto-corrected to correct term house', [
                    'corrected_house_id' => $correctHouse->id,
                    'term_id' => $selectedTermId,
                ]);
            } else {
                return redirect()->back()->with('error',
                    "Cannot allocate students: The house '{$house->name}' does not exist for the selected term. " .
                    "Please ensure you are viewing the correct term or create the house for this term first."
                );
            }
        }

        // Check for students already allocated to a house in this term
        $alreadyAllocated = DB::table('student_house')
            ->where('term_id', $selectedTermId)
            ->whereIn('student_id', $studentsIds)
            ->pluck('student_id')
            ->toArray();

        if (!empty($alreadyAllocated)) {
            $studentsIds = array_diff($studentsIds, $alreadyAllocated);
            if (empty($studentsIds)) {
                return redirect()->back()->with('error', 'All selected students are already allocated to a house for this term.');
            }
            Log::info('Some students already allocated, skipping', [
                'skipped_count' => count($alreadyAllocated),
                'proceeding_count' => count($studentsIds),
            ]);
        }

        DB::transaction(function () use ($studentsIds, $house, $selectedTermId) {
            $pivotData = [];
            $now = now();
            foreach ($studentsIds as $studentId) {
                $pivotData[$studentId] = [
                    'term_id' => $selectedTermId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            $house->students()->attach($pivotData);
            CacheHelper::forgetUnallocatedHouseStudents($selectedTermId);
        });

        $message = 'Students added successfully!';
        if (!empty($alreadyAllocated)) {
            $message .= ' (' . count($alreadyAllocated) . ' students were already allocated and skipped)';
        }

        return redirect()->back()->with('message', $message);
    }

    public function moveUsers(SyncHouseUsersRequest $request, $id)
    {
        $selectedTermId = $this->selectedTermId();
        $house = House::find($id);

        if (!$house) {
            return redirect()->back()->with('error', 'House not found, Sorry!');
        }

        try {
            $result = $this->houseMembershipService->allocateUsers(
                $house,
                $request->validated('users'),
                $selectedTermId
            );
        } catch (ValidationException $exception) {
            return redirect()->back()
                ->withErrors($exception->errors())
                ->withInput()
                ->with('error', collect($exception->errors())->flatten()->first());
        }

        $message = 'Users added successfully!';
        if ($result['skipped_count'] > 0) {
            $message .= ' (' . $result['skipped_count'] . ' users were already allocated and skipped)';
        }

        return redirect()->back()->with('message', $message);
    }


    public function deleteMultipleStudents(Request $request, $houseId){
        try {
            DB::transaction(function () use ($request, $houseId) {
                $house = House::findOrFail($houseId);
                $studentIds = $request->input('students', []);
                
                if (empty($studentIds)) {
                    throw new \Exception('No students selected.');
                }

                $house->students()->detach($studentIds);
                CacheHelper::forgetUnallocatedHouseStudents($house->term_id);
            });

            return redirect()->back()->with('message', 'Selected students removed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error removing students: ' . $e->getMessage());
        }
    }


    public function deleteStudent($houseId, $studentId){
        $house = House::find($houseId);
        $student = Student::find($studentId);
        if (!$house) {
            return redirect()->back()->with('error', 'House not found!');
        }

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found!');
        }
    
        if (!$house->students()->find($studentId)) {
            return redirect()->back()->with('error', 'Student is not part of this house!');
        }
        $house->students()->detach($studentId);
        CacheHelper::forgetUnallocatedHouseStudents($house->term_id);
        return redirect()->back()->with('message', 'Student deleted successfully!');
    }

    public function deleteMultipleUsers(RemoveHouseUsersRequest $request, $houseId)
    {
        try {
            $house = House::findOrFail($houseId);
            $deletedCount = $this->houseMembershipService->removeUsers($house, $request->validated('users'));

            if ($deletedCount === 0) {
                return redirect()->back()->with('error', 'No selected users were attached to this house.');
            }

            return redirect()->back()->with('message', 'Selected users removed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error removing users: ' . $e->getMessage());
        }
    }

    public function deleteUser($houseId, $userId)
    {
        $house = House::find($houseId);
        $user = User::find($userId);

        if (!$house) {
            return redirect()->back()->with('error', 'House not found!');
        }

        if (!$user) {
            return redirect()->back()->with('error', 'User not found!');
        }

        try {
            $this->houseMembershipService->removeUser($house, $userId);
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->back()->with('message', 'User removed successfully!');
    }



    public function getHouseData($houseId){
        $termId = $this->selectedTermId();
        $house = House::query()
            ->withCount(['students', 'users'])
            ->with([
                'houseHead:id,firstname,lastname',
                'houseAssistant:id,firstname,lastname',
                'students' => function ($query) use ($termId) {
                    $query->whereHas('studentTerms', function ($studentTermsQuery) use ($termId) {
                        $studentTermsQuery->where('term_id', $termId)
                            ->where('status', 'Current');
                    })->with([
                        'type',
                        'currentClassRelation' => function ($classQuery) {
                            $classQuery->with('grade');
                        },
                    ])->orderBy('first_name')->orderBy('last_name');
                },
                'users' => function ($query) {
                    $query->orderBy('firstname')->orderBy('lastname');
                },
            ])
            ->findOrFail($houseId);

        return view('houses.view-house-students',['house' => $house]);
    }


    public function editHouse($houseId){
        $house = House::findOrFail($houseId);
        $teachers = CacheHelper::getUsers();
        return view('houses.edit-house',['house' => $house,'users' => $teachers]);
    }

    public function getContacts(){
        return view('houses.apps-contacts-list');
    }

    //House Analysis reports
    public function houseReport(){
        $selectedTermId = $this->selectedTermId();
        $houses = House::withCount('students')
                       ->withCount('users')
                       ->with(['houseHead', 'houseAssistant'])
                       ->where('term_id', $selectedTermId)
                       ->orderBy('name')
                       ->get();
    
        $school_data = SchoolSetup::first();
        $chartData = $houses->map(function ($house) {
            return [
                'name' => $house->name,
                'value' => $house->students_count,
                'itemStyle' => [
                    'color' => $house->color_code,
                ],
            ];
        });
    
        return view('houses.houses-analysis', [
            'houses' => $houses,
            'school_data' => $school_data,
            'chartData' => $chartData
        ]);
    }
    
    //House Analysis reports
    public function studentsHouseAnalysis(){
        $selectedTermId = $this->selectedTermId();
        $houses = House::withCount(['students', 'users'])
                        ->with([
                            'houseHead',
                            'houseAssistant',
                            'students' => function ($query) use ($selectedTermId) {
                                $query->whereHas('studentTerms', function ($studentTermsQuery) use ($selectedTermId) {
                                    $studentTermsQuery->where('term_id', $selectedTermId)
                                        ->where('status', 'Current');
                                })->with('currentClassRelation.grade')
                                  ->orderBy('first_name')
                                  ->orderBy('last_name');
                            },
                        ])
                        ->where('term_id', $selectedTermId)
                        ->orderBy('name')
                        ->get();
    
        $school_data = SchoolSetup::first();
        return view('houses.students-house-analysis', [
            'houses' => $houses,
            'school_data' => $school_data,
        ]);
    }


    public function studentsHouseAnalysisExport(){
        try {
            $selectedTermId = $this->selectedTermId();
            $data = House::withCount(['students', 'users'])
                        ->with([
                            'houseHead',
                            'houseAssistant',
                            'students' => function ($query) use ($selectedTermId) {
                                $query->whereHas('studentTerms', function ($studentTermsQuery) use ($selectedTermId) {
                                    $studentTermsQuery->where('term_id', $selectedTermId)
                                        ->where('status', 'Current');
                                })->with('currentClassRelation.grade')
                                  ->orderBy('first_name')
                                  ->orderBy('last_name');
                            },
                        ])
                        ->where('term_id', $selectedTermId)
                        ->orderBy('name')
                        ->get();
            return Excel::download(new HouseAnalysisReportExport($data), 'students-houses-analysis-export.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Error occurred: ' . $e->getMessage());
        }
    }


    public function deleteHouse($houseId){
        $house = House::findOrFail($houseId);
        if ($house->students()->count() > 0) {
            return redirect()->back()->with('error', 'House has students, delete students first!');
        }

        if ($house->users()->count() > 0) {
            return redirect()->back()->with('error', 'House has users, remove user allocations first!');
        }

        try {
            DB::transaction(function () use ($house) {
                $house->delete();
            });

            return redirect()->back()->with('message', 'House deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the house.');
        }
    }

    public function showClassGrouping(Request $request, $classId, $sequenceId, $type){
        $klass = Klass::findOrFail($classId);
        $currentTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        $test = Test::where('term_id', $currentTermId)
            ->where('grade_id', $klass->grade_id)
            ->where('sequence', $sequenceId)
            ->where('type', $type)
            ->first();

        $jceMappings = ValueAdditionSubjectMapping::where('school_type', 'Senior')
            ->where('exam_type', 'JCE')
            ->where('is_active', true)
            ->get()
            ->keyBy('subject_id')
            ->map(fn($m) => $m->source_key)
            ->toArray();

        $gradeNames = Grade::pluck('name', 'id');
        $houses = House::with([
            'houseHead',
            'houseAssistant',
            'students' => function ($query) use ($currentTermId, $type, $sequenceId, $klass) {
                $query->whereHas('studentTerms', function ($q) use ($currentTermId) {
                        $q->where('term_id', $currentTermId)->where('status', 'Current');
                    })
                    ->whereHas('currentClassRelation', function ($q) use ($currentTermId, $klass) {
                        $q->where('klasses.term_id', $currentTermId)
                        ->where('klasses.grade_id', $klass->grade_id);
                    })
                    ->with([
                        'currentClassRelation' => function ($q) use ($currentTermId, $klass) {
                            $q->select('klasses.id', 'klasses.name', 'klasses.type', 'klasses.grade_id')
                            ->where('klasses.term_id', $currentTermId)
                            ->where('klasses.grade_id', $klass->grade_id);
                        },
                        'tests' => function ($q) use ($currentTermId, $type, $sequenceId) {
                            $q->where('tests.term_id', $currentTermId)
                            ->where('tests.type', $type)
                            ->where('tests.sequence', '<=', $sequenceId)
                            ->withPivot('score', 'grade', 'points')
                            ->with('subject.subject');
                        },
                        'studentTerms' => function ($q) use ($currentTermId) {
                            $q->where('term_id', $currentTermId);
                        },
                        'jce',
                    ]);
            },
        ])->where('term_id', $currentTermId)->get();

        $overallTotalStudents = 0;
        $overallStudentsWithMoreThan6Credits = 0;
        $overallMaleWithMoreThan6Credits = 0;
        $overallFemaleWithMoreThan6Credits = 0;
        $overallStudentsWithMoreThan6JceCredits = 0;

        $groupedData = $houses->map(function ($house) use ($jceMappings, $gradeNames) {
            $students = $house->students;
            $totalStudents = $students->count();

            $houseStudentsWithMoreThan6Credits = 0;
            $houseMaleWithMoreThan6Credits = 0;
            $houseFemaleWithMoreThan6Credits = 0;
            $houseStudentsWithMoreThan6JceCredits = 0;

            $classGroups = $students->groupBy(function ($student) {
                    $currentClass = $student->currentClassRelation->first();
                    return $currentClass ? $currentClass->id : 'unassigned';
                })->map(function ($students, $classId) use (
                    $jceMappings,
                    &$houseStudentsWithMoreThan6Credits,
                    &$houseMaleWithMoreThan6Credits,
                    &$houseFemaleWithMoreThan6Credits,
                    &$houseStudentsWithMoreThan6JceCredits,
                    $gradeNames
                ) {
                    $firstStudent = $students->first();
                    $currentClass = $firstStudent?->currentClassRelation?->first();
                    $className = $currentClass?->name ?? 'Unassigned';
                    $classType = $currentClass?->type ?? 'N/A';
                    $classGradeId = $currentClass->grade_id ?? null;

                    $totalStudentsInClass = $students->count();

                    $classStudentsWithMoreThan6Credits = 0;
                    $classMaleWithMoreThan6Credits = 0;
                    $classFemaleWithMoreThan6Credits = 0;
                    $classStudentsWithMoreThan6JceCredits = 0;

                    foreach ($students as $student) {
                        $studentGradeId = $student->studentTerms->first()->grade_id ?? null;
                        $relevantTests = $student->tests->filter(fn($t) => $t->grade_id == $studentGradeId);

                        $creditsCount = 0;
                        foreach ($relevantTests as $relevantTest) {
                            $grade = $relevantTest->pivot->grade;
                            $isDouble = (bool) ($relevantTest->subject->subject->is_double ?? false);
                            if ($isDouble && is_string($grade) && strlen($grade) === 2) {
                                $creditsCount += in_array($grade[0], ['A','B','C'], true) ? 1 : 0;
                                $creditsCount += in_array($grade[1], ['A','B','C'], true) ? 1 : 0;
                            } elseif (in_array($grade, ['A*', 'A', 'B', 'C'], true)) {
                                $creditsCount++;
                            }
                        }

                        if ($creditsCount >= 6) {
                            $classStudentsWithMoreThan6Credits++;
                            if ($student->gender === 'M') $classMaleWithMoreThan6Credits++;
                            if ($student->gender === 'F') $classFemaleWithMoreThan6Credits++;
                        }

                        if ($student->jce) {
                            $jceCreditsCount = 0;
                            $seenSubjects = [];
                            foreach ($relevantTests as $t) {
                                $subjectId = $t->subject->subject_id ?? null;
                                if (!$subjectId || in_array($subjectId, $seenSubjects)) continue;
                                $seenSubjects[] = $subjectId;

                                $sourceKey = $jceMappings[$subjectId] ?? null;
                                $column = $sourceKey ?? 'overall';
                                $jceGrade = $student->jce->{$column} ?? null;
                                if ($jceGrade === 'Merit') $jceGrade = 'A';
                                if (in_array($jceGrade, ['A', 'B', 'C'], true)) {
                                    $jceCreditsCount++;
                                }
                            }
                            if ($jceCreditsCount >= 6) {
                                $classStudentsWithMoreThan6JceCredits++;
                            }
                        }
                    }

                    $houseStudentsWithMoreThan6Credits += $classStudentsWithMoreThan6Credits;
                    $houseMaleWithMoreThan6Credits += $classMaleWithMoreThan6Credits;
                    $houseFemaleWithMoreThan6Credits += $classFemaleWithMoreThan6Credits;
                    $houseStudentsWithMoreThan6JceCredits += $classStudentsWithMoreThan6JceCredits;

                    $pctInternal = $totalStudentsInClass > 0
                        ? ($classStudentsWithMoreThan6Credits / $totalStudentsInClass) * 100 : 0;
                    $pctJce = $totalStudentsInClass > 0
                        ? ($classStudentsWithMoreThan6JceCredits / $totalStudentsInClass) * 100 : 0;
                    $valueAdd = $pctInternal - $pctJce;

                    return [
                        'id' => $classId,
                        'name' => $className,
                        'type' => $classType,
                        'grade_id' => $classGradeId,
                        'grade_name' => $classGradeId ? ($gradeNames[$classGradeId] ?? null) : null,
                        'count' => $totalStudentsInClass,
                        'male_with_more_than_6_credits' => $classMaleWithMoreThan6Credits,
                        'female_with_more_than_6_credits' => $classFemaleWithMoreThan6Credits,
                        'students_with_more_than_6_credits' => $classStudentsWithMoreThan6Credits,
                        'percentage_with_more_than_6_credits' => round($pctInternal, 2),
                        'students_with_more_than_6_jce_credits' => $classStudentsWithMoreThan6JceCredits,
                        'percentage_of_jce_grades' => round($pctJce, 2),
                        'value_addition' => round($valueAdd, 2),
                    ];
                })->sortBy('name');

            $pctInternalHouse = $totalStudents > 0
                ? ($houseStudentsWithMoreThan6Credits / $totalStudents) * 100 : 0;
            $pctJceHouse = $totalStudents > 0
                ? ($houseStudentsWithMoreThan6JceCredits / $totalStudents) * 100 : 0;
            $valueAddHouse = $pctInternalHouse - $pctJceHouse;

            return [
                'house' => $house,
                'total_students' => $totalStudents,
                'classes' => $classGroups,
                'students_with_more_than_6_credits' => $houseStudentsWithMoreThan6Credits,
                'male_with_more_than_6_credits' => $houseMaleWithMoreThan6Credits,
                'female_with_more_than_6_credits' => $houseFemaleWithMoreThan6Credits,
                'percentage_with_more_than_6_credits' => round($pctInternalHouse, 2),
                'students_with_more_than_6_jce_credits' => $houseStudentsWithMoreThan6JceCredits,
                'percentage_of_jce_grades' => round($pctJceHouse, 2),
                'value_addition' => round($valueAddHouse, 2),
            ];
        });

        foreach ($groupedData as $data) {
            $overallTotalStudents += $data['total_students'];
            $overallStudentsWithMoreThan6Credits += $data['students_with_more_than_6_credits'];
            $overallMaleWithMoreThan6Credits += $data['male_with_more_than_6_credits'];
            $overallFemaleWithMoreThan6Credits += $data['female_with_more_than_6_credits'];
            $overallStudentsWithMoreThan6JceCredits += $data['students_with_more_than_6_jce_credits'];
        }

        $overallPercentageWithMoreThan6Credits = $overallTotalStudents > 0
            ? ($overallStudentsWithMoreThan6Credits / $overallTotalStudents) * 100 : 0;
        $overallPercentageOfJceGrades = $overallTotalStudents > 0
            ? ($overallStudentsWithMoreThan6JceCredits / $overallTotalStudents) * 100 : 0;
        $overallValueAddition = $overallPercentageWithMoreThan6Credits - $overallPercentageOfJceGrades;

        $overallTotals = [
            'overallTotalStudents' => $overallTotalStudents,
            'overallStudentsWithMoreThan6Credits' => $overallStudentsWithMoreThan6Credits,
            'overallMaleWithMoreThan6Credits' => $overallMaleWithMoreThan6Credits,
            'overallFemaleWithMoreThan6Credits' => $overallFemaleWithMoreThan6Credits,
            'overallStudentsWithMoreThan6JceCredits' => $overallStudentsWithMoreThan6JceCredits,
            'overallPercentageWithMoreThan6Credits' => round($overallPercentageWithMoreThan6Credits, 2),
            'overallPercentageOfJceGrades' => round($overallPercentageOfJceGrades, 2),
            'overallValueAddition' => round($overallValueAddition, 2),
        ];

        if ($request->query('export') === 'excel') {
            return Excel::download(new ClassGroupingExport($groupedData, $overallTotals, $test), 'houses_grade_analysis_report.xlsx');
        }

        return view('houses.house-grade-analysis', [
            'groupedData' => $groupedData,
            'test' => $test,
            'school_data' => SchoolSetup::first(),
            'overallTotalStudents' => $overallTotalStudents,
            'overallStudentsWithMoreThan6Credits' => $overallStudentsWithMoreThan6Credits,
            'overallMaleWithMoreThan6Credits' => $overallMaleWithMoreThan6Credits,
            'overallFemaleWithMoreThan6Credits' => $overallFemaleWithMoreThan6Credits,
            'overallStudentsWithMoreThan6JceCredits' => $overallStudentsWithMoreThan6JceCredits,
            'overallPercentageWithMoreThan6Credits' => round($overallPercentageWithMoreThan6Credits, 2),
            'overallPercentageOfJceGrades' => round($overallPercentageOfJceGrades, 2),
            'overallValueAddition' => round($overallValueAddition, 2),
        ]);
    }


    public function showClassGroupingBest5(Request $request, $classId, $sequenceId, $type){
        $klass = Klass::findOrFail($classId);
        $currentTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        $test = Test::where('term_id', $currentTermId)
            ->where('grade_id', $klass->grade_id)
            ->where('sequence', $sequenceId)
            ->where('type', $type)
            ->first();

        $jceMappings = ValueAdditionSubjectMapping::where('school_type', 'Senior')
            ->where('exam_type', 'JCE')
            ->where('is_active', true)
            ->get()
            ->keyBy('subject_id')
            ->map(fn($m) => $m->source_key)
            ->toArray();
        $gradeNames = Grade::pluck('name', 'id');

        $houses = House::with([
            'houseHead',
            'houseAssistant',
            'students' => function ($query) use ($currentTermId, $type, $sequenceId, $klass) {
                $query->whereHas('studentTerms', function ($q) use ($currentTermId) {
                        $q->where('term_id', $currentTermId)->where('status', 'Current');
                    })
                    ->whereHas('currentClassRelation', function ($q) use ($currentTermId, $klass) {
                        $q->where('klasses.term_id', $currentTermId)
                        ->where('klasses.grade_id', $klass->grade_id);
                    })
                    ->with([
                        'currentClassRelation' => function ($q) use ($currentTermId, $klass) {
                            $q->select('klasses.id', 'klasses.name', 'klasses.type', 'klasses.grade_id')
                            ->where('klasses.term_id', $currentTermId)
                            ->where('klasses.grade_id', $klass->grade_id);
                        },
                        'tests' => function ($q) use ($currentTermId, $type, $sequenceId) {
                            $q->where('tests.term_id', $currentTermId)
                            ->where('tests.type', $type)
                            ->where('tests.sequence', '<=', $sequenceId)
                            ->withPivot('score', 'grade', 'points')
                            ->with('subject.subject');
                        },
                        'studentTerms' => function ($q) use ($currentTermId) {
                            $q->where('term_id', $currentTermId);
                        },
                        'jce',
                    ]);
            },
        ])->where('term_id', $currentTermId)->get();

        $overallTotalStudents = 0;
        $overallStudentsWithMoreThan5Credits = 0;
        $overallMaleWithMoreThan5Credits = 0;
        $overallFemaleWithMoreThan5Credits = 0;
        $overallStudentsWithMoreThan5JceCredits = 0;

        $groupedData = $houses->map(function ($house) use ($jceMappings, $gradeNames) {
            $students = $house->students;
            $totalStudents = $students->count();

            $houseStudentsWithMoreThan5Credits = 0;
            $houseMaleWithMoreThan5Credits = 0;
            $houseFemaleWithMoreThan5Credits = 0;
            $houseStudentsWithMoreThan5JceCredits = 0;

            $classGroups = $students
                ->groupBy(function ($student) {
                    $currentClass = $student->currentClassRelation->first();
                    return $currentClass ? $currentClass->id : 'unassigned';
                })
                ->map(function ($students, $classId) use (
                    $jceMappings,
                    &$houseStudentsWithMoreThan5Credits,
                    &$houseMaleWithMoreThan5Credits,
                    &$houseFemaleWithMoreThan5Credits,
                    &$houseStudentsWithMoreThan5JceCredits,
                    $gradeNames
                ) {
                    $firstStudent = $students->first();
                    $currentClass = $firstStudent?->currentClassRelation?->first();
                    $className = $currentClass?->name ?? 'Unassigned';
                    $classType = $currentClass?->type ?? 'N/A';
                    $classGradeId = $currentClass->grade_id ?? null;

                    $totalStudentsInClass = $students->count();

                    $classStudentsWithMoreThan5Credits = 0;
                    $classMaleWithMoreThan5Credits = 0;
                    $classFemaleWithMoreThan5Credits = 0;
                    $classStudentsWithMoreThan5JceCredits = 0;

                    foreach ($students as $student) {
                        $studentGradeId = $student->studentTerms->first()->grade_id ?? null;
                        $relevantTests = $student->tests->filter(fn($t) => $t->grade_id == $studentGradeId);
                        $creditsCount = 0;
                        foreach ($relevantTests as $relevantTest) {
                            $grade = $relevantTest->pivot->grade;
                            $isDouble = (bool) ($relevantTest->subject->subject->is_double ?? false);
                            if ($isDouble && is_string($grade) && strlen($grade) === 2) {
                                $creditsCount += in_array($grade[0], ['A','B','C'], true) ? 1 : 0;
                                $creditsCount += in_array($grade[1], ['A','B','C'], true) ? 1 : 0;
                            } elseif (in_array($grade, ['A*', 'A', 'B', 'C'], true)) {
                                $creditsCount++;
                            }
                        }

                        if ($creditsCount >= 5) {
                            $classStudentsWithMoreThan5Credits++;
                            if ($student->gender === 'M') $classMaleWithMoreThan5Credits++;
                            if ($student->gender === 'F') $classFemaleWithMoreThan5Credits++;
                        }

                        if ($student->jce) {
                            $jceCreditsCount = 0;
                            $seenSubjects = [];
                            foreach ($relevantTests as $t) {
                                $subjectId = $t->subject->subject_id ?? null;
                                if (!$subjectId || in_array($subjectId, $seenSubjects)) continue;
                                $seenSubjects[] = $subjectId;

                                $sourceKey = $jceMappings[$subjectId] ?? null;
                                $column = $sourceKey ?? 'overall';
                                $jceGrade = $student->jce->{$column} ?? null;
                                if ($jceGrade === 'Merit') $jceGrade = 'A';
                                if (in_array($jceGrade, ['A', 'B', 'C'], true)) {
                                    $jceCreditsCount++;
                                }
                            }
                            if ($jceCreditsCount >= 5) {
                                $classStudentsWithMoreThan5JceCredits++;
                            }
                        }
                    }

                    $houseStudentsWithMoreThan5Credits += $classStudentsWithMoreThan5Credits;
                    $houseMaleWithMoreThan5Credits += $classMaleWithMoreThan5Credits;
                    $houseFemaleWithMoreThan5Credits += $classFemaleWithMoreThan5Credits;
                    $houseStudentsWithMoreThan5JceCredits += $classStudentsWithMoreThan5JceCredits;

                    $pctInternal = $totalStudentsInClass > 0
                        ? ($classStudentsWithMoreThan5Credits / $totalStudentsInClass) * 100 : 0;
                    $pctJce = $totalStudentsInClass > 0
                        ? ($classStudentsWithMoreThan5JceCredits / $totalStudentsInClass) * 100 : 0;
                    $valueAdd = $pctInternal - $pctJce;

                    return [
                        'id' => $classId,
                        'name' => $className,
                        'type' => $classType,
                        'grade_id' => $classGradeId,
                        'grade_name' => $classGradeId ? ($gradeNames[$classGradeId] ?? null) : null,
                        'count' => $totalStudentsInClass,
                        'male_with_more_than_5_credits' => $classMaleWithMoreThan5Credits,
                        'female_with_more_than_5_credits' => $classFemaleWithMoreThan5Credits,
                        'students_with_more_than_5_credits' => $classStudentsWithMoreThan5Credits,
                        'percentage_with_more_than_5_credits' => round($pctInternal, 2),
                        'students_with_more_than_5_jce_credits' => $classStudentsWithMoreThan5JceCredits,
                        'percentage_of_jce_grades' => round($pctJce, 2),
                        'value_addition' => round($valueAdd, 2),
                    ];
                })->sortBy('name');

            $pctInternalHouse = $totalStudents > 0
                ? ($houseStudentsWithMoreThan5Credits / $totalStudents) * 100 : 0;
            $pctJceHouse = $totalStudents > 0
                ? ($houseStudentsWithMoreThan5JceCredits / $totalStudents) * 100 : 0;
            $valueAddHouse = $pctInternalHouse - $pctJceHouse;

            return [
                'house' => $house,
                'total_students' => $totalStudents,
                'classes' => $classGroups,
                'students_with_more_than_5_credits' => $houseStudentsWithMoreThan5Credits,
                'male_with_more_than_5_credits' => $houseMaleWithMoreThan5Credits,
                'female_with_more_than_5_credits' => $houseFemaleWithMoreThan5Credits,
                'percentage_with_more_than_5_credits' => round($pctInternalHouse, 2),
                'students_with_more_than_5_jce_credits' => $houseStudentsWithMoreThan5JceCredits,
                'percentage_of_jce_grades' => round($pctJceHouse, 2),
                'value_addition' => round($valueAddHouse, 2),
            ];
        });

        foreach ($groupedData as $data) {
            $overallTotalStudents += $data['total_students'];
            $overallStudentsWithMoreThan5Credits += $data['students_with_more_than_5_credits'];
            $overallMaleWithMoreThan5Credits += $data['male_with_more_than_5_credits'];
            $overallFemaleWithMoreThan5Credits += $data['female_with_more_than_5_credits'];
            $overallStudentsWithMoreThan5JceCredits += $data['students_with_more_than_5_jce_credits'];
        }

        $overallPercentageWithMoreThan5Credits = $overallTotalStudents > 0
            ? ($overallStudentsWithMoreThan5Credits / $overallTotalStudents) * 100 : 0;
        $overallPercentageOfJceGrades = $overallTotalStudents > 0
            ? ($overallStudentsWithMoreThan5JceCredits / $overallTotalStudents) * 100 : 0;
        $overallValueAddition = $overallPercentageWithMoreThan5Credits - $overallPercentageOfJceGrades;

        $overallTotals = [
            'overallTotalStudents' => $overallTotalStudents,
            'overallStudentsWithMoreThan5Credits' => $overallStudentsWithMoreThan5Credits,
            'overallMaleWithMoreThan5Credits' => $overallMaleWithMoreThan5Credits,
            'overallFemaleWithMoreThan5Credits' => $overallFemaleWithMoreThan5Credits,
            'overallStudentsWithMoreThan5JceCredits' => $overallStudentsWithMoreThan5JceCredits,
            'overallPercentageWithMoreThan5Credits' => round($overallPercentageWithMoreThan5Credits, 2),
            'overallPercentageOfJceGrades' => round($overallPercentageOfJceGrades, 2),
            'overallValueAddition' => round($overallValueAddition, 2),
        ];

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new \App\Exports\ClassGroupingBest5Export($groupedData, $overallTotals, $test),
                'houses_grade_analysis_best5.xlsx'
            );
            
        }

        return view('houses.house-grade-analysis-best5', [
            'groupedData' => $groupedData,
            'test' => $test,
            'school_data' => SchoolSetup::first(),
            'overallTotalStudents' => $overallTotalStudents,
            'overallStudentsWithMoreThan5Credits' => $overallStudentsWithMoreThan5Credits,
            'overallMaleWithMoreThan5Credits' => $overallMaleWithMoreThan5Credits,
            'overallFemaleWithMoreThan5Credits' => $overallFemaleWithMoreThan5Credits,
            'overallStudentsWithMoreThan5JceCredits' => $overallStudentsWithMoreThan5JceCredits,
            'overallPercentageWithMoreThan5Credits' => round($overallPercentageWithMoreThan5Credits, 2),
            'overallPercentageOfJceGrades' => round($overallPercentageOfJceGrades, 2),
            'overallValueAddition' => round($overallValueAddition, 2),
        ]);
    }

}
