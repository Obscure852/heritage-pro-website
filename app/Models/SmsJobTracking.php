<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsJobTracking extends Model{
    use HasFactory;
    
    protected $table = 'sms_job_tracking';
    
    protected $fillable = [
        'job_id',
        'user_id',
        'term_id',
        'status',
        'recipient_type',
        'message',
        'filters',
        'total_recipients',
        'sent_count',
        'failed_count',
        'percentage',
        'total_cost',
        'sms_units_used',
        'status_message',
        'errors',
        'started_at',
        'completed_at',
        'cancelled_at'
    ];
    
    protected $casts = [
        'filters' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function term(){
        return $this->belongsTo(Term::class);
    }
    
    public function calculateCost(){
        $costPerSms = app(\App\Services\Messaging\SmsCostCalculator::class)->getCostPerUnit();
        return $this->sms_units_used * $costPerSms;
    }

    public function updateProgress($sent, $failed){
        $this->sent_count = $sent;
        $this->failed_count = $failed;
        $this->percentage = $this->total_recipients > 0 
            ? round((($sent + $failed) / $this->total_recipients) * 100) 
            : 0;
        
        if (($sent + $failed) >= $this->total_recipients) {
            $this->status = 'completed';
            $this->completed_at = now();
            $this->status_message = "Completed: {$sent} sent, {$failed} failed";
        } else {
            $this->status = 'processing';
            $this->status_message = "Processing: {$sent}/{$this->total_recipients} sent";
        }
        
        $this->save();
    }
    
    public function cancel(){
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->status_message = 'Job cancelled by user';
        $this->save();
    }
    
    public function markAsFailed($error = null){
        $this->status = 'failed';
        $this->completed_at = now();
        $this->status_message = 'Job failed';
        
        if ($error) {
            $errors = $this->errors ?? [];
            $errors[] = $error;
            $this->errors = $errors;
        }
        
        $this->save();
    }
    
    public function scopeActive($query){
        return $query->whereIn('status', ['pending', 'processing']);
    }
    
    public function scopeForUser($query, $userId){
        return $query->where('user_id', $userId);
    }
}
