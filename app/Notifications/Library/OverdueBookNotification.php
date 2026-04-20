<?php

namespace App\Notifications\Library;

use App\Models\Library\LibraryTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueBookNotification extends Notification {
    use Queueable;

    protected LibraryTransaction $transaction;
    protected int $daysOverdue;

    /**
     * Create a new notification instance.
     *
     * @param LibraryTransaction $transaction
     * @param int $daysOverdue
     */
    public function __construct(LibraryTransaction $transaction, int $daysOverdue) {
        $this->transaction = $transaction;
        $this->daysOverdue = $daysOverdue;
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
            'title' => 'Overdue Library Book',
            'message' => "\"{$bookTitle}\" is {$this->daysOverdue} days overdue. Please return it to the library.",
            'transaction_id' => $this->transaction->id,
            'days_overdue' => $this->daysOverdue,
            'due_date' => $this->transaction->due_date->toDateString(),
            'book_title' => $bookTitle,
            'icon' => 'bx-error-circle',
            'color' => 'danger',
        ];
    }
}
