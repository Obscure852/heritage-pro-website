<?php

namespace App\Http\Controllers\Schemes;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Services\Schemes\CoverageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller {
    /**
     * Display the admin-only scheme overview: school-wide completion counts
     * and a list of teachers who have class assignments but no scheme.
     */
    public function index(Request $request): View {
        $this->authorize('admin-schemes');

        $defaultTerm = Term::currentOrLastActiveTerm();
        $term = $request->filled('term_id')
            ? (Term::find($request->query('term_id')) ?? $defaultTerm)
            : $defaultTerm;

        $terms = TermHelper::getSelectableTerms($defaultTerm);

        // Grade filter (sticky via session)
        $grades = \App\Models\Grade::where('active', true)->orderBy('name')->get();
        $selectedGradeId = $request->input('grade_id');
        if ($selectedGradeId) {
            session(['admin_dashboard_grade_id' => $selectedGradeId]);
        } else {
            $selectedGradeId = session('admin_dashboard_grade_id');
        }
        if (!$selectedGradeId || !$grades->contains('id', (int) $selectedGradeId)) {
            $selectedGradeId = $grades->first()?->id;
            session(['admin_dashboard_grade_id' => $selectedGradeId]);
        }

        $coverageService = new CoverageService();

        $completion     = $coverageService->schoolCompletion($term);
        $missingSchemes = $coverageService->missingSchemes($term, $selectedGradeId ? (int) $selectedGradeId : null);

        return view('schemes.admin.dashboard', compact('completion', 'missingSchemes', 'term', 'terms', 'grades', 'selectedGradeId'));
    }
}
