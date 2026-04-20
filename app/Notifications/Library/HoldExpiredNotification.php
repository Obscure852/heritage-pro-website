<?php

namespace App\Notifications\Library;

use App\Models\Library\LibraryReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HoldExpiredNotification extends Notification {
    use Queueable;

    protected LibraryReservation $reservation;

    /**
     * Create a new notification instance.
     *
     * @param LibraryReservation $reservation
     */
    public function __construct(LibraryReservation $reservation) {
        $this->reservation = $reservation;
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
        $bookTitle = $this->reservation->book->title ?? 'Unknown Book';

        return [
            'title' => 'Library Hold Expired',
            'message' => "Your hold on \"{$bookTitle}\" has expired as it was not collected within the pickup window.",
            'reservation_id' => $this->reservation->id,
            'book_title' => $bookTitle,
            'icon' => 'bx-time-five',
            'color' => 'warning',
        ];
    }
}
