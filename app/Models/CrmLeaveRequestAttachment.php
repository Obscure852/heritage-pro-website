<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmLeaveRequestAttachment extends Model
{
    protected $fillable = [
        'leave_request_id',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(CrmLeaveRequest::class, 'leave_request_id');
    }
}
