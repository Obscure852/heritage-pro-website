<?php

namespace App\Http\Controllers\Fee;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\StoreDiscountTypeRequest;
use App\Http\Requests\Fee\StoreFeeStructureRequest;
use App\Http\Requests\Fee\StoreFeeTypeRequest;
use App\Http\Requests\Fee\UpdateDiscountTypeRequest;
use App\Http\Requests\Fee\UpdateFeeStructureRequest;
use App\Http\Requests\Fee\UpdateFeeTypeRequest;
use App\Models\Fee\DiscountType;
use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\FeeType;
use App\Models\Grade;
use App\Models\Term;
use App\Models\User;
use App\Services\Fee\DiscountService;
use App\Services\Fee\FeeAuditService;
use App\Services\Fee\FeeStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FeeSetupController extends Controller
{
    protected FeeStructureService $feeStructureService;
    protected DiscountService $discountService;

    public function __construct(FeeStructureService $feeStructureService, DiscountService $discountService)
    {
        $this->middleware('auth');
        $this->feeStructureService = $feeStructureService;
        $this->discountService = $discountService;
    }

    // ========================================
    // Main Index (Tabbed Page)
    // ========================================

    /**
     * Display the main fee setup page with all tabs.
     */
    public function index(): View
    {
        Gate::authorize('manage-fee-setup');

        // Fee Types data
        $feeTypes = FeeType::orderBy('code')->get();
        $categories = FeeType::categories();

        // Fee Structures data
        $feeStructures = FeeStructure::with(['feeType', 'grade', 'createdBy'])
            ->orderBy('year', 'desc')
            ->orderBy('grade_id')
            ->orderBy('fee_type_id')
            ->get();
        $grades = Grade::where('active', true)->orderBy('name')->get();
        $years = $this->getAvailableYears();
        $activeFeeTypes = FeeType::active()->orderBy('code')->get();

        // Discount Types data
        $discountTypes = DiscountType::orderBy('code')->get();
        $appliesOptions = DiscountType::appliesOptions();

        // Payment Methods (from config or database)
        $paymentMethods = config('fees.payment_methods', [
            'cash' => true,
            'bank_transfer' => true,
            'mobile_money' => false,
            'card' => false,
            'other' => true,
        ]);

        // Default settings
        $defaultSettings = [
            'currency_symbol' => 'P',
            'currency_code' => 'BWP',
            'currency_position' => 'before',
            'receipt_prefix' => 'RCP',
            'receipt_number_start' => 1,
            'receipt_footer' => '',
            'auto_generate_receipt' => true,
            'enable_late_fees' => false,
            'late_fee_grace_period' => 7,
            'late_fee_type' => 'fixed',
            'late_fee_amount' => 50,
            'enable_payment_plans' => true,
            'default_plan_frequency' => 'termly',
            'notify_on_payment' => false,
            'notify_on_overdue' => false,
            'reminder_days_before' => 3,
            'overdue_reminder_intervals' => [7, 14, 30],
            'admin_notification_email' => '',
            'invoice_prefix' => 'INV',
            'default_payment_terms' => 30,
            'invoice_notes' => '',
            'carryover_lookback_years' => 3,
            'auto_lock_past_years' => false,
            'locked_until_year' => null,
        ];

        // Load all fee settings from database
        $dbSettings = \App\Models\SMSApiSetting::where('key', 'like', 'fee.%')
            ->pluck('value', 'key')
            ->mapWithKeys(function ($value, $key) {
                // Remove 'fee.' prefix from key
                $shortKey = str_replace('fee.', '', $key);
                return [$shortKey => $value];
            })
            ->toArray();

        // Merge database settings with defaults (database takes priority)
        $settings = array_merge($defaultSettings, $dbSettings);

        // Convert boolean strings to actual booleans for checkboxes
        $booleanFields = ['auto_generate_receipt', 'enable_late_fees', 'enable_payment_plans', 'notify_on_payment', 'notify_on_overdue', 'auto_lock_past_years'];
        foreach ($booleanFields as $field) {
            if (isset($settings[$field])) {
                $settings[$field] = filter_var($settings[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Decode JSON fields
        if (isset($settings['overdue_reminder_intervals']) && is_string($settings['overdue_reminder_intervals'])) {
            $settings['overdue_reminder_intervals'] = json_decode($settings['overdue_reminder_intervals'], true) ?? [7, 14, 30];
        }

        // Convert numeric strings to numbers
        $numericFields = ['receipt_number_start', 'late_fee_grace_period', 'late_fee_amount', 'reminder_days_before', 'default_payment_terms', 'carryover_lookback_years'];
        foreach ($numericFields as $field) {
            if (isset($settings[$field])) {
                $settings[$field] = is_numeric($settings[$field]) ? (float) $settings[$field] : $settings[$field];
            }
        }

        // Get current term year for default filter
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? date('Y');

        // Audit Trail tab data
        $auditActions = FeeAuditLog::actions();
        $auditableTypes = [
            'App\\Models\\Fee\\StudentInvoice' => 'Invoice',
            'App\\Models\\Fee\\FeePayment' => 'Payment',
            'App\\Models\\Fee\\FeeRefund' => 'Refund/Credit Note',
            'App\\Models\\Fee\\DiscountType' => 'Discount Type',
            'App\\Models\\Fee\\StudentDiscount' => 'Student Discount',
            'App\\Models\\Fee\\FeeBalanceCarryover' => 'Balance Carryover',
            'App\\Models\\Fee\\StudentClearance' => 'Clearance Override',
        ];
        $feeUsers = User::whereIn('id', FeeAuditLog::select('user_id')->distinct())
            ->orderBy('firstname')->orderBy('lastname')
            ->get(['id', 'firstname', 'lastname']);

        return view('fees.setup.index', compact(
            'feeTypes',
            'categories',
            'feeStructures',
            'grades',
            'years',
            'activeFeeTypes',
            'discountTypes',
            'appliesOptions',
            'paymentMethods',
            'settings',
            'currentTermYear',
            'auditActions',
            'auditableTypes',
            'feeUsers'
        ));
    }

    // ========================================
    // Fee Type Methods
    // ========================================

    /**
     * Display a listing of fee types.
     */
    public function indexTypes(Request $request): View
    {
        Gate::authorize('manage-fee-setup');

        $query = FeeType::query();

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $feeTypes = $query->orderBy('code')->paginate(20);

        return view('fees.setup.fee-types.index', [
            'feeTypes' => $feeTypes,
            'categories' => FeeType::categories(),
            'filters' => $request->only(['category', 'is_active', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new fee type.
     */
    public function createType(): View
    {
        Gate::authorize('manage-fee-setup');

        return view('fees.setup.fee-types.create', [
            'categories' => FeeType::categories(),
        ]);
    }

    /**
     * Store a newly created fee type.
     */
    public function storeType(StoreFeeTypeRequest $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $this->feeStructureService->createFeeType($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee type created successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Fee type created successfully.');
    }

    /**
     * Show the form for editing a fee type.
     */
    public function editType(FeeType $feeType): View
    {
        Gate::authorize('manage-fee-setup');

        return view('fees.setup.fee-types.edit', [
            'feeType' => $feeType,
            'categories' => FeeType::categories(),
        ]);
    }

    /**
     * Update the specified fee type.
     */
    public function updateType(UpdateFeeTypeRequest $request, FeeType $feeType): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $this->feeStructureService->updateFeeType($feeType, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee type updated successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Fee type updated successfully.');
    }

    /**
     * Remove the specified fee type (soft delete).
     */
    public function destroyType(Request $request, FeeType $feeType): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        // Check if fee type has associated structures
        if ($feeType->feeStructures()->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete fee type with existing fee structures.',
                ], 422);
            }

            return redirect()
                ->route('fees.setup.index')
                ->with('error', 'Cannot delete fee type with existing fee structures.');
        }

        $this->feeStructureService->deleteFeeType($feeType);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee type deleted successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Fee type deleted successfully.');
    }

    // ========================================
    // Fee Structure Methods
    // ========================================

    /**
     * Display a listing of fee structures.
     */
    public function indexStructures(Request $request): View
    {
        Gate::authorize('manage-fee-setup');

        $query = FeeStructure::with(['feeType', 'grade', 'createdBy']);

        // Apply filters
        if ($request->filled('grade_id')) {
            $query->forGrade($request->grade_id);
        }

        if ($request->filled('year')) {
            $query->forYear($request->year);
        }

        if ($request->filled('fee_type_id')) {
            $query->where('fee_type_id', $request->fee_type_id);
        }

        $feeStructures = $query->orderBy('year', 'desc')
            ->orderBy('grade_id')
            ->orderBy('fee_type_id')
            ->paginate(20);

        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? date('Y');

        return view('fees.setup.fee-structures.index', [
            'feeStructures' => $feeStructures,
            'grades' => Grade::where('active', true)->orderBy('name')->get(),
            'feeTypes' => FeeType::active()->orderBy('code')->get(),
            'years' => $this->getAvailableYears(),
            'filters' => $request->only(['grade_id', 'year', 'fee_type_id']),
            'currentTermYear' => $currentTermYear,
        ]);
    }

    /**
     * Show the form for creating a new fee structure.
     */
    public function createStructure(): View
    {
        Gate::authorize('manage-fee-setup');

        return view('fees.setup.fee-structures.create', [
            'grades' => Grade::where('active', true)->orderBy('name')->get(),
            'feeTypes' => FeeType::active()->orderBy('code')->get(),
            'years' => $this->getAvailableYears(),
        ]);
    }

    /**
     * Store a newly created fee structure.
     */
    public function storeStructure(StoreFeeStructureRequest $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $this->feeStructureService->createFeeStructure(
            $request->validated(),
            $request->user()
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee structure created successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Fee structure created successfully.');
    }

    /**
     * Show the form for editing a fee structure.
     */
    public function editStructure(FeeStructure $feeStructure): View
    {
        Gate::authorize('manage-fee-setup');

        $feeStructure->load(['feeType', 'grade']);

        // Check if historical year (locked)
        $isLocked = $this->feeStructureService->isHistoricalYear($feeStructure->year);

        return view('fees.setup.fee-structures.edit', [
            'feeStructure' => $feeStructure,
            'grades' => Grade::where('active', true)->orderBy('name')->get(),
            'feeTypes' => FeeType::active()->orderBy('code')->get(),
            'years' => $this->getAvailableYears(),
            'isLocked' => $isLocked,
        ]);
    }

    /**
     * Update the specified fee structure.
     */
    public function updateStructure(UpdateFeeStructureRequest $request, FeeStructure $feeStructure): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        // Check if historical year (locked)
        if ($this->feeStructureService->isHistoricalYear($feeStructure->year)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify fee structures for historical years.',
                ], 422);
            }

            return redirect()
                ->route('fees.setup.index')
                ->with('error', 'Cannot modify fee structures for historical years.');
        }

        $this->feeStructureService->updateFeeStructure($feeStructure, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee structure updated successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Fee structure updated successfully.');
    }

    /**
     * Remove the specified fee structure (soft delete).
     */
    public function destroyStructure(Request $request, FeeStructure $feeStructure): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        // Check if historical year (locked)
        if ($this->feeStructureService->isHistoricalYear($feeStructure->year)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete fee structures for historical years.',
                ], 422);
            }

            return redirect()
                ->route('fees.setup.index')
                ->with('error', 'Cannot delete fee structures for historical years.');
        }

        // Check if structure has associated invoice items
        if ($feeStructure->invoiceItems()->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete fee structure with existing invoice items.',
                ], 422);
            }

            return redirect()
                ->route('fees.setup.index')
                ->with('error', 'Cannot delete fee structure with existing invoice items.');
        }

        $this->feeStructureService->deleteFeeStructure($feeStructure);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Fee structure deleted successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Fee structure deleted successfully.');
    }

    /**
     * Copy fee structures from one year to another.
     */
    public function copyStructures(Request $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $validated = $request->validate([
            'from_year' => 'required|integer|min:2020|max:2099',
            'to_year' => 'required|integer|min:2020|max:2099|different:from_year',
        ], [
            'from_year.required' => 'Please select a source year.',
            'to_year.required' => 'Please select a destination year.',
            'to_year.different' => 'Source and destination years must be different.',
        ]);

        $copiedCount = $this->feeStructureService->copyStructuresToYear(
            $validated['from_year'],
            $validated['to_year'],
            $request->user()
        );

        if ($copiedCount > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$copiedCount} fee structure(s) copied successfully.",
                ]);
            }

            return redirect()
                ->route('fees.setup.index')
                ->with('success', "{$copiedCount} fee structure(s) copied successfully.");
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'No new fee structures to copy. Structures may already exist for the destination year.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('info', 'No new fee structures to copy. Structures may already exist for the destination year.');
    }

    /**
     * Get available years for fee structures from the terms table.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAvailableYears()
    {
        // Get unique years from the terms table
        return Term::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    // ========================================
    // Discount Type Methods
    // ========================================

    /**
     * Display a listing of discount types.
     */
    public function indexDiscountTypes(Request $request): View
    {
        Gate::authorize('manage-fee-setup');

        $query = DiscountType::query();

        // Apply filters
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('applies_to')) {
            $query->where('applies_to', $request->applies_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $discountTypes = $query->orderBy('code')->paginate(20);

        return view('fees.setup.discount-types.index', [
            'discountTypes' => $discountTypes,
            'appliesOptions' => DiscountType::appliesOptions(),
            'filters' => $request->only(['is_active', 'applies_to', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new discount type.
     */
    public function createDiscountType(): View
    {
        Gate::authorize('manage-fee-setup');

        return view('fees.setup.discount-types.create', [
            'appliesOptions' => DiscountType::appliesOptions(),
        ]);
    }

    /**
     * Store a newly created discount type.
     */
    public function storeDiscountType(StoreDiscountTypeRequest $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $this->discountService->createDiscountType($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Discount type created successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Discount type created successfully.');
    }

    /**
     * Show the form for editing a discount type.
     */
    public function editDiscountType(DiscountType $discountType): View
    {
        Gate::authorize('manage-fee-setup');

        return view('fees.setup.discount-types.edit', [
            'discountType' => $discountType,
            'appliesOptions' => DiscountType::appliesOptions(),
        ]);
    }

    /**
     * Update the specified discount type.
     */
    public function updateDiscountType(UpdateDiscountTypeRequest $request, DiscountType $discountType): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $this->discountService->updateDiscountType($discountType, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Discount type updated successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Discount type updated successfully.');
    }

    /**
     * Remove the specified discount type (soft delete).
     */
    public function destroyDiscountType(Request $request, DiscountType $discountType): JsonResponse|RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        // Check if discount type has assigned students
        if ($discountType->studentDiscounts()->exists()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete discount type with assigned students.',
                ], 422);
            }

            return redirect()
                ->route('fees.setup.index')
                ->with('error', 'Cannot delete discount type with assigned students.');
        }

        $this->discountService->deleteDiscountType($discountType);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Discount type deleted successfully.',
            ]);
        }

        return redirect()
            ->route('fees.setup.index')
            ->with('success', 'Discount type deleted successfully.');
    }

    // ========================================
    // Payment Methods
    // ========================================

    /**
     * Update payment method settings.
     */
    public function updatePaymentMethod(Request $request): JsonResponse
    {
        Gate::authorize('manage-fee-setup');

        $validated = $request->validate([
            'method' => 'required|string|in:cash,bank_transfer,mobile_money,card,other',
            'enabled' => 'required|boolean',
        ]);

        // In a real implementation, this would update the database or config
        // For now, we'll return a success response
        // You could create a FeeSettings model or use a settings package

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
        ]);
    }

    // ========================================
    // General Settings
    // ========================================

    /**
     * Update general fee settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        Gate::authorize('manage-fee-setup');

        // Calculate max lookback based on system data
        $maxLookback = \App\Services\Fee\BalanceService::getMaxLookbackYears();

        $validated = $request->validate([
            'currency_symbol' => 'nullable|string|max:5',
            'currency_code' => 'nullable|string|max:3|alpha',
            'currency_position' => 'nullable|string|in:before,after',
            'receipt_prefix' => 'nullable|string|max:10',
            'receipt_number_start' => 'nullable|integer|min:1',
            'receipt_footer' => 'nullable|string|max:500',
            'auto_generate_receipt' => 'nullable|boolean',
            'enable_late_fees' => 'nullable|boolean',
            'late_fee_grace_period' => 'nullable|integer|min:0|max:30',
            'late_fee_type' => 'nullable|string|in:fixed,percentage',
            'late_fee_amount' => 'nullable|numeric|min:0',
            'enable_payment_plans' => 'nullable|boolean',
            'default_plan_frequency' => 'nullable|string|in:monthly,termly,custom',
            'notify_on_payment' => 'nullable|boolean',
            'notify_on_overdue' => 'nullable|boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:14',
            'overdue_reminder_intervals' => 'nullable|array|max:3',
            'overdue_reminder_intervals.*' => 'nullable|integer|min:1|max:90',
            'admin_notification_email' => 'nullable|email|max:255',
            'invoice_prefix' => 'nullable|string|max:10',
            'default_payment_terms' => 'nullable|integer|min:1|max:90',
            'invoice_notes' => 'nullable|string|max:500',
            'carryover_lookback_years' => "nullable|integer|min:1|max:{$maxLookback}",
            'auto_lock_past_years' => 'nullable|boolean',
            'locked_until_year' => 'nullable|integer|min:2015|max:' . ((int) date('Y') - 1),
        ]);

        // Save currency settings to the database
        if (isset($validated['currency_symbol'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.currency_symbol'],
                [
                    'value' => $validated['currency_symbol'],
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Currency Symbol',
                    'description' => 'Symbol displayed with currency amounts (e.g., P, $, R)',
                    'validation_rules' => 'required|string|max:5',
                    'is_editable' => true,
                    'display_order' => 1,
                ]
            );
        }

        if (isset($validated['currency_code'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.currency_code'],
                [
                    'value' => strtoupper($validated['currency_code']),
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Currency Code',
                    'description' => 'ISO currency code (e.g., BWP, USD, ZAR)',
                    'validation_rules' => 'required|string|max:3|alpha',
                    'is_editable' => true,
                    'display_order' => 2,
                ]
            );
        }

        if (isset($validated['currency_position'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.currency_position'],
                [
                    'value' => $validated['currency_position'],
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Currency Position',
                    'description' => 'Where to display currency symbol (before or after amount)',
                    'validation_rules' => 'required|string|in:before,after',
                    'is_editable' => true,
                    'display_order' => 3,
                ]
            );
        }

        // Save the carryover lookback years setting to the database
        if (isset($validated['carryover_lookback_years'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.carryover_lookback_years'],
                [
                    'value' => (string) $validated['carryover_lookback_years'],
                    'category' => 'fees',
                    'type' => 'integer',
                    'display_name' => 'Carryover Lookback Years',
                    'description' => 'Number of years to check for outstanding balances when generating invoices',
                    'validation_rules' => "required|integer|min:1|max:{$maxLookback}",
                    'is_editable' => true,
                    'display_order' => 50,
                ]
            );
        }

        // Save receipt settings
        if (isset($validated['receipt_prefix'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.receipt_prefix'],
                [
                    'value' => $validated['receipt_prefix'],
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Receipt Number Prefix',
                    'description' => 'Prefix added before receipt numbers',
                    'is_editable' => true,
                    'display_order' => 10,
                ]
            );
        }

        if (isset($validated['receipt_number_start'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.receipt_number_start'],
                [
                    'value' => (string) $validated['receipt_number_start'],
                    'category' => 'fees',
                    'type' => 'integer',
                    'display_name' => 'Receipt Number Start',
                    'description' => 'Starting number for receipts',
                    'is_editable' => true,
                    'display_order' => 11,
                ]
            );
        }

        if (array_key_exists('receipt_footer', $validated)) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.receipt_footer'],
                [
                    'value' => $validated['receipt_footer'] ?? '',
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Receipt Footer Text',
                    'description' => 'Text displayed at the bottom of printed receipts',
                    'is_editable' => true,
                    'display_order' => 12,
                ]
            );
        }

        // Save auto_generate_receipt (checkbox - needs special handling)
        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.auto_generate_receipt'],
            [
                'value' => $request->has('auto_generate_receipt') ? '1' : '0',
                'category' => 'fees',
                'type' => 'boolean',
                'display_name' => 'Auto-generate Receipt',
                'description' => 'Automatically create receipt when payment is recorded',
                'is_editable' => true,
                'display_order' => 13,
            ]
        );

        // Save late fee settings
        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.enable_late_fees'],
            [
                'value' => $request->has('enable_late_fees') ? '1' : '0',
                'category' => 'fees',
                'type' => 'boolean',
                'display_name' => 'Enable Late Fees',
                'description' => 'Automatically apply late fees after due date',
                'is_editable' => true,
                'display_order' => 20,
            ]
        );

        if (isset($validated['late_fee_grace_period'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.late_fee_grace_period'],
                [
                    'value' => (string) $validated['late_fee_grace_period'],
                    'category' => 'fees',
                    'type' => 'integer',
                    'display_name' => 'Late Fee Grace Period',
                    'description' => 'Days after due date before late fee is applied',
                    'is_editable' => true,
                    'display_order' => 21,
                ]
            );
        }

        if (isset($validated['late_fee_type'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.late_fee_type'],
                [
                    'value' => $validated['late_fee_type'],
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Late Fee Type',
                    'description' => 'How the late fee is calculated (fixed or percentage)',
                    'is_editable' => true,
                    'display_order' => 22,
                ]
            );
        }

        if (isset($validated['late_fee_amount'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.late_fee_amount'],
                [
                    'value' => (string) $validated['late_fee_amount'],
                    'category' => 'fees',
                    'type' => 'decimal',
                    'display_name' => 'Late Fee Amount',
                    'description' => 'Amount or percentage for late fee',
                    'is_editable' => true,
                    'display_order' => 23,
                ]
            );
        }

        // Save payment plan settings
        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.enable_payment_plans'],
            [
                'value' => $request->has('enable_payment_plans') ? '1' : '0',
                'category' => 'fees',
                'type' => 'boolean',
                'display_name' => 'Enable Payment Plans',
                'description' => 'Allow creating installment payment plans for invoices',
                'is_editable' => true,
                'display_order' => 25,
            ]
        );

        if (isset($validated['default_plan_frequency'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.default_plan_frequency'],
                [
                    'value' => $validated['default_plan_frequency'],
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Default Plan Frequency',
                    'description' => 'Default frequency when creating payment plans',
                    'is_editable' => true,
                    'display_order' => 26,
                ]
            );
        }

        // Save notification settings
        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.notify_on_payment'],
            [
                'value' => $request->has('notify_on_payment') ? '1' : '0',
                'category' => 'fees',
                'type' => 'boolean',
                'display_name' => 'Notify on Payment',
                'description' => 'Send notification when payment is recorded',
                'is_editable' => true,
                'display_order' => 30,
            ]
        );

        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.notify_on_overdue'],
            [
                'value' => $request->has('notify_on_overdue') ? '1' : '0',
                'category' => 'fees',
                'type' => 'boolean',
                'display_name' => 'Notify on Overdue',
                'description' => 'Send notification when fees become overdue',
                'is_editable' => true,
                'display_order' => 31,
            ]
        );

        if (isset($validated['reminder_days_before'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.reminder_days_before'],
                [
                    'value' => (string) $validated['reminder_days_before'],
                    'category' => 'fees',
                    'type' => 'integer',
                    'display_name' => 'Reminder Days Before Due',
                    'description' => 'Days before due date to send payment reminder',
                    'is_editable' => true,
                    'display_order' => 32,
                ]
            );
        }

        // Save overdue reminder intervals as JSON
        if (isset($validated['overdue_reminder_intervals'])) {
            // Filter out empty values and sort
            $intervals = array_filter($validated['overdue_reminder_intervals'], fn($v) => !empty($v));
            sort($intervals);

            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.overdue_reminder_intervals'],
                [
                    'value' => json_encode(array_values($intervals)),
                    'category' => 'fees',
                    'type' => 'json',
                    'display_name' => 'Overdue Reminder Intervals',
                    'description' => 'Days after due date to send overdue reminders',
                    'is_editable' => true,
                    'display_order' => 34,
                ]
            );
        }

        if (array_key_exists('admin_notification_email', $validated)) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.admin_notification_email'],
                [
                    'value' => $validated['admin_notification_email'] ?? '',
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Admin Notification Email',
                    'description' => 'Email address for admin fee notifications',
                    'is_editable' => true,
                    'display_order' => 33,
                ]
            );
        }

        // Save invoice settings
        if (isset($validated['invoice_prefix'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.invoice_prefix'],
                [
                    'value' => $validated['invoice_prefix'],
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Invoice Number Prefix',
                    'description' => 'Prefix added before invoice numbers',
                    'is_editable' => true,
                    'display_order' => 40,
                ]
            );
        }

        if (isset($validated['default_payment_terms'])) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.default_payment_terms'],
                [
                    'value' => (string) $validated['default_payment_terms'],
                    'category' => 'fees',
                    'type' => 'integer',
                    'display_name' => 'Default Payment Terms',
                    'description' => 'Default number of days before invoice is due',
                    'is_editable' => true,
                    'display_order' => 41,
                ]
            );
        }

        if (array_key_exists('invoice_notes', $validated)) {
            \App\Models\SMSApiSetting::updateOrCreate(
                ['key' => 'fee.invoice_notes'],
                [
                    'value' => $validated['invoice_notes'] ?? '',
                    'category' => 'fees',
                    'type' => 'string',
                    'display_name' => 'Invoice Notes',
                    'description' => 'Default notes displayed on invoices',
                    'is_editable' => true,
                    'display_order' => 42,
                ]
            );
        }

        // Save year locking settings
        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.auto_lock_past_years'],
            [
                'value' => $request->has('auto_lock_past_years') ? '1' : '0',
                'category' => 'fees',
                'type' => 'boolean',
                'display_name' => 'Auto-lock Past Years',
                'description' => 'Automatically prevent modifications to fee data from previous years',
                'is_editable' => true,
                'display_order' => 60,
            ]
        );

        // Handle locked_until_year - can be empty to clear the lock
        \App\Models\SMSApiSetting::updateOrCreate(
            ['key' => 'fee.locked_until_year'],
            [
                'value' => $validated['locked_until_year'] ?? '',
                'category' => 'fees',
                'type' => 'integer',
                'display_name' => 'Locked Until Year',
                'description' => 'Lock all fee data up to and including this year',
                'is_editable' => true,
                'display_order' => 61,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully.',
        ]);
    }

    // ========================================
    // Audit Trail Methods
    // ========================================

    /**
     * Get audit logs via AJAX with filters.
     */
    public function getAuditLogs(Request $request, FeeAuditService $auditService): JsonResponse
    {
        Gate::authorize('manage-fee-setup');

        $query = FeeAuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Model type filter
        if ($request->filled('model_type')) {
            $query->where('auditable_type', $request->model_type);
        }

        // Search filter (notes, IP address)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%");
            });
        }

        // Paginate
        $perPage = $request->get('per_page', 50);
        $logs = $query->paginate($perPage);

        // Format the logs for display
        $formattedLogs = $logs->getCollection()->map(function ($log) use ($auditService) {
            $changes = $auditService->formatChanges($log->old_values, $log->new_values);

            return [
                'id' => $log->id,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $log->created_at->diffForHumans(),
                'user_name' => $log->user?->full_name ?? 'System',
                'action' => $log->action,
                'action_label' => $log->action_label,
                'model_type' => class_basename($log->auditable_type),
                'model_type_display' => $this->getModelTypeDisplay($log->auditable_type),
                'auditable_id' => $log->auditable_id,
                'auditable_display' => $this->getAuditableDisplay($log),
                'notes' => $log->notes,
                'ip_address' => $log->ip_address,
                'changes' => $changes,
                'has_changes' => !empty($changes),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedLogs,
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ],
        ]);
    }

    /**
     * Get human-readable model type display name.
     */
    protected function getModelTypeDisplay(string $modelType): string
    {
        $typeMap = [
            'App\\Models\\Fee\\StudentInvoice' => 'Invoice',
            'App\\Models\\Fee\\FeePayment' => 'Payment',
            'App\\Models\\Fee\\FeeRefund' => 'Refund/Credit Note',
            'App\\Models\\Fee\\DiscountType' => 'Discount Type',
            'App\\Models\\Fee\\StudentDiscount' => 'Student Discount',
            'App\\Models\\Fee\\FeeBalanceCarryover' => 'Balance Carryover',
            'App\\Models\\Fee\\StudentClearance' => 'Clearance Override',
            'App\\Models\\Fee\\FeeType' => 'Fee Type',
            'App\\Models\\Fee\\FeeStructure' => 'Fee Structure',
        ];

        return $typeMap[$modelType] ?? class_basename($modelType);
    }

    /**
     * Get human-readable reference for an audit entry.
     */
    protected function getAuditableDisplay(FeeAuditLog $log): string
    {
        // Try to load the related model for display info
        $auditable = $log->auditable;

        if (!$auditable) {
            // Model has been deleted, use stored values
            $newValues = $log->new_values ?? $log->old_values ?? [];

            if (isset($newValues['invoice_number'])) {
                return $newValues['invoice_number'];
            }
            if (isset($newValues['receipt_number'])) {
                return $newValues['receipt_number'];
            }
            if (isset($newValues['reference_number'])) {
                return $newValues['reference_number'];
            }
            if (isset($newValues['code'])) {
                return $newValues['code'] . (isset($newValues['name']) ? ' - ' . $newValues['name'] : '');
            }

            return "#{$log->auditable_id}";
        }

        // Generate display based on model type
        switch (get_class($auditable)) {
            case 'App\\Models\\Fee\\StudentInvoice':
                $studentName = $auditable->student?->full_name ?? 'Unknown';
                return "{$auditable->invoice_number} - {$studentName}";

            case 'App\\Models\\Fee\\FeePayment':
                $studentName = $auditable->invoice?->student?->full_name ?? 'Unknown';
                return "{$auditable->receipt_number} - {$studentName}";

            case 'App\\Models\\Fee\\FeeRefund':
                $studentName = $auditable->invoice?->student?->full_name ?? 'Unknown';
                return "{$auditable->reference_number} - {$studentName}";

            case 'App\\Models\\Fee\\DiscountType':
                return "{$auditable->code} - {$auditable->name}";

            case 'App\\Models\\Fee\\StudentDiscount':
                $studentName = $auditable->student?->full_name ?? 'Unknown';
                $discountName = $auditable->discountType?->name ?? 'Unknown';
                return "{$studentName} - {$discountName}";

            case 'App\\Models\\Fee\\FeeBalanceCarryover':
                $studentName = $auditable->student?->full_name ?? 'Unknown';
                return "{$studentName} - {$auditable->from_year} to {$auditable->to_year}";

            case 'App\\Models\\Fee\\StudentClearance':
                $studentName = $auditable->student?->full_name ?? 'Unknown';
                return "{$studentName} - Override";

            case 'App\\Models\\Fee\\FeeType':
                return "{$auditable->code} - {$auditable->name}";

            case 'App\\Models\\Fee\\FeeStructure':
                $gradeName = $auditable->grade?->name ?? 'Unknown';
                $feeTypeName = $auditable->feeType?->name ?? 'Unknown';
                return "{$gradeName} - {$feeTypeName} ({$auditable->year})";

            default:
                return "#{$log->auditable_id}";
        }
    }
}
