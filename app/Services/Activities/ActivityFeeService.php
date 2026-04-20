<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityFeeCharge;
use App\Models\Fee\StudentInvoice;
use App\Models\Fee\StudentInvoiceItem;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ActivityFeeService
{
    public function createCharge(Activity $activity, array $data, User $actor): ActivityFeeCharge
    {
        return DB::transaction(function () use ($activity, $data, $actor) {
            $lockedActivity = Activity::query()
                ->with(['feeType', 'term'])
                ->lockForUpdate()
                ->findOrFail($activity->id);

            $this->assertChargeChangesAllowed($lockedActivity);
            $this->assertFeeSetupPresent($lockedActivity);

            $enrollment = $this->resolveActiveEnrollment($lockedActivity, (int) $data['student_id']);
            $event = $this->resolveChargeEvent($lockedActivity, $data);

            $charge = $lockedActivity->feeCharges()->create([
                'activity_enrollment_id' => $enrollment->id,
                'activity_event_id' => $event?->id,
                'student_id' => $enrollment->student_id,
                'fee_type_id' => $lockedActivity->fee_type_id,
                'term_id' => $lockedActivity->term_id,
                'year' => $lockedActivity->year,
                'charge_type' => $data['charge_type'],
                'amount' => number_format((float) $data['amount'], 2, '.', ''),
                'billing_status' => ActivityFeeCharge::STATUS_PENDING,
                'generated_by' => $actor->id,
                'generated_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'fee_charge_created',
                null,
                $this->chargeSnapshot($charge->fresh()),
                'Activity fee charge created.'
            );

            return $this->attemptPosting($lockedActivity, $charge->fresh(), $actor, false);
        });
    }

    public function postCharge(Activity $activity, ActivityFeeCharge $charge, User $actor): ActivityFeeCharge
    {
        return DB::transaction(function () use ($activity, $charge, $actor) {
            $lockedActivity = Activity::query()
                ->with(['feeType', 'term'])
                ->lockForUpdate()
                ->findOrFail($activity->id);

            $lockedCharge = ActivityFeeCharge::query()
                ->lockForUpdate()
                ->findOrFail($charge->id);

            $this->assertChargeBelongsToActivity($lockedActivity, $lockedCharge);

            return $this->attemptPosting($lockedActivity, $lockedCharge, $actor, true);
        });
    }

    public function activityBillingSummary(Activity $activity): array
    {
        $charges = $activity->feeCharges()
            ->with(['invoice:id,balance'])
            ->get();

        $postedCharges = $charges->where('billing_status', ActivityFeeCharge::STATUS_POSTED);

        return [
            'total_count' => $charges->count(),
            'pending_count' => $charges->where('billing_status', ActivityFeeCharge::STATUS_PENDING)->count(),
            'posted_count' => $postedCharges->count(),
            'blocked_count' => $charges->where('billing_status', ActivityFeeCharge::STATUS_BLOCKED)->count(),
            'total_amount' => $charges->sum('amount'),
            'posted_amount' => $postedCharges->sum('amount'),
            'pending_amount' => $charges->where('billing_status', ActivityFeeCharge::STATUS_PENDING)->sum('amount'),
            'blocked_amount' => $charges->where('billing_status', ActivityFeeCharge::STATUS_BLOCKED)->sum('amount'),
            'outstanding_amount' => $postedCharges
                ->filter(fn (ActivityFeeCharge $charge) => (float) ($charge->invoice?->balance ?? 0) > 0)
                ->sum('amount'),
        ];
    }

    public function studentSummary(Student $student, int $termId): array
    {
        $activeEnrollments = ActivityEnrollment::query()
            ->with([
                'activity:id,name,code,status,term_id,year',
                'gradeSnapshot:id,name',
                'klassSnapshot:id,name',
            ])
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->where('status', ActivityEnrollment::STATUS_ACTIVE)
            ->orderByDesc('joined_at')
            ->get();

        $historicalEnrollments = ActivityEnrollment::query()
            ->with([
                'activity:id,name,code,status,term_id,year',
                'gradeSnapshot:id,name',
                'klassSnapshot:id,name',
            ])
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->where('status', '!=', ActivityEnrollment::STATUS_ACTIVE)
            ->orderByDesc('left_at')
            ->limit(6)
            ->get();

        $charges = ActivityFeeCharge::query()
            ->with([
                'activity:id,name,code',
                'event:id,title',
                'invoice:id,invoice_number,status,balance',
                'feeType:id,name',
            ])
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->get();

        return [
            'activeEnrollments' => $activeEnrollments,
            'historicalEnrollments' => $historicalEnrollments,
            'charges' => $charges,
            'summary' => [
                'active_count' => $activeEnrollments->count(),
                'charge_count' => $charges->count(),
                'posted_count' => $charges->where('billing_status', ActivityFeeCharge::STATUS_POSTED)->count(),
                'pending_count' => $charges->where('billing_status', ActivityFeeCharge::STATUS_PENDING)->count(),
                'blocked_count' => $charges->where('billing_status', ActivityFeeCharge::STATUS_BLOCKED)->count(),
                'total_amount' => $charges->sum('amount'),
            ],
        ];
    }

    private function attemptPosting(Activity $activity, ActivityFeeCharge $charge, User $actor, bool $throwIfUnavailable): ActivityFeeCharge
    {
        if ($charge->billing_status === ActivityFeeCharge::STATUS_POSTED || $charge->student_invoice_item_id) {
            if ($throwIfUnavailable) {
                throw ValidationException::withMessages([
                    'charge' => 'This charge is already linked to an invoice item.',
                ]);
            }

            return $charge->fresh(['invoice', 'invoiceItem', 'event', 'student']);
        }

        $invoice = $this->findActiveAnnualInvoice($charge->student_id, $charge->year);

        if (!$invoice) {
            $cancelledInvoice = $this->findCancelledAnnualInvoice($charge->student_id, $charge->year);

            if ($cancelledInvoice) {
                $before = $this->chargeSnapshot($charge);

                $charge->forceFill([
                    'billing_status' => ActivityFeeCharge::STATUS_BLOCKED,
                    'student_invoice_id' => $cancelledInvoice->id,
                    'student_invoice_item_id' => null,
                ])->save();

                $this->recordAudit(
                    $actor,
                    $activity,
                    'fee_charge_blocked',
                    $before,
                    $this->chargeSnapshot($charge->fresh()),
                    'Activity fee charge is blocked because the annual invoice is cancelled.'
                );

                if ($throwIfUnavailable) {
                    throw ValidationException::withMessages([
                        'charge' => sprintf(
                            'The student has a cancelled %d annual invoice. Create or restore an active annual invoice before posting this charge.',
                            $charge->year
                        ),
                    ]);
                }

                return $charge->fresh(['invoice', 'invoiceItem', 'event', 'student']);
            }

            $charge->forceFill([
                'billing_status' => ActivityFeeCharge::STATUS_PENDING,
                'student_invoice_id' => null,
                'student_invoice_item_id' => null,
            ])->save();

            if ($throwIfUnavailable) {
                throw ValidationException::withMessages([
                    'charge' => 'Generate the student annual invoice first, then retry posting this activity charge.',
                ]);
            }

            return $charge->fresh(['invoice', 'invoiceItem', 'event', 'student']);
        }

        $before = $this->chargeSnapshot($charge);
        $invoiceItem = $this->appendChargeToInvoice($charge, $invoice);

        $charge->forceFill([
            'billing_status' => ActivityFeeCharge::STATUS_POSTED,
            'student_invoice_id' => $invoice->id,
            'student_invoice_item_id' => $invoiceItem->id,
        ])->save();

        $this->recordAudit(
            $actor,
            $activity,
            'fee_charge_posted',
            $before,
            $this->chargeSnapshot($charge->fresh()),
            sprintf('Activity fee charge posted to invoice %s.', $invoice->invoice_number)
        );

        return $charge->fresh(['invoice', 'invoiceItem', 'event', 'student']);
    }

    private function appendChargeToInvoice(ActivityFeeCharge $charge, StudentInvoice $invoice): StudentInvoiceItem
    {
        if ($invoice->isCancelled()) {
            throw ValidationException::withMessages([
                'invoice' => 'Cannot post an activity charge to a cancelled invoice.',
            ]);
        }

        $invoiceItem = StudentInvoiceItem::query()->create([
            'student_invoice_id' => $invoice->id,
            'activity_fee_charge_id' => $charge->id,
            'fee_structure_id' => null,
            'item_type' => StudentInvoiceItem::TYPE_ACTIVITY_FEE,
            'description' => $this->chargeDescription($charge),
            'amount' => $charge->amount,
            'discount_amount' => '0.00',
            'net_amount' => $charge->amount,
        ]);

        $invoice->subtotal_amount = bcadd((string) $invoice->subtotal_amount, (string) $charge->amount, 2);
        $invoice->total_amount = bcadd((string) $invoice->total_amount, (string) $charge->amount, 2);
        $invoice->save();

        return $invoiceItem;
    }

    private function findActiveAnnualInvoice(int $studentId, int $year): ?StudentInvoice
    {
        return StudentInvoice::query()
            ->lockForUpdate()
            ->where('student_id', $studentId)
            ->where('year', $year)
            ->whereNull('deleted_at')
            ->where('status', '!=', StudentInvoice::STATUS_CANCELLED)
            ->latest('id')
            ->first();
    }

    private function findCancelledAnnualInvoice(int $studentId, int $year): ?StudentInvoice
    {
        return StudentInvoice::query()
            ->lockForUpdate()
            ->where('student_id', $studentId)
            ->where('year', $year)
            ->whereNull('deleted_at')
            ->where('status', StudentInvoice::STATUS_CANCELLED)
            ->latest('id')
            ->first();
    }

    private function assertChargeChangesAllowed(Activity $activity): void
    {
        if ($activity->status === Activity::STATUS_ARCHIVED) {
            throw ValidationException::withMessages([
                'activity' => 'Charges cannot be created once an activity is archived.',
            ]);
        }
    }

    private function assertFeeSetupPresent(Activity $activity): void
    {
        if (!$activity->fee_type_id) {
            throw ValidationException::withMessages([
                'fee_type' => 'Assign an activity fee type before generating charges from this page.',
            ]);
        }
    }

    private function resolveActiveEnrollment(Activity $activity, int $studentId): ActivityEnrollment
    {
        $enrollment = ActivityEnrollment::query()
            ->where('activity_id', $activity->id)
            ->where('student_id', $studentId)
            ->where('status', ActivityEnrollment::STATUS_ACTIVE)
            ->first();

        if (!$enrollment) {
            throw ValidationException::withMessages([
                'student_id' => 'Select a student with an active roster entry before generating a charge.',
            ]);
        }

        return $enrollment;
    }

    private function resolveChargeEvent(Activity $activity, array $data): ?ActivityEvent
    {
        $eventId = $data['activity_event_id'] ?? null;
        $chargeType = (string) $data['charge_type'];

        if ($chargeType === ActivityFeeCharge::CHARGE_TYPE_EVENT && !$eventId) {
            throw ValidationException::withMessages([
                'activity_event_id' => 'Select the related event when posting an event fee.',
            ]);
        }

        if (!$eventId) {
            return null;
        }

        $event = ActivityEvent::query()
            ->where('activity_id', $activity->id)
            ->find($eventId);

        if (!$event) {
            throw ValidationException::withMessages([
                'activity_event_id' => 'The selected event does not belong to this activity.',
            ]);
        }

        return $event;
    }

    private function assertChargeBelongsToActivity(Activity $activity, ActivityFeeCharge $charge): void
    {
        if ($charge->activity_id !== $activity->id) {
            throw ValidationException::withMessages([
                'charge' => 'The selected charge does not belong to this activity.',
            ]);
        }
    }

    private function chargeDescription(ActivityFeeCharge $charge): string
    {
        $chargeTypeLabel = ActivityFeeCharge::chargeTypes()[$charge->charge_type] ?? Str::headline($charge->charge_type);

        $description = sprintf(
            '%s: %s%s',
            $chargeTypeLabel,
            $charge->activity?->name ?? 'Activity',
            $charge->activity?->code ? ' (' . $charge->activity->code . ')' : ''
        );

        if ($charge->event?->title) {
            $description .= ' - ' . $charge->event->title;
        }

        return $description;
    }

    private function chargeSnapshot(ActivityFeeCharge $charge): array
    {
        return [
            'id' => $charge->id,
            'student_id' => $charge->student_id,
            'activity_enrollment_id' => $charge->activity_enrollment_id,
            'activity_event_id' => $charge->activity_event_id,
            'fee_type_id' => $charge->fee_type_id,
            'charge_type' => $charge->charge_type,
            'amount' => (string) $charge->amount,
            'billing_status' => $charge->billing_status,
            'student_invoice_id' => $charge->student_invoice_id,
            'student_invoice_item_id' => $charge->student_invoice_item_id,
        ];
    }

    private function recordAudit(User $user, Activity $activity, string $action, ?array $oldValues, ?array $newValues, string $notes): void
    {
        ActivityAuditLog::create([
            'user_id' => $user->id,
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => request()?->ip(),
            'user_agent' => (string) request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
