<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SchoolSetup;

class AdmissionCompleted extends Mailable{
    use Queueable, SerializesModels;

    public $admission;

    public function __construct($admission){
        $this->admission = $admission;
    }

    public function build(){
        return $this->subject('Your Admission Application')
                    ->view('admissions.admission-completed')
                    ->with([
                        'admission' => $this->admission,
                        'school_data' => SchoolSetup::first()
                    ]);
    }
}
