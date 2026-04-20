<?php

namespace App\Services\Fee;

use App\Models\Fee\FeePayment;
use App\Models\Fee\StudentInvoice;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentTerm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for generating fee reports and analytics.
 *
 * Reports are filtered by year (annual) rather than by term.
 */
class ReportingService
{
    /**
     * Format amount as currency (P X,XXX.XX).
     */
    protected function formatCurrency($amount): string
    {
        return 'P ' . number_format((float) $amount, 2, '.', ',');
    }

    /**
     * Get dashboard statistics.
     *
     * @param int|null $year Filter by year, or null for all time
     * @return array{
     *     total_invoiced: string,
     *     total_collected: string,
     *     total_outstanding: string,
     *     collection_rate: string,
     *     invoice_count: int,
     *     payment_count: int,
     *     student_count_with_balance: int
     * }
     */
    public function getDashboardStats(?int $year = null): array
    {
        // Use database aggregation for better performance
        $invoiceQuery = StudentInvoice::active();
        if ($year !== null) {
            $invoiceQuery->forYear($year);
        }

        // Calculate totals using database aggregation (single query)
        $invoiceStats = (clone $invoiceQuery)
            ->selectRaw('
                COALESCE(SUM(total_amount), 0) as total_invoiced,
                COALESCE(SUM(amount_paid), 0) as total_paid,
                COUNT(*) as invoice_count
            ')
            ->first();

        $totalInvoiced = number_format((float) ($invoiceStats->total_invoiced ?? 0), 2, '.', '');
        $totalPaid = number_format((float) ($invoiceStats->total_paid ?? 0), 2, '.', '');
        $invoiceCount = (int) ($invoiceStats->invoice_count ?? 0);

        $totalOutstanding = bcsub($totalInvoiced, $totalPaid, 2);

        // Collection rate calculation (avoid division by zero)
        $collectionRate = '0.00';
        if (bccomp($totalInvoiced, '0.00', 2) > 0) {
            $collectionRate = bcmul(bcdiv($totalPaid, $totalInvoiced, 4), '100', 2);
        }

        // Count students with outstanding balance (separate query for distinct count)
        $studentCountWithBalance = (clone $invoiceQuery)
            ->where('balance', '>', 0)
            ->distinct('student_id')
            ->count('student_id');

        // Payment count
        $paymentQuery = FeePayment::notVoided();
        if ($year !== null) {
            $paymentQuery->forYear($year);
        }
        $paymentCount = $paymentQuery->count();

        return [
            'total_invoiced' => $totalInvoiced,
            'total_collected' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'collection_rate' => $collectionRate,
            'invoice_count' => $invoiceCount,
            'payment_count' => $paymentCount,
            'student_count_with_balance' => $studentCountWithBalance,
        ];
    }

    /**
     * Get collection summary for a date range.
     *
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param int|null $year Optional year filter
     * @return array{
     *     total_collected: string,
     *     payment_count: int,
     *     by_method: array<string, array{count: int, total_amount: string}>,
     *     daily_breakdown: array<string, string>
     * }
     */
    public function getCollectionSummary(string $startDate, string $endDate, ?int $year = null): array
    {
        $baseQuery = FeePayment::notVoided()
            ->forDateRange($startDate, $endDate);

        if ($year !== null) {
            $baseQuery->forYear($year);
        }

        // Get total and count using database aggregation
        $totals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_collected, COUNT(*) as payment_count')
            ->first();

        $totalCollected = number_format((float) ($totals->total_collected ?? 0), 2, '.', '');
        $paymentCount = (int) ($totals->payment_count ?? 0);

        // Get by method breakdown using database aggregation
        $byMethodResults = (clone $baseQuery)
            ->selectRaw('payment_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('payment_method')
            ->get();

        $byMethod = [];
        foreach ($byMethodResults as $row) {
            $byMethod[$row->payment_method] = [
                'count' => (int) $row->count,
                'total_amount' => number_format((float) $row->total_amount, 2, '.', ''),
            ];
        }

        // Get daily breakdown using database aggregation
        $dailyResults = (clone $baseQuery)
            ->selectRaw('DATE(payment_date) as date, COALESCE(SUM(amount), 0) as total')
            ->groupBy(DB::raw('DATE(payment_date)'))
            ->orderBy('date')
            ->get();

        $dailyBreakdown = [];
        foreach ($dailyResults as $row) {
            $dailyBreakdown[$row->date] = number_format((float) $row->total, 2, '.', '');
        }

        return [
            'total_collected' => $totalCollected,
            'payment_count' => $paymentCount,
            'by_method' => $byMethod,
            'daily_breakdown' => $dailyBreakdown,
        ];
    }

    /**
     * Get collections grouped by payment method.
     *
     * @param int|null $year Optional year filter
     * @param string|null $startDate Optional start date
     * @param string|null $endDate Optional end date
     * @return array Array of payment method data with payment_method, count, total_amount, percentage
     */
    public function getCollectionsByMethod(?int $year = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = FeePayment::notVoided();

        if ($year !== null) {
            $query->forYear($year);
        }

        if ($startDate !== null && $endDate !== null) {
            $query->forDateRange($startDate, $endDate);
        }

        // Use database aggregation for better performance
        $results = $query
            ->selectRaw('payment_method, COUNT(*) as payment_count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('payment_method')
            ->get();

        // Calculate grand total
        $grandTotal = $results->sum('total_amount');
        $grandTotalStr = number_format((float) $grandTotal, 2, '.', '');

        // Build result array with percentages
        $byMethod = [];
        foreach ($results as $row) {
            $totalAmount = number_format((float) $row->total_amount, 2, '.', '');
            $percentage = '0.00';

            if (bccomp($grandTotalStr, '0.00', 2) > 0) {
                $percentage = bcmul(bcdiv($totalAmount, $grandTotalStr, 4), '100', 2);
            }

            $byMethod[] = [
                'payment_method' => $row->payment_method,
                'payment_count' => (int) $row->payment_count,
                'total_amount' => $totalAmount,
                'percentage' => $percentage,
            ];
        }

        return $byMethod;
    }

    /**
     * Get student statement data (for PDF generation).
     *
     * @param int $studentId
     * @param int|null $year Optional year filter
     * @return array{
     *     student: Student,
     *     invoices: Collection,
     *     payments: Collection,
     *     balance_history: array,
     *     summary: array
     * }
     */
    public function getStudentStatement(int $studentId, ?int $year = null): array
    {
        $student = Student::findOrFail($studentId);

        // Get invoices
        $invoiceQuery = StudentInvoice::forStudent($studentId)->active();
        if ($year !== null) {
            $invoiceQuery->forYear($year);
        }
        $invoices = $invoiceQuery->with('items')->orderBy('issued_at', 'asc')->get();

        // Get payments
        $paymentQuery = FeePayment::forStudent($studentId)->notVoided();
        if ($year !== null) {
            $paymentQuery->forYear($year);
        }
        $payments = $paymentQuery->orderBy('payment_date', 'asc')->get();

        // Build running balance history (merge invoices and payments chronologically)
        $balanceHistory = [];
        $runningBalance = '0.00';

        // Collect all transactions
        $transactions = [];

        foreach ($invoices as $invoice) {
            $transactions[] = [
                'date' => $invoice->issued_at,
                'type' => 'invoice',
                'reference' => $invoice->invoice_number,
                'description' => 'Invoice #' . $invoice->invoice_number,
                'debit' => (string) $invoice->total_amount,
                'credit' => '0.00',
                'item' => $invoice,
            ];
        }

        foreach ($payments as $payment) {
            $transactions[] = [
                'date' => Carbon::parse($payment->payment_date),
                'type' => 'payment',
                'reference' => $payment->receipt_number,
                'description' => 'Payment #' . $payment->receipt_number,
                'debit' => '0.00',
                'credit' => (string) $payment->amount,
                'item' => $payment,
            ];
        }

        // Sort by date
        usort($transactions, function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        // Calculate running balance
        foreach ($transactions as $transaction) {
            $runningBalance = bcadd($runningBalance, $transaction['debit'], 2);
            $runningBalance = bcsub($runningBalance, $transaction['credit'], 2);

            $balanceHistory[] = [
                'date' => $transaction['date']->format('Y-m-d'),
                'type' => $transaction['type'],
                'reference' => $transaction['reference'],
                'description' => $transaction['description'],
                'debit' => $transaction['debit'],
                'credit' => $transaction['credit'],
                'balance' => $runningBalance,
            ];
        }

        // Calculate summary
        $totalInvoiced = '0.00';
        $totalPaid = '0.00';

        foreach ($invoices as $invoice) {
            $totalInvoiced = bcadd($totalInvoiced, (string) $invoice->total_amount, 2);
        }

        foreach ($payments as $payment) {
            $totalPaid = bcadd($totalPaid, (string) $payment->amount, 2);
        }

        $balance = bcsub($totalInvoiced, $totalPaid, 2);

        return [
            'student' => $student,
            'invoices' => $invoices,
            'payments' => $payments,
            'balance_history' => $balanceHistory,
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'balance' => $balance,
            ],
        ];
    }

    /**
     * Get student payment history.
     *
     * @param int $studentId
     * @param int|null $year Optional year filter
     * @return Collection All payments with invoice details, ordered by payment_date desc
     */
    public function getStudentPaymentHistory(int $studentId, ?int $year = null): Collection
    {
        $query = FeePayment::forStudent($studentId)
            ->notVoided()
            ->with(['invoice', 'receivedBy']);

        if ($year !== null) {
            $query->forYear($year);
        }

        return $query->orderBy('payment_date', 'desc')->get();
    }

    /**
     * Get outstanding balances grouped by grade.
     *
     * @param int $year
     * @return array<int, array{
     *     grade_id: int,
     *     grade_name: string,
     *     student_count: int,
     *     total_outstanding: string,
     *     average_per_student: string
     * }>
     */
    public function getOutstandingByGrade(int $year): array
    {
        // Get student enrollments for this year (through term relationship)
        $studentTerms = StudentTerm::whereHas('term', fn($q) => $q->where('year', $year))
            ->where('status', 'Current')
            ->with('grade')
            ->get()
            ->unique('student_id'); // One entry per student

        // Get all outstanding invoices for this year
        $invoices = StudentInvoice::forYear($year)
            ->active()
            ->where('balance', '>', 0)
            ->get()
            ->keyBy('student_id');

        // Group by grade
        $byGrade = [];

        foreach ($studentTerms as $studentTerm) {
            $gradeId = $studentTerm->grade_id;

            if (!isset($byGrade[$gradeId])) {
                $byGrade[$gradeId] = [
                    'grade_id' => $gradeId,
                    'grade_name' => $studentTerm->grade->name ?? 'Unknown',
                    'student_count' => 0,
                    'total_outstanding' => '0.00',
                ];
            }

            // Check if student has outstanding balance
            if (isset($invoices[$studentTerm->student_id])) {
                $byGrade[$gradeId]['student_count']++;
                $byGrade[$gradeId]['total_outstanding'] = bcadd(
                    $byGrade[$gradeId]['total_outstanding'],
                    (string) $invoices[$studentTerm->student_id]->balance,
                    2
                );
            }
        }

        // Calculate averages and format result
        $result = [];
        foreach ($byGrade as $gradeId => $data) {
            $averagePerStudent = '0.00';
            if ($data['student_count'] > 0) {
                $averagePerStudent = bcdiv($data['total_outstanding'], (string) $data['student_count'], 2);
            }

            $result[] = [
                'grade_id' => $data['grade_id'],
                'grade_name' => $data['grade_name'],
                'student_count' => $data['student_count'],
                'total_outstanding' => $data['total_outstanding'],
                'average_per_student' => $averagePerStudent,
            ];
        }

        // Sort by grade name
        usort($result, function ($a, $b) {
            return $a['grade_name'] <=> $b['grade_name'];
        });

        return $result;
    }

    /**
     * Get aging report with 30/60/90 day buckets.
     *
     * @param int $year
     * @return array{
     *     summary: array{
     *         current: array{count: int, amount: string},
     *         overdue_30: array{count: int, amount: string},
     *         overdue_60: array{count: int, amount: string},
     *         overdue_90: array{count: int, amount: string}
     *     },
     *     details: array
     * }
     */
    public function getAgingReport(int $year): array
    {
        $today = Carbon::today();

        // Get all outstanding invoices for this year
        $invoices = StudentInvoice::forYear($year)
            ->active()
            ->where('balance', '>', 0)
            ->with(['student.currentGrade'])
            ->get();

        // Initialize buckets with keys matching view expectations
        $summary = [
            'current' => ['count' => 0, 'total_amount' => '0.00'],      // 0-30 days
            '30_days' => ['count' => 0, 'total_amount' => '0.00'],      // 31-60 days
            '60_days' => ['count' => 0, 'total_amount' => '0.00'],      // 61-90 days
            '90_days' => ['count' => 0, 'total_amount' => '0.00'],      // 90+ days
        ];

        // Invoice-level details for the table
        $details = [];

        foreach ($invoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_date);
            $daysOverdue = max(0, $today->diffInDays($dueDate, false) * -1);
            $balance = (string) $invoice->balance;

            // Categorize into aging bucket
            if ($daysOverdue <= 30) {
                $summary['current']['count']++;
                $summary['current']['total_amount'] = bcadd($summary['current']['total_amount'], $balance, 2);
            } elseif ($daysOverdue <= 60) {
                $summary['30_days']['count']++;
                $summary['30_days']['total_amount'] = bcadd($summary['30_days']['total_amount'], $balance, 2);
            } elseif ($daysOverdue <= 90) {
                $summary['60_days']['count']++;
                $summary['60_days']['total_amount'] = bcadd($summary['60_days']['total_amount'], $balance, 2);
            } else {
                $summary['90_days']['count']++;
                $summary['90_days']['total_amount'] = bcadd($summary['90_days']['total_amount'], $balance, 2);
            }

            // Add invoice-level detail
            $details[] = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'student_id' => $invoice->student_id,
                'student_name' => $invoice->student->full_name ?? ($invoice->student->first_name . ' ' . $invoice->student->last_name),
                'grade_name' => $invoice->student->currentGrade->name ?? 'N/A',
                'due_date' => $invoice->due_date,
                'balance' => $balance,
                'days_overdue' => $daysOverdue,
            ];
        }

        // Sort details by days overdue (most overdue first)
        usort($details, function ($a, $b) {
            return $b['days_overdue'] <=> $a['days_overdue'];
        });

        return [
            'summary' => $summary,
            'details' => $details,
        ];
    }

    /**
     * Get list of all debtors (students with outstanding balance).
     *
     * @param int $year
     * @param int|null $gradeId Optional grade filter
     * @param float|null $minBalance Optional minimum balance filter
     * @return \Illuminate\Support\Collection
     */
    public function getDebtorsList(int $year, ?int $gradeId = null, ?float $minBalance = null): \Illuminate\Support\Collection
    {
        $today = Carbon::today();

        // Get student enrollments for this year
        $studentTermQuery = StudentTerm::whereHas('term', fn($q) => $q->where('year', $year))
            ->where('status', 'Current')
            ->with(['student', 'grade']);

        if ($gradeId !== null) {
            $studentTermQuery->where('grade_id', $gradeId);
        }

        $studentTerms = $studentTermQuery->get()
            ->unique('student_id')
            ->keyBy('student_id');

        // Get all outstanding invoices for this year
        $invoices = StudentInvoice::forYear($year)
            ->active()
            ->where('balance', '>', 0)
            ->get();

        // Aggregate by student
        $debtors = [];

        foreach ($invoices as $invoice) {
            $studentId = $invoice->student_id;

            // Skip if student not enrolled in this year (or filtered by grade)
            if (!isset($studentTerms[$studentId])) {
                continue;
            }

            $studentTerm = $studentTerms[$studentId];

            if (!isset($debtors[$studentId])) {
                $student = $studentTerm->student;
                $debtors[$studentId] = [
                    'student_id' => $studentId,
                    'student_name' => $student->full_name ?? ($student->first_name . ' ' . $student->last_name),
                    'student_number' => $student->student_number ?? $student->id,
                    'grade_name' => $studentTerm->grade->name ?? 'Unknown',
                    'total_invoiced' => '0.00',
                    'total_paid' => '0.00',
                    'balance' => '0.00',
                    'oldest_invoice_date' => $invoice->due_date,
                    'days_overdue' => 0,
                ];
            }

            $debtors[$studentId]['total_invoiced'] = bcadd(
                $debtors[$studentId]['total_invoiced'],
                (string) $invoice->total_amount,
                2
            );
            $debtors[$studentId]['total_paid'] = bcadd(
                $debtors[$studentId]['total_paid'],
                (string) $invoice->amount_paid,
                2
            );
            $debtors[$studentId]['balance'] = bcadd(
                $debtors[$studentId]['balance'],
                (string) $invoice->balance,
                2
            );

            // Track oldest invoice and days overdue
            $dueDate = Carbon::parse($invoice->due_date);
            $existingOldest = Carbon::parse($debtors[$studentId]['oldest_invoice_date']);
            if ($dueDate->lt($existingOldest)) {
                $debtors[$studentId]['oldest_invoice_date'] = $invoice->due_date;
            }

            // Calculate days overdue from oldest invoice
            $oldestDueDate = Carbon::parse($debtors[$studentId]['oldest_invoice_date']);
            $daysOverdue = max(0, $today->diffInDays($oldestDueDate, false) * -1);
            $debtors[$studentId]['days_overdue'] = $daysOverdue;
        }

        // Apply minimum balance filter
        if ($minBalance !== null) {
            $minBalanceStr = number_format($minBalance, 2, '.', '');
            $debtors = array_filter($debtors, function ($debtor) use ($minBalanceStr) {
                return bccomp($debtor['balance'], $minBalanceStr, 2) >= 0;
            });
        }

        // Sort by balance descending (highest owed first)
        $debtors = array_values($debtors);
        usort($debtors, function ($a, $b) {
            return bccomp($b['balance'], $a['balance'], 2);
        });

        return collect($debtors);
    }

    /**
     * Get collector performance metrics.
     *
     * @param int $year
     * @param string|null $startDate Optional start date filter
     * @param string|null $endDate Optional end date filter
     * @return array<int, array{
     *     collector_id: int,
     *     collector_name: string,
     *     total_collected: string,
     *     payment_count: int,
     *     average_payment: string,
     *     by_method: array
     * }>
     */
    public function getCollectorPerformance(int $year, ?string $startDate = null, ?string $endDate = null): array
    {
        $baseQuery = FeePayment::forYear($year)->notVoided();

        if ($startDate !== null && $endDate !== null) {
            $baseQuery->forDateRange($startDate, $endDate);
        }

        // Get collector totals using database aggregation
        $collectorTotals = (clone $baseQuery)
            ->join('users', 'fee_payments.received_by', '=', 'users.id')
            ->selectRaw("
                fee_payments.received_by as collector_id,
                CONCAT(users.firstname, ' ', users.lastname) as collector_name,
                COALESCE(SUM(fee_payments.amount), 0) as total_collected,
                COUNT(*) as payment_count
            ")
            ->groupBy('fee_payments.received_by', 'users.firstname', 'users.lastname')
            ->orderByDesc('total_collected')
            ->get();

        // Get method breakdown per collector using database aggregation
        $methodBreakdown = (clone $baseQuery)
            ->selectRaw('
                received_by as collector_id,
                payment_method,
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount
            ')
            ->groupBy('received_by', 'payment_method')
            ->get()
            ->groupBy('collector_id');

        // Build result
        $result = [];
        foreach ($collectorTotals as $row) {
            $collectorId = $row->collector_id;
            $totalCollected = number_format((float) $row->total_collected, 2, '.', '');
            $paymentCount = (int) $row->payment_count;

            // Calculate average
            $averagePayment = '0.00';
            if ($paymentCount > 0) {
                $averagePayment = bcdiv($totalCollected, (string) $paymentCount, 2);
            }

            // Build method breakdown for this collector
            $byMethod = [];
            if (isset($methodBreakdown[$collectorId])) {
                foreach ($methodBreakdown[$collectorId] as $methodRow) {
                    $byMethod[] = [
                        'payment_method' => $methodRow->payment_method,
                        'count' => (int) $methodRow->count,
                        'total_amount' => number_format((float) $methodRow->total_amount, 2, '.', ''),
                    ];
                }
            }

            $result[] = [
                'collector_id' => $collectorId,
                'collector_name' => $row->collector_name ?? 'Unknown',
                'total_collected' => $totalCollected,
                'payment_count' => $paymentCount,
                'average_payment' => $averagePayment,
                'by_method' => $byMethod,
            ];
        }

        return $result;
    }

    /**
     * Get payment trends over time.
     *
     * @param int $year
     * @param string $groupBy Grouping period: 'day', 'week', or 'month'
     * @return array<int, array{
     *     period: string,
     *     total_amount: string,
     *     payment_count: int,
     *     cumulative_amount: string
     * }>
     */
    public function getPaymentTrends(int $year, string $groupBy = 'day'): array
    {
        // Use database aggregation with appropriate date grouping
        $query = FeePayment::forYear($year)->notVoided();

        // Determine the SQL date format based on groupBy
        switch ($groupBy) {
            case 'week':
                // MySQL YEARWEEK function or DATE_SUB to get start of week
                $periodSelect = "DATE(DATE_SUB(payment_date, INTERVAL WEEKDAY(payment_date) DAY)) as period";
                break;
            case 'month':
                $periodSelect = "DATE_FORMAT(payment_date, '%Y-%m') as period";
                break;
            case 'day':
            default:
                $periodSelect = "DATE(payment_date) as period";
                break;
        }

        $results = $query
            ->selectRaw("{$periodSelect}, COALESCE(SUM(amount), 0) as total_amount, COUNT(*) as payment_count")
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();

        // Build result with cumulative amounts
        $result = [];
        $cumulativeAmount = '0.00';

        foreach ($results as $row) {
            $totalAmount = number_format((float) $row->total_amount, 2, '.', '');
            $cumulativeAmount = bcadd($cumulativeAmount, $totalAmount, 2);

            $result[] = [
                'period' => $row->period,
                'total_amount' => $totalAmount,
                'payment_count' => (int) $row->payment_count,
                'cumulative_amount' => $cumulativeAmount,
            ];
        }

        return $result;
    }

    /**
     * Get comparison across multiple years.
     *
     * @param array<int> $years
     * @return array<int, array{
     *     year: int,
     *     total_invoiced: string,
     *     total_collected: string,
     *     collection_rate: string,
     *     student_count: int
     * }>
     */
    public function getYearComparison(array $years): array
    {
        if (empty($years)) {
            return [];
        }

        // Use database aggregation to get all years' data in a single query
        $results = StudentInvoice::active()
            ->whereIn('year', $years)
            ->selectRaw('
                year,
                COALESCE(SUM(total_amount), 0) as total_invoiced,
                COALESCE(SUM(amount_paid), 0) as total_collected,
                COUNT(DISTINCT student_id) as student_count
            ')
            ->groupBy('year')
            ->get()
            ->keyBy('year');

        $result = [];
        foreach ($years as $year) {
            $row = $results->get($year);

            $totalInvoiced = number_format((float) ($row->total_invoiced ?? 0), 2, '.', '');
            $totalCollected = number_format((float) ($row->total_collected ?? 0), 2, '.', '');

            // Calculate collection rate
            $collectionRate = '0.00';
            if (bccomp($totalInvoiced, '0.00', 2) > 0) {
                $collectionRate = bcmul(bcdiv($totalCollected, $totalInvoiced, 4), '100', 2);
            }

            $result[] = [
                'year' => $year,
                'total_invoiced' => $totalInvoiced,
                'total_collected' => $totalCollected,
                'collection_rate' => $collectionRate,
                'student_count' => (int) ($row->student_count ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Get comparison across grades for a year.
     *
     * @param int $year
     * @return array<int, array{
     *     grade_id: int,
     *     grade_name: string,
     *     total_invoiced: string,
     *     total_collected: string,
     *     collection_rate: string,
     *     student_count: int
     * }>
     */
    public function getGradeComparison(int $year): array
    {
        // Get student enrollments for this year
        $studentTerms = StudentTerm::whereHas('term', fn($q) => $q->where('year', $year))
            ->where('status', 'Current')
            ->with('grade')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id');

        // Get all invoices for this year
        $invoices = StudentInvoice::forYear($year)->active()->get();

        // Aggregate by grade
        $byGrade = [];

        foreach ($invoices as $invoice) {
            $studentId = $invoice->student_id;

            // Get student's grade for this year
            if (!isset($studentTerms[$studentId])) {
                continue;
            }

            $gradeId = $studentTerms[$studentId]->grade_id;
            $gradeName = $studentTerms[$studentId]->grade->name ?? 'Unknown';

            if (!isset($byGrade[$gradeId])) {
                $byGrade[$gradeId] = [
                    'grade_id' => $gradeId,
                    'grade_name' => $gradeName,
                    'total_invoiced' => '0.00',
                    'total_collected' => '0.00',
                    'collection_rate' => '0.00',
                    'student_ids' => [],
                ];
            }

            $byGrade[$gradeId]['total_invoiced'] = bcadd(
                $byGrade[$gradeId]['total_invoiced'],
                (string) $invoice->total_amount,
                2
            );
            $byGrade[$gradeId]['total_collected'] = bcadd(
                $byGrade[$gradeId]['total_collected'],
                (string) $invoice->amount_paid,
                2
            );
            $byGrade[$gradeId]['student_ids'][$studentId] = true;
        }

        // Calculate collection rates and student counts
        $result = [];
        foreach ($byGrade as $gradeId => $data) {
            $collectionRate = '0.00';
            if (bccomp($data['total_invoiced'], '0.00', 2) > 0) {
                $collectionRate = bcmul(bcdiv($data['total_collected'], $data['total_invoiced'], 4), '100', 2);
            }

            $result[] = [
                'grade_id' => $data['grade_id'],
                'grade_name' => $data['grade_name'],
                'total_invoiced' => $data['total_invoiced'],
                'total_collected' => $data['total_collected'],
                'collection_rate' => $collectionRate,
                'student_count' => count($data['student_ids']),
            ];
        }

        // Sort by grade name
        usort($result, function ($a, $b) {
            return $a['grade_name'] <=> $b['grade_name'];
        });

        return $result;
    }

    /**
     * Get top debtors by outstanding balance.
     *
     * @param int $year
     * @param int $limit Maximum number of debtors to return
     * @return \Illuminate\Support\Collection
     */
    public function getTopDebtors(int $year, int $limit = 10): \Illuminate\Support\Collection
    {
        $debtors = $this->getDebtorsList($year);

        return $debtors->take($limit);
    }

    /**
     * Get most recent payments (for dashboard quick view).
     *
     * @param int $limit Maximum number of payments to return
     * @return array Array of formatted payment data
     */
    public function getRecentPayments(int $limit = 10): array
    {
        $payments = FeePayment::notVoided()
            ->with(['student', 'invoice.student'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $payments->map(function ($payment) {
            $student = $payment->invoice?->student ?? $payment->student;

            return [
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'student_name' => $student?->full_name ?? ($student?->first_name . ' ' . $student?->last_name) ?? 'N/A',
                'amount' => (string) $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date,
            ];
        })->toArray();
    }

    // ========================================
    // Daily Operations Reports
    // ========================================

    /**
     * Get daily collections for a specific date.
     *
     * @param string $date Y-m-d format
     * @param int|null $year Optional year filter
     * @return array{
     *     date: string,
     *     total_collected: string,
     *     payment_count: int,
     *     payments: array,
     *     by_method: array,
     *     by_collector: array
     * }
     */
    public function getDailyCollections(string $date, ?int $year = null): array
    {
        $baseQuery = FeePayment::notVoided()
            ->whereDate('payment_date', $date);

        if ($year !== null) {
            $baseQuery->forYear($year);
        }

        // Get totals using database aggregation
        $totals = (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_collected, COUNT(*) as payment_count')
            ->first();

        $totalCollected = number_format((float) ($totals->total_collected ?? 0), 2, '.', '');
        $paymentCount = (int) ($totals->payment_count ?? 0);

        // Get by method breakdown using database aggregation
        $methodResults = (clone $baseQuery)
            ->selectRaw('payment_method, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('payment_method')
            ->get();

        $byMethod = [];
        foreach ($methodResults as $row) {
            $byMethod[$row->payment_method] = [
                'count' => (int) $row->count,
                'total' => number_format((float) $row->total, 2, '.', ''),
            ];
        }

        // Get by collector breakdown using database aggregation
        $collectorResults = (clone $baseQuery)
            ->join('users', 'fee_payments.received_by', '=', 'users.id')
            ->selectRaw("CONCAT(users.firstname, ' ', users.lastname) as collector_name, COUNT(*) as count, COALESCE(SUM(fee_payments.amount), 0) as total")
            ->groupBy('fee_payments.received_by', 'users.firstname', 'users.lastname')
            ->get();

        $byCollector = [];
        foreach ($collectorResults as $row) {
            $byCollector[] = [
                'name' => $row->collector_name ?? 'Unknown',
                'count' => (int) $row->count,
                'total' => number_format((float) $row->total, 2, '.', ''),
            ];
        }

        // Get detailed payment list (only if needed for display - kept for backward compatibility)
        $payments = (clone $baseQuery)
            ->with(['invoice.student', 'receivedBy'])
            ->orderBy('created_at', 'asc')
            ->get();

        $paymentsData = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'student_name' => $payment->invoice->student->full_name ?? 'N/A',
                'student_number' => $payment->invoice->student->student_number ?? 'N/A',
                'amount' => (string) $payment->amount,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'received_by' => $payment->receivedBy?->name ?? 'Unknown',
                'created_at' => $payment->created_at->format('H:i:s'),
            ];
        })->toArray();

        return [
            'date' => $date,
            'total_collected' => $totalCollected,
            'payment_count' => $paymentCount,
            'payments' => $paymentsData,
            'by_method' => $byMethod,
            'by_collector' => $byCollector,
        ];
    }

    /**
     * Get end-of-day report for a specific date.
     * More comprehensive than daily collections, includes opening/closing summary.
     *
     * @param string $date Y-m-d format
     * @param int|null $year Optional year filter
     * @return array{
     *     date: string,
     *     generated_at: string,
     *     generated_by: string|null,
     *     summary: array,
     *     by_method: array,
     *     by_collector: array,
     *     payments: array
     * }
     */
    public function getEndOfDayReport(string $date, ?int $year = null): array
    {
        $dailyData = $this->getDailyCollections($date, $year);

        // Calculate opening balance using database aggregation
        $invoicesBeforeQuery = StudentInvoice::active()
            ->whereDate('created_at', '<', $date);

        if ($year !== null) {
            $invoicesBeforeQuery->forYear($year);
        }

        $totalInvoicedBefore = (clone $invoicesBeforeQuery)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total')
            ->value('total') ?? 0;
        $totalInvoicedBefore = number_format((float) $totalInvoicedBefore, 2, '.', '');

        $paymentsBeforeQuery = FeePayment::notVoided()
            ->whereDate('payment_date', '<', $date);

        if ($year !== null) {
            $paymentsBeforeQuery->forYear($year);
        }

        $totalPaidBefore = $paymentsBeforeQuery
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total') ?? 0;
        $totalPaidBefore = number_format((float) $totalPaidBefore, 2, '.', '');

        $openingBalance = bcsub($totalInvoicedBefore, $totalPaidBefore, 2);

        // Calculate invoiced today using database aggregation
        $invoicesTodayQuery = StudentInvoice::active()
            ->whereDate('created_at', $date);

        if ($year !== null) {
            $invoicesTodayQuery->forYear($year);
        }

        $invoicesTodayStats = $invoicesTodayQuery
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total, COUNT(*) as count')
            ->first();

        $totalInvoicedToday = number_format((float) ($invoicesTodayStats->total ?? 0), 2, '.', '');
        $invoiceCountToday = (int) ($invoicesTodayStats->count ?? 0);

        // Closing balance = opening + invoiced today - collected today
        $closingBalance = bcsub(
            bcadd($openingBalance, $totalInvoicedToday, 2),
            $dailyData['total_collected'],
            2
        );

        return [
            'date' => $date,
            'generated_at' => now()->format('d M Y H:i:s'),
            'generated_by' => auth()->user()?->name,
            'summary' => [
                'opening_balance' => $openingBalance,
                'invoiced_today' => $totalInvoicedToday,
                'invoice_count_today' => $invoiceCountToday,
                'collected_today' => $dailyData['total_collected'],
                'payment_count_today' => $dailyData['payment_count'],
                'closing_balance' => $closingBalance,
            ],
            'by_method' => $dailyData['by_method'],
            'by_collector' => $dailyData['by_collector'],
            'payments' => $dailyData['payments'],
        ];
    }
}
