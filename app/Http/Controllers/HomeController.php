<?php

namespace App\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Helpers\TermHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Grade;
use App\Models\SchoolSetup;
use Illuminate\Support\Collection;
use Log;

class HomeController extends Controller{
    
    public function __construct(){
        $this->middleware(function ($request, $next) {
            if (!auth()->user()) {
                return redirect()->route('login');
            }
            return $next($request);
        });
    }

    public function index(Request $request){
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    public function root(){
        $terms = StudentController::terms();
        $currentTerm = TermHelper::getCurrentTerm();
        return view('dashboard.index', ['terms' => $terms,'currentTerm' => $currentTerm]);
    }

    public function getDashboardTermData() {
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $school_data = SchoolSetup::first();

        $grades = CacheHelper::getDashboardGrades($school_data->type, $selectedTermId);
        $users = CacheHelper::getDashboardUsers();
        $notifications = CacheHelper::getDashboardNotifications(auth()->user());
        $students = CacheHelper::getStudentData($selectedTermId);

        $grades = $this->resolveDashboardGrades($grades, $students);
        $analysis = $this->analyzeStudentData($grades, $students);

        if ($students->isNotEmpty()) {
            $students_count = $students->sum('student_count');
            return view('dashboard.dashboard-term', [
                'grades' => $grades,
                'analysis' => $analysis,
                'notifications' => $notifications,
                'students_count' => $students_count,
                'users' => $users,
                'students' => $students,
            ]);
        }

        return view('dashboard.dashboard-welcome', ['users' => $users]);
    }

    private function analyzeStudentData(Collection $grades, $students): array
    {
        $analysis = $grades->mapWithKeys(function ($grade) {
            return [$grade->name => ['M' => 0, 'F' => 0]];
        })->toArray();

        if ($students->isEmpty()) {
            return $analysis;
        }

        $gradesById = $grades->keyBy('id');

        foreach ($students as $student) {
            $grade = $gradesById->get($student->grade_id) ?? Grade::find($student->grade_id);

            if (!$grade) {
                continue;
            }

            $gradeName = $grade->name;
            $gender = strtoupper(trim($student->gender));
            if ($gender !== 'M' && $gender !== 'F') {
                $gender = 'F';
            }

            if (!isset($analysis[$gradeName])) {
                $analysis[$gradeName] = ['M' => 0, 'F' => 0];
            }

            $analysis[$gradeName][$gender] += $student->student_count;
        }

        return $analysis;
    }

    private function resolveDashboardGrades(Collection $grades, $students): Collection
    {
        if ($students->isEmpty()) {
            return $grades;
        }

        $existingGradeIds = $grades->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        $missingGradeIds = collect($students)
            ->pluck('grade_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->diff($existingGradeIds);

        if ($missingGradeIds->isEmpty()) {
            return $grades;
        }

        $missingGrades = Grade::query()
            ->whereIn('id', $missingGradeIds->all())
            ->orderBy('sequence')
            ->get();

        return $grades
            ->concat($missingGrades)
            ->unique('name')
            ->sortBy(fn ($grade) => sprintf('%05d-%s', (int) ($grade->sequence ?? 0), (string) $grade->name))
            ->values();
    }
    
    public function lang($locale){
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }
}
