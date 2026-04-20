<?php

namespace App\Services\Library;

use App\Helpers\LinkSMSHelper;
use App\Models\Library\LibraryOverdueNotice;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Models\User;
use App\Notifications\Library\EscalationNotification;
use App\Notifications\Library\OverdueBookNotification;
use Illuminate\Support\Facades\Log;

class LibraryNotificationService {
    protected LinkSMSHelper $smsHelper;

    public function __construct(LinkSMSHelper $smsHelper) {
        $this->smsHelper = $smsHelper;
    }

    // ==================== OVERDUE NOTIFICATIONS ====================

    /**
     * Send overdue notification to a borrower with deduplication.
     *
     * Sends in-app notification (database channel) and optionally SMS
     * when overdue_sms_enabled setting is true.
     *
     * @param LibraryTransaction $transaction
     * @param int $daysOverdue The threshold day (e.g., 1, 7, 14)
     * @return array Result with 'sent', 'skipped', 'channels' keys
     */
    public function sendOverdueNotification(LibraryTransaction $transaction, int $daysOverdue): array {
        // Dedup check: prevent duplicate sends for same transaction+type+day
        if (LibraryOverdueNotice::alreadySent($transaction->id, 'overdue_reminder', $daysOverdue)) {
            return ['skipped' => true, 'reason' => 'already_sent'];
        }

        $channels = [];
        $borrower = $transaction->borrower;

        if (!$borrower) {
            Log::warning('Cannot send overdue notification: borrower not found', [
                'transaction_id' => $transaction->id,
            ]);
            return ['skipped' => true, 'reason' => 'no_borrower'];
        }

        // Send in-app notification via Laravel database channel
        try {
            $borrower->notify(new OverdueBookNotification($transaction, $daysOverdue));
            $channels[] = 'in_app';

            LibraryOverdueNotice::create([
                'library_transaction_id' => $transaction->id,
                'borrower_type' => $transaction->borrower_type,
                'borrower_id' => $transaction->borrower_id,
                'notice_type' => 'overdue_reminder',
                'channel' => 'in_app',
                'days_overdue' => $daysOverdue,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send in-app overdue notification', [
                'transaction_id' => $transaction->id,
                'days_overdue' => $daysOverdue,
                'error' => $e->getMessage(),
            ]);
            return ['sent' => false, 'error' => $e->getMessage()];
        }

        // SMS: only when overdue_sms_enabled setting is true
        $smsEnabled = LibrarySetting::get('overdue_sms_enabled', false);
        if ($smsEnabled) {
            $this->sendOverdueSms($transaction, $daysOverdue, $channels);
        }

        return ['sent' => true, 'channels' => $channels];
    }

    // ==================== ESCALATION NOTIFICATIONS ====================

