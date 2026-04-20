<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    use HasFactory;

    public const TYPE_CALENDAR_EVENT = 'calendar_event';

    public static array $typeConfig = [
        'calendar_event' => ['icon' => 'fas fa-calendar-alt', 'color' => '#3b82f6'],
    ];

    protected $fillable = [
        'term_id',
        'user_id',
        'title',
        'body',
        'is_general',
        'department_id',
        'filter_id',
        'area_of_work',
        'allow_comments',
        'is_pinned',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function attachments(){
        return $this->hasMany(NotificationAttachment::class);
    }

    public function recipients(){
        return $this->belongsToMany(User::class, 'notification_user')->withTimestamps();
    }


    public function sponsorRecipients(){
        return $this->belongsToMany(Sponsor::class, 'notification_sponsor')->withTimestamps();
    }

    public function notificationComments(){
        return $this->hasMany(NotificationComment::class);
    }

    public function sponsorComments(){
        return $this->hasMany(NotificationSponsorComment::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function scopePinned($query) {
        return $query->where('is_pinned', true);
    }

    public function scopeActive($query) {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
        });
    }
    
    public function scopeForUser($query, $user) {
        if ($user->roles->contains('name', 'Administrator')) {
            return $query;
        }
    
        return $query->where(function($q) use ($user) {
            $q->where('is_general', true)
              ->orWhere('department_id', $user->department_id)
              ->orWhere('area_of_work', $user->area_of_work)
              ->orWhereHas('recipients', function($q) use ($user) {
                  $q->where('user_id', $user->id);
              });
        });
    }

    public function getFileIcon($fileType){
        return match (true) {
            str_contains($fileType, 'pdf') => '<i class="bx bxs-file-pdf"></i>',
            str_contains($fileType, 'word') || str_contains($fileType, 'docx') => '<i class="bx bxs-file-doc"></i>',
            str_contains($fileType, 'excel') || str_contains($fileType, 'spreadsheet') => '<i class="bx bxs-file-xlsx"></i>',
            str_contains($fileType, 'powerpoint') || str_contains($fileType, 'presentation') => '<i class="bx bxs-file-ppt"></i>',
            
            str_contains($fileType, 'image') => '<i class="bx bxs-file-image"></i>',
            str_contains($fileType, 'zip') || str_contains($fileType, 'rar') || str_contains($fileType, 'archive') 
                => '<i class="bx bxs-file-archive"></i>',
            
            str_contains($fileType, 'text') => '<i class="bx bxs-file-txt"></i>',
            default => '<i class="bx bxs-file"></i>',
        };
    }

public function getFileColorClass($fileType){
    return match (true) {
        str_contains($fileType, 'pdf') => 'bg-danger',
        str_contains($fileType, 'word') || str_contains($fileType, 'docx') => 'bg-info',
        str_contains($fileType, 'excel') || str_contains($fileType, 'spreadsheet') => 'bg-success',
        str_contains($fileType, 'powerpoint') || str_contains($fileType, 'presentation') => 'bg-warning',
        str_contains($fileType, 'image') => 'bg-primary',
        str_contains($fileType, 'zip') || str_contains($fileType, 'rar') || str_contains($fileType, 'archive') 
            => 'bg-secondary',
        default => 'bg-dark',
    };
}

public function formatFileSize($size){
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}
    
}
