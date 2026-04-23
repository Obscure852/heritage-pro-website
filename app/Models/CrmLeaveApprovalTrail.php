<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmLeaveApprovalTrail extends Model
{
    public $timestamps = false;

    protected $table = 'crm_leave_approval_trail';

    protected $fillable = [
        'leave_request_id',
        'user_id',
        'action',
        'level',
        'comment',
    ];

    protected $casts = [
        'level' => 'integer',
        'created_at' => 'datetime',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(CrmLeaveRequest::class, 'leave_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
