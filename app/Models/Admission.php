<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model{
    use HasFactory, SoftDeletes;
    public $timestamps = true;
    
    protected $fillable = [
        'sponsor_id',
        'connect_id',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'nationality',
        'phone',
        'id_number',
        'term_id',
        'grade_applying_for',
        'year',
        'application_date',
        'status',
        'last_updated_by',

    ];

    public function getFullNameAttribute(){
        return trim("{$this->first_name} {$this->last_name}");
    }

    function admissionAcademics(){
        return $this->hasOne(AdmissionAcademic::class);
    }

    function admissionMedicals(){
        return $this->hasOne(AdmissionHealthInformation::class);
    }

    public function seniorAdmissionAcademic() {
        return $this->hasOne(SeniorAdmissionAcademic::class);
    }


    function terms() {
        return $this->hasMany(Term::class);
    }

    public function term(){
        return $this->belongsTo(Term::class, 'term_id');
    }

    function sponsor(){
        return $this->belongsTo(Sponsor::class,'sponsor_id');
    }

    static function lastUpdatedBy($id){
        $admission = Admission::find($id);
        if ($admission && $admission->last_updated_by) {
            $user = User::find(intval($admission->last_updated_by));
            return $user ? $user->full_name : 'Support';
        }
        return 'Support';
    }

    public function onlineAttachments(){
        return $this->hasMany(OnlineApplicationAttachment::class);
    }

    public function getFormattedIdNumberAttribute(){
        if (empty($this->id_number)) {
            return '';
        }
        
        $idNumber = preg_replace('/\s+/', '', $this->id_number);
        $length = strlen($idNumber);
        
        if ($length <= 3) {
            return $idNumber;
        }
        
        $groups = [];
        $remainder = $length % 3;
        
        if ($remainder > 0) {
            $groups[] = substr($idNumber, 0, $remainder);
            $idNumber = substr($idNumber, $remainder);
        }
        
        $groups = array_merge($groups, str_split($idNumber, 3));
        return implode(' ', $groups);
    }

    public function getFormattedDateOfBirthAttribute(): string {
        if (empty($this->date_of_birth)) {
            return '';
        }
        return \Carbon\Carbon::parse($this->date_of_birth)->format('d/m/Y');
    }

    public function getFormattedPhoneAttribute(){
        if (empty($this->phone)) {
            return '';
        }
        
        $phone = preg_replace('/^00267/', '', $this->phone);
        $phone = preg_replace('/\s+/', '', $phone);
    
        if (strlen($phone) === 8) {
            return substr($phone, 0, 2) . ' ' . 
                   substr($phone, 2, 3) . ' ' . 
                   substr($phone, 5);
        }
        return $phone;
    }
}
