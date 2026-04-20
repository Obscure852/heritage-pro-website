<?php

namespace App\Notifications\Library;

use App\Models\Library\LibraryReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HoldReadyNotification extends Notification {
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
        $expiresAt = $this->reservation->expires_at
            ? $this->reservation->expires_at->format('d M Y')
            : 'N/A';

        return [
            'title' => 'Library Reservation Ready',
            'message' => "Your reserved book \"{$bookTitle}\" is ready for pickup. Please collect it by {$expiresAt}.",
            'reservation_id' => $this->reservation->id,
            'book_title' => $bookTitle,
            'expires_at' => $this->reservation->expires_at?->toDateTimeString(),
            'icon' => 'bx-book-bookmark',
            'color' => 'success',
        ];
    }
}
