<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetup;
use App\Services\Leave\LeaveBalanceService;
use App\Services\Leave\LeaveStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller for leave statement generation and download.
 *
 * Provides functionality for staff to view and download
 * their leave statements as PDF documents.
 */
class LeaveStatementController extends Controller {
    /**
     * @var LeaveStatementService
     */
    protected LeaveStatementService $statementService;

    /**
     * @var LeaveBalanceService
     */
    protected LeaveBalanceService $balanceService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveStatementService $statementService
     * @param LeaveBalanceService $balanceService
     */
    public function __construct(
        LeaveStatementService $statementService,
        LeaveBalanceService $balanceService
    ) {
        $this->middleware('auth');
        $this->statementService = $statementService;
        $this->balanceService = $balanceService;
    }

    /**
     * Show statement preview/selection page.
     *
     * Displays a page where staff can select the year
     * for which they want to generate a leave statement.
     *
     * @return View
     */
    public function index(): View {
        $user = auth()->user();
        $currentYear = $this->balanceService->getCurrentLeaveYear();
        $availableYears = $this->statementService->getAvailableYears($user);

        // Add current year if not in list
        if (!$availableYears->contains($currentYear)) {
            $availableYears->prepend($currentYear);
        }

        return view('leave.statements.index', [
            'availableYears' => $availableYears,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Download leave statement as PDF.
     *
     * Generates a PDF statement for the authenticated user
     * for the specified year and triggers a download.
     *
     * @param Request $request
     * @return Response
     */
    public function download(Request $request): Response {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $user = auth()->user();
        $year = (int) $request->year;

        // Generate statement data
        $statement = $this->statementService->generateStatement($user, $year);

        // Get school info for branding
        $school = SchoolSetup::first();

        // Generate PDF
        $pdf = Pdf::loadView('leave.statements.statement-pdf', [
            'statement' => $statement,
            'school' => $school,
        ]);

        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = sprintf(
            'Leave_Statement_%s_%d.pdf',
            str_replace(' ', '_', $user->name),
            $year
        );

        return $pdf->download($filename);
    }
}
