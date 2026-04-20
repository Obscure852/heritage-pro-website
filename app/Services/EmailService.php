<?php

namespace App\Services;

use App\Models\Email;
use App\Models\SchoolSetup;
use App\Mail\BulkEmail;
use App\Jobs\SendBulkEmailJob;
use App\Helpers\TermHelper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmailService
{
    protected $recipientService;

    public function __construct(RecipientService $recipientService)
    {
        $this->recipientService = $recipientService;
    }

    /**
     * Send a direct email to a single recipient
     *
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendDirectEmail(array $data): array
    {
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()?->id);
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;

        try {
            // Handle attachment
            if (!empty($data['attachment'])) {
                $attachment = $data['attachment'];
                $attachmentPath = $attachment->store('email_attachments');
                $attachmentName = $attachment->getClientOriginalName();
                $attachmentMime = $attachment->getMimeType();
            }

            // Get school details
            $schoolData = SchoolSetup::first();
            $details = [
                'subject' => $data['subject'],
                'body' => $data['body'],
                'schoolName' => $schoolData->school_name ?? 'Heritage Pro',
                'schoolEmail' => $schoolData->email ?? '',
                'schoolPhone' => $schoolData->phone ?? '',
                'schoolAddress' => $schoolData->postal_address ?? '',
                'schoolWebsite' => $schoolData->website ?? '',
                'schoolLogo' => $schoolData->logo ?? null,
            ];

            // Send email
            $email = new BulkEmail($details, $attachmentPath, $attachmentName, $attachmentMime);
            Mail::to($data['recipient_email'])->send($email);
            $status = 'sent';

        } catch (\Exception $e) {
            $status = 'failed';
            Log::error('Direct email failed', [
                'recipient' => $data['recipient_email'],
                'error' => $e->getMessage()
            ]);

            // Cleanup attachment on failure
            if ($attachmentPath && Storage::exists($attachmentPath)) {
                Storage::delete($attachmentPath);
            }

            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }

        // Log email
        $this->logEmail([
            'term_id' => $termId,
            'sender_id' => auth()->id(),
            'receiver_type' => $data['receiver_type'],
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'attachment_path' => $attachmentPath,
            'status' => $status,
            'num_of_recipients' => 1,
            'type' => 'Direct',
        ]);

        return [
            'success' => true,
            'message' => 'Email sent successfully'
        ];
    }

    /**
     * Send bulk emails to multiple recipients
     *
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'count' => int]
     */
    public function sendBulkEmails(array $data): array
    {
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()?->id);
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;

        try {
            // Handle attachment
            if (!empty($data['attachment'])) {
                $attachment = $data['attachment'];
                $attachmentPath = $attachment->store('email_attachments');
                $attachmentName = $attachment->getClientOriginalName();
                $attachmentMime = $attachment->getMimeType();
            }

            // Get recipients
            $recipients = $this->recipientService->getEmailRecipients($data);
            $recipientCount = $recipients->count();

            if ($recipientCount === 0) {
                return [
                    'success' => false,
                    'message' => 'No recipients found with valid email addresses',
                    'count' => 0
                ];
            }

            // Get school details
            $schoolData = SchoolSetup::first();
            $details = [
                'subject' => $data['subject'],
                'body' => $data['message'], // Note: bulk uses 'message' key
                'schoolName' => $schoolData->school_name ?? 'Heritage Pro',
                'schoolEmail' => $schoolData->email ?? '',
                'schoolPhone' => $schoolData->phone ?? '',
                'schoolAddress' => $schoolData->postal_address ?? '',
                'schoolWebsite' => $schoolData->website ?? '',
                'schoolLogo' => $schoolData->logo ?? null,
            ];

            // Queue all emails (no synchronous threshold)
            foreach ($recipients as $recipient) {
                SendBulkEmailJob::dispatch(
                    $recipient,
                    $details,
                    $attachmentPath,
                    $attachmentName,
                    $attachmentMime
                );
            }

            // Log bulk email summary
            $this->logBulkEmail([
                'term_id' => $termId,
                'sender_id' => auth()->id(),
                'receiver_type' => $data['recipient_type'],
                'subject' => $data['subject'],
                'body' => $data['message'],
                'attachment_path' => $attachmentPath,
                'num_of_recipients' => $recipientCount,
                'type' => 'Bulk',
                'filters' => $data,
            ]);

            return [
                'success' => true,
                'message' => "Bulk emails queued successfully. {$recipientCount} emails will be sent.",
                'count' => $recipientCount
            ];

        } catch (\Exception $e) {
            Log::error('Bulk email failed', [
                'error' => $e->getMessage(),
                'filters' => $data
            ]);

            // Cleanup attachment on failure
            if ($attachmentPath && Storage::exists($attachmentPath)) {
                Storage::delete($attachmentPath);
            }

            return [
                'success' => false,
                'message' => 'Failed to send bulk emails: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Count recipients for email campaign
     *
     * @param array $filters
     * @return int
     */
    public function countRecipients(array $filters): int
    {
        return $this->recipientService->countEmailRecipients($filters);
    }

    /**
     * Log individual email
     *
     * @param array $data
     * @return Email
     */
    protected function logEmail(array $data): Email
    {
        return Email::create($data);
    }

    /**
     * Log bulk email summary
     *
     * @param array $data
     * @return Email
     */
    protected function logBulkEmail(array $data): Email
    {
        // Store filters as JSON if provided
        if (isset($data['filters'])) {
            $data['filters'] = json_encode($data['filters']);
        }

        return Email::create($data);
    }

    /**
     * Send email to a single recipient (used by jobs)
     *
     * @param mixed $recipient
     * @param array $details
     * @param string|null $attachmentPath
     * @param string|null $attachmentName
     * @param string|null $attachmentMime
     * @return string 'sent' or 'failed'
     */
    public function sendToRecipient(
        $recipient,
        array $details,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
        ?string $attachmentMime = null
    ): string {
        try {
            $email = new BulkEmail($details, $attachmentPath, $attachmentName, $attachmentMime);
            Mail::to($recipient->email)->send($email);
            return 'sent';
        } catch (\Exception $e) {
            Log::error('Email to recipient failed', [
                'recipient' => $recipient->email,
                'error' => $e->getMessage()
            ]);
            return 'failed';
        }
    }

    /**
     * Get emails by term
     *
     * @param int|null $termId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmailsByTerm(?int $termId = null)
    {
        $termId = $termId ?? session('selected_term_id', TermHelper::getCurrentTerm()?->id);

        return Email::where('term_id', $termId)
            ->with(['sender', 'user', 'sponsor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Batch insert multiple email records
     *
     * Use this instead of individual Email::create() calls for bulk operations
     * to reduce database load by 95%
     *
     * @param array $emailRecords Array of email data arrays
     * @return int Number of records inserted
     */
    public function batchInsertEmails(array $emailRecords): int
    {
        if (empty($emailRecords)) {
            return 0;
        }

        // Add timestamps to all records
        $now = now();
        foreach ($emailRecords as &$record) {
            $record['created_at'] = $now;
            $record['updated_at'] = $now;

            // Encode filters as JSON if array provided
            if (isset($record['filters']) && is_array($record['filters'])) {
                $record['filters'] = json_encode($record['filters']);
            }
        }

        // Batch insert all records in one query
        Email::insert($emailRecords);

        return count($emailRecords);
    }
}