    /**
     * Send escalation notification to a supervisor.
     *
     * Resolves the escalation target (class_teacher or hod) and sends
     * an in-app notification. Falls back to checked_out_by user if
     * the target cannot be resolved.
     *
     * @param LibraryTransaction $transaction
     * @param int $daysOverdue
     * @param string $escalationType 'class_teacher' or 'hod'
     * @return array Result with 'sent', 'skipped', 'target' keys
     */
    public function sendEscalationNotification(LibraryTransaction $transaction, int $daysOverdue, string $escalationType): array {
        // Dedup check
        if (LibraryOverdueNotice::alreadySent($transaction->id, 'escalation', $daysOverdue)) {
            return ['skipped' => true, 'reason' => 'already_sent'];
        }

        $borrower = $transaction->borrower;
        $borrowerName = $this->resolveBorrowerName($borrower);
        $target = $this->resolveEscalationTarget($transaction, $escalationType);

        if (!$target) {
            Log::warning('Cannot resolve escalation target, skipping', [
                'transaction_id' => $transaction->id,
                'escalation_type' => $escalationType,
            ]);
            return ['skipped' => true, 'reason' => 'no_target'];
        }

        try {
            $target->notify(new EscalationNotification($transaction, $daysOverdue, $borrowerName));

            LibraryOverdueNotice::create([
                'library_transaction_id' => $transaction->id,
                'borrower_type' => $transaction->borrower_type,
                'borrower_id' => $transaction->borrower_id,
                'notice_type' => 'escalation',
                'channel' => 'in_app',
                'days_overdue' => $daysOverdue,
                'escalated_to' => $escalationType,
                'sent_at' => now(),
            ]);

            return ['sent' => true, 'target' => $target->name ?? 'Unknown', 'escalation_type' => $escalationType];
        } catch (\Exception $e) {
            Log::error('Failed to send escalation notification', [
                'transaction_id' => $transaction->id,
                'escalation_type' => $escalationType,
                'error' => $e->getMessage(),
            ]);
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    // ==================== LOST DECLARATION NOTICE ====================

    /**
     * Send lost declaration notice to borrower.
     *
     * Notifies the borrower that their overdue book has been declared lost.
     *
     * @param LibraryTransaction $transaction
     * @param int $daysOverdue
     * @return void
     */
    public function sendLostDeclarationNotice(LibraryTransaction $transaction, int $daysOverdue): void {
        $borrower = $transaction->borrower;

        if (!$borrower) {
            Log::warning('Cannot send lost declaration notice: borrower not found', [
                'transaction_id' => $transaction->id,
            ]);
            return;
        }

        try {
            $bookTitle = optional($transaction->copy)->book->title ?? 'Unknown Book';

            // Reuse OverdueBookNotification with a "lost" context message
            // The notification channel (database) will store it for the borrower
            $borrower->notify(new OverdueBookNotification($transaction, $daysOverdue));

            LibraryOverdueNotice::create([
                'library_transaction_id' => $transaction->id,
                'borrower_type' => $transaction->borrower_type,
                'borrower_id' => $transaction->borrower_id,
                'notice_type' => 'lost_declaration',
                'channel' => 'in_app',
                'days_overdue' => $daysOverdue,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send lost declaration notice', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ==================== PRIVATE HELPERS ====================

    /**
     * Send SMS for overdue notification.
     *
     * Resolves phone number from borrower (User.phone or Student.sponsor.phone)
     * and sends via LinkSMSHelper. SMS failures are logged but do not stop processing.
     *
     * @param LibraryTransaction $transaction
     * @param int $daysOverdue
     * @param array &$channels Appended with 'sms' if sent
     */
    protected function sendOverdueSms(LibraryTransaction $transaction, int $daysOverdue, array &$channels): void {
        $borrower = $transaction->borrower;
        $phone = $this->resolvePhone($borrower, $transaction->borrower_type);

        if (!$phone) {
            Log::debug('Skipping overdue SMS: no phone number resolved', [
                'transaction_id' => $transaction->id,
                'borrower_type' => $transaction->borrower_type,
            ]);
            return;
        }

        $bookTitle = optional($transaction->copy)->book->title ?? 'Unknown Book';
        $message = "Library Overdue: \"{$bookTitle}\" is {$daysOverdue} days overdue. Please return it to the library as soon as possible.";

        try {
            $this->smsHelper->sendMessage(
                $message,
                $phone,
                $transaction->borrower_id,
                $transaction->borrower_type,
                'Library Overdue',
                1
            );

            $channels[] = 'sms';

            LibraryOverdueNotice::create([
                'library_transaction_id' => $transaction->id,
                'borrower_type' => $transaction->borrower_type,
                'borrower_id' => $transaction->borrower_id,
                'notice_type' => 'overdue_reminder',
                'channel' => 'sms',
                'days_overdue' => $daysOverdue,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send overdue SMS', [
                'transaction_id' => $transaction->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolve phone number for a borrower.
     *
     * For User (staff): use $borrower->phone
     * For Student: try $borrower->sponsor->phone, then $borrower->phone
     *
     * @param mixed $borrower
     * @param string $borrowerType
     * @return string|null
     */
    protected function resolvePhone($borrower, string $borrowerType): ?string {
        if (!$borrower) {
            return null;
        }

        if ($borrowerType === 'user') {
            // Staff member: use their phone directly
            return !empty($borrower->phone) ? $borrower->phone : null;
        }

        // Student: try sponsor phone first, then student phone
        if ($borrowerType === 'student') {
            $sponsor = $borrower->sponsor;
            if ($sponsor && !empty($sponsor->phone)) {
                return $sponsor->phone;
            }

            return !empty($borrower->phone) ? $borrower->phone : null;
        }

        return null;
    }

    /**
     * Resolve escalation target User model.
     *
     * 'class_teacher': For Student borrowers, resolve via klass->teacher.
     * 'hod': Try department HOD resolution via roles.
     * Always falls back to $transaction->checkedOutBy (the librarian).
     *
     * @param LibraryTransaction $transaction
     * @param string $escalationType
     * @return User|null
     */
    protected function resolveEscalationTarget(LibraryTransaction $transaction, string $escalationType): ?User {
        $borrower = $transaction->borrower;
        $fallback = $transaction->checkedOutBy;

        if ($escalationType === 'class_teacher' && $transaction->borrower_type === 'student' && $borrower) {
            try {
                // Student -> classes (belongsToMany Klass) -> latest class -> teacher
                $latestClass = $borrower->classes()->latest('klass_student.created_at')->first();
                if ($latestClass && $latestClass->teacher) {
                    return $latestClass->teacher;
                }
            } catch (\Exception $e) {
                Log::debug('Could not resolve class teacher for escalation', [
                    'student_id' => $borrower->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($escalationType === 'hod') {
            try {
                // Try to find an HOD user. HODs are Users with the HOD role.
                // Without a direct department linkage, fall back to checked_out_by.
                // This is a best-effort resolution.
                $hod = User::whereHas('roles', function ($q) {
                    $q->where('name', 'HOD');
                })->first();

                if ($hod) {
                    return $hod;
                }
            } catch (\Exception $e) {
                Log::debug('Could not resolve HOD for escalation', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback: the user who checked out the book (librarian)
        return $fallback;
    }

    /**
     * Resolve borrower display name with fallback chain.
     *
     * @param mixed $borrower
     * @return string
     */
    protected function resolveBorrowerName($borrower): string {
        if (!$borrower) {
            return 'Unknown Borrower';
        }

        return $borrower->full_name ?? $borrower->name ?? 'Unknown Borrower';
    }
}
