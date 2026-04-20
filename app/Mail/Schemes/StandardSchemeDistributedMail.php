<?php

namespace App\Mail\Schemes;

use App\Models\SchoolSetup;
use App\Models\Schemes\StandardScheme;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StandardSchemeDistributedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $mailSubject;

    /**
     * @param array<int, array{scheme_id:int, label:string, url:string}> $schemeItems
     */
    public function __construct(
        public StandardScheme $standardScheme,
        public User $publisher,
        public User $recipient,
        public array $schemeItems,
        public ?SchoolSetup $schoolSetup = null,
    ) {
        $this->schoolSetup ??= SchoolSetup::query()->latest()->first();

        $subjectName = $this->standardScheme->subject?->name ?? 'Standard Scheme';
        $gradeName = $this->standardScheme->grade?->name ?? 'Grade';
        $termLabel = 'Term ' . ($this->standardScheme->term?->term ?? '—') . ' ' . ($this->standardScheme->term?->year ?? '—');

        $this->mailSubject = "New Scheme of Work Available: {$subjectName} {$gradeName} {$termLabel}";
    }

    public function build(): self
    {
        return $this->subject($this->mailSubject)
            ->view('emails.schemes.standard-scheme-distributed')
            ->with([
                'standardScheme' => $this->standardScheme,
                'publisher' => $this->publisher,
                'recipient' => $this->recipient,
                'schemeItems' => $this->schemeItems,
                'schoolSetup' => $this->schoolSetup,
            ]);
    }
}
