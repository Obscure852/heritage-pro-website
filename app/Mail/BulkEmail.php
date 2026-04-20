<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BulkEmail extends Mailable{
    use Queueable, SerializesModels;

    public $details;
    public $attachmentPath;
    public $attachmentName;
    public $attachmentMime;

    public function __construct($details, $attachmentPath = null, $attachmentName = null, $attachmentMime = null){
        $this->details = $details;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
        $this->attachmentMime = $attachmentMime;
    }

    public function envelope(){
        return new Envelope(
            subject: $this->details['subject'],
        );
    }

    public function content(){
        return new Content(
            view: 'emails.bulk-email',
        );
    }

    public function build(){
        $email = $this->subject($this->details['subject'])
                      ->view('emails.bulk-email')
                      ->with('details', $this->details);

        if ($this->attachmentPath) {
            // Sanitize path to prevent directory traversal
            $safePath = basename($this->attachmentPath);
            $directory = dirname($this->attachmentPath);
            // Only allow paths within expected directories
            if ($directory !== '.' && !str_starts_with($directory, '..')) {
                $safePath = $directory . '/' . $safePath;
            }
            $fullPath = storage_path('app/' . $safePath);

            if (file_exists($fullPath)) {
                $email->attach($fullPath, [
                    'as' => $this->attachmentName ?? basename($safePath),
                    'mime' => $this->attachmentMime ?? 'application/octet-stream',
                ]);
            }
        }
        return $email;
    }
    
}