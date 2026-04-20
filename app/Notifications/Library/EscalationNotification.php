<?php

namespace App\Notifications\Library;

use App\Models\Library\LibraryTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EscalationNotification extends Notification {
    use Queueable;

    protected LibraryTransaction $transaction;
    protected int $daysOverdue;
    protected string $borrowerName;

    /**
     * Create a new notification instance.
     *
     * @param LibraryTransaction $transaction
     * @param int $daysOverdue
     * @param string $borrowerName
     */
    public function __construct(LibraryTransaction $transaction, int $daysOverdue, string $borrowerName) {
        $this->transaction = $transaction;
        $this->daysOverdue = $daysOverdue;
        $this->borrowerName = $borrowerName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        $bookTitle = optional($this->transaction->copy)->book->title ?? 'Unknown Book';

        return [
            'title' => 'Library Overdue Escalation',
            'message' => "{$this->borrowerName} has \"{$bookTitle}\" overdue for {$this->daysOverdue} days.",
            'transaction_id' => $this->transaction->id,
            'days_overdue' => $this->daysOverdue,
            'borrower_name' => $this->borrowerName,
            'book_title' => $bookTitle,
            'icon' => 'bx-error',
            'color' => 'warning',
        ];
    }
}
