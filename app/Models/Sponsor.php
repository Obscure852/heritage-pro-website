<?php

namespace App\Models;

use App\Notifications\SponsorResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Sponsor extends Authenticatable{
    use HasFactory,SoftDeletes, Notifiable;
    public $timestamps = true;
    
    protected $fillable = [
        'connect_id',
        'title',
        'first_name',
        'last_name',
        'email',
        'gender',
        'date_of_birth',
        'nationality',
        'relation',
        'status',
        'sponsor_filter_id',
        'id_number',
        'phone',
        'profession',
        'work_place',
        'telephone',
        'year',
        'password',
        'remember_token',
        'last_updated_by',
    ];


    protected $hidden = [
        'password', 
        'remember_token',
    ];

    public function sendPasswordResetNotification($token){
        $this->notify(new SponsorResetPasswordNotification($token));
    }

    function students(){
        return $this->hasMany(Student::class);
    }

    public function getFullNameAttribute(){
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function filter(){
        return $this->belongsTo(SponsorFilter::class);
    }

    public function otherInformation(){
        return $this->hasOne(OtherInformation::class);
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function channelConsents()
    {
        return $this->morphMany(RecipientChannelConsent::class, 'recipient');
    }

    public function notifications(){
        return $this->belongsToMany(Notification::class, 'notification_sponsor')->withTimestamps();
    }

    public function notificationComments(){
        return $this->hasMany(NotificationSponsorComment::class);
    }
    
    public function receivedEmails(){
        return $this->hasMany(Email::class, 'sponsor_id');
    }

    public function hasValidPhoneNumber() {
        $localPhoneRegex = '/^002677\d{7}$/';
        $shortPhoneRegex = '/^7\d{7}$/';
        return isset($this->phone) && (preg_match($localPhoneRegex, $this->phone) || preg_match($shortPhoneRegex, $this->phone));
    }

    public function hasValidEmail(){
        return filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false;
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

    public function getFormattedTelephoneAttribute(){
        if (empty($this->telephone)) {
            return '';
        }
        
        $phone = preg_replace('/^00267/', '', $this->telephone);
        $phone = preg_replace('/\s+/', '', $phone);
        
        if (strlen($phone) === 7) {
            return substr($phone, 0, 3) . ' ' . 
                   substr($phone, 3);
        }
        return $phone;
    }


    public function getFormattedDateOfBirthAttribute(): string {
        if (empty($this->date_of_birth)) {
            return '';
        }
        return \Carbon\Carbon::parse($this->date_of_birth)->format('d/m/Y');
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

}
