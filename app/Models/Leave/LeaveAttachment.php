<?php

namespace App\Models\Leave;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Leave attachment model.
 *
 * Stores file attachments for leave requests (medical certificates, etc.).
 *
 * @property int $id
 * @property int $leave_request_id
 * @property string $file_name
 * @property string $file_path
 * @property int $file_size
 * @property string $mime_type
 * @property int $uploaded_by
 * @property \Carbon\Carbon $created_at
 * @property-read string $url
 */
class LeaveAttachment extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'leave_request_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function (self $model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function leaveRequest() {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function uploadedBy() {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ==================== COMPUTED ATTRIBUTES ====================

    /**
     * Get the public URL for the attachment.
     */
    public function getUrlAttribute(): string {
        return Storage::url($this->file_path);
    }
}
