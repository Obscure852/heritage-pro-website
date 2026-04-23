<?php

namespace App\Services\Crm;

use App\Models\CrmAttendanceCorrection;
use App\Models\CrmAttendanceRecord;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AttendanceNotificationService
{
    public function __construct(
        private readonly DiscussionDeliveryService $discussionService
    ) {
    }

    public function notifyLateArrival(CrmAttendanceRecord $record): void
    {
        $user = $record->user;
        $manager = $user?->reportsTo;

        if (! $manager) {
            return;
        }

        $this->sendMessage(
            $this->systemSender(),
            $manager,
            "Late arrival: {$user->name} clocked in at {$record->clocked_in_at->format('H:i')} on {$record->date->format('d M Y')}."
        );
    }

    public function notifyAutoClose(CrmAttendanceRecord $record): void
    {
        $user = $record->user;

        if (! $user) {
            return;
        }

        $this->sendMessage(
            $this->systemSender(),
            $user,
            "Your clock-out on {$record->date->format('d M Y')} was automatically recorded at 23:59 because you did not clock out. Please submit a correction if this is incorrect."
        );
    }

    public function notifyAbsentMarked(CrmAttendanceRecord $record): void
    {
        $user = $record->user;
        $manager = $user?->reportsTo;

        if (! $manager) {
            return;
        }

        $this->sendMessage(
            $this->systemSender(),
            $manager,
            "Absent: {$user->name} had no clock-in recorded for {$record->date->format('d M Y')} and has been marked absent."
        );
    }

    public function notifyCorrectionSubmitted(CrmAttendanceCorrection $correction): void
    {
        $user = $correction->requester;
        $manager = $user?->reportsTo;

        if (! $manager) {
            return;
        }

        $date = $correction->record?->date?->format('d M Y') ?? 'unknown date';

        $this->sendMessage(
            $this->systemSender(),
            $manager,
            "Correction request: {$user->name} has submitted a correction for {$date}. Reason: {$correction->reason}"
        );
    }

    public function notifyCorrectionApproved(CrmAttendanceCorrection $correction): void
    {
        $user = $correction->requester;

        if (! $user) {
            return;
        }

        $date = $correction->record?->date?->format('d M Y') ?? 'unknown date';
        $reviewer = $correction->reviewer?->name ?? 'a manager';

        $this->sendMessage(
            $this->systemSender(),
            $user,
            "Your correction request for {$date} has been approved by {$reviewer}."
        );
    }

    public function notifyCorrectionRejected(CrmAttendanceCorrection $correction): void
    {
        $user = $correction->requester;

        if (! $user) {
            return;
        }

        $date = $correction->record?->date?->format('d M Y') ?? 'unknown date';
        $reviewer = $correction->reviewer?->name ?? 'a manager';
        $reason = $correction->rejection_reason ? " Reason: {$correction->rejection_reason}" : '';

        $this->sendMessage(
            $this->systemSender(),
            $user,
            "Your correction request for {$date} has been rejected by {$reviewer}.{$reason}"
        );
    }

    private function sendMessage(User $sender, User $recipient, string $body): void
    {
        if ((int) $sender->id === (int) $recipient->id) {
            return;
        }

        try {
            $thread = $this->discussionService->startOrResumeDirectThread($sender, $recipient, [
                'subject' => 'Attendance Notification',
            ]);

            $this->discussionService->storeAppMessage($thread, $sender, $body);
        } catch (\Throwable $e) {
            Log::warning('Attendance notification failed', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function systemSender(): User
    {
        return User::query()
            ->where('role', 'admin')
            ->where('active', true)
            ->orderBy('id')
            ->firstOrFail();
    }
}
