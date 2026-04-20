<?php

namespace App\Models\Schemes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemeWorkflowAudit extends Model {
    public $timestamps = false;

    protected $table = 'scheme_workflow_audits';

    protected $fillable = [
        'scheme_of_work_id',
        'actor_id',
        'action',
        'from_status',
        'to_status',
        'comments',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Action constants
    const ACTION_SUBMITTED             = 'submitted';
    const ACTION_PLACED_UNDER_REVIEW   = 'placed_under_review';
    const ACTION_APPROVED              = 'approved';
    const ACTION_REVISION_REQUIRED     = 'revision_required';
    const ACTION_SUPERVISOR_APPROVED   = 'supervisor_approved';
    const ACTION_SUPERVISOR_RETURNED   = 'supervisor_returned';
    const ACTION_REFERENCE_PUBLISHED   = 'reference_published';
    const ACTION_REFERENCE_UNPUBLISHED = 'reference_unpublished';

    public function scheme(): BelongsTo {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_of_work_id');
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Create an immutable audit log record for a workflow transition.
     */
    public static function log(
        SchemeOfWork $scheme,
        User $actor,
        string $action,
        string $fromStatus,
        string $toStatus,
        ?string $comments = null
    ): self {
        return self::create([
            'scheme_of_work_id' => $scheme->id,
            'actor_id'          => $actor->id,
            'action'            => $action,
            'from_status'       => $fromStatus,
            'to_status'         => $toStatus,
            'comments'          => $comments,
            'created_at'        => now(),
        ]);
    }
}
