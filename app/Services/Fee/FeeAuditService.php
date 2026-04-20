<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeePayment;
use App\Models\Fee\StudentInvoice;
use Illuminate\Database\Eloquent\Collection;

class FeeAuditService
{
    /**
     * Get audit history for an invoice.
     * Includes audit logs for the invoice itself and all its payments.
     *
     * @param StudentInvoice $invoice
     * @return Collection
     */
    public function getAuditHistoryForInvoice(StudentInvoice $invoice): Collection
    {
        // Get invoice audit logs
        $invoiceLogs = FeeAuditLog::forModel(StudentInvoice::class, $invoice->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get payment audit logs for this invoice's payments
        $paymentIds = $invoice->payments()->pluck('id');
        $paymentLogs = FeeAuditLog::where('auditable_type', FeePayment::class)
            ->whereIn('auditable_id', $paymentIds)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Merge and sort by created_at descending
        return $invoiceLogs->merge($paymentLogs)->sortByDesc('created_at')->values();
    }

    /**
     * Get audit history for a payment.
     *
     * @param FeePayment $payment
     * @return Collection
     */
    public function getAuditHistoryForPayment(FeePayment $payment): Collection
    {
        return FeeAuditLog::forModel(FeePayment::class, $payment->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent audit logs with optional filters.
     *
     * @param int $limit
     * @param string|null $action Filter by action type
     * @param int|null $userId Filter by user
     * @return Collection
     */
    public function getRecentAuditLogs(int $limit = 50, ?string $action = null, ?int $userId = null): Collection
    {
        $query = FeeAuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc');

        if ($action) {
            $query->byAction($action);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get audit logs for a specific date.
     *
     * @param string $date Y-m-d format
     * @return Collection
     */
    public function getAuditLogsForDate(string $date): Collection
    {
        return FeeAuditLog::with(['user', 'auditable'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Format changes for display (diff between old and new values).
     *
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return array List of changes with field, old_value, new_value
     */
    public function formatChanges(?array $oldValues, ?array $newValues): array
    {
        $changes = [];

        // For create actions (no old values)
        if ($oldValues === null && $newValues !== null) {
            foreach ($newValues as $field => $value) {
                if (!in_array($field, ['created_at', 'updated_at', 'id'])) {
                    $changes[] = [
                        'field' => $this->formatFieldName($field),
                        'old_value' => null,
                        'new_value' => $this->formatValue($value),
                    ];
                }
            }
            return $changes;
        }

        // For delete actions (no new values)
        if ($oldValues !== null && $newValues === null) {
            foreach ($oldValues as $field => $value) {
                if (!in_array($field, ['created_at', 'updated_at', 'id'])) {
                    $changes[] = [
                        'field' => $this->formatFieldName($field),
                        'old_value' => $this->formatValue($value),
                        'new_value' => null,
                    ];
                }
            }
            return $changes;
        }

        // For update actions (both old and new values)
        if ($oldValues !== null && $newValues !== null) {
            $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

            foreach ($allKeys as $field) {
                if (in_array($field, ['created_at', 'updated_at', 'id'])) {
                    continue;
                }

                $oldVal = $oldValues[$field] ?? null;
                $newVal = $newValues[$field] ?? null;

                // Only include if values are different
                if ($oldVal !== $newVal) {
                    $changes[] = [
                        'field' => $this->formatFieldName($field),
                        'old_value' => $this->formatValue($oldVal),
                        'new_value' => $this->formatValue($newVal),
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Format field name for display.
     */
    protected function formatFieldName(string $field): string
    {
        return ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display.
     */
    protected function formatValue($value): string
    {
        if ($value === null) {
            return '-';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
