<?php

namespace App\Models\Schemes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardSchemeWorkflowAudit extends Model {
    public $timestamps = false;

    protected $table = 'standard_scheme_workflow_audits';

    protected $fillable = [
        'standard_scheme_id',
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
    const ACTION_SUBMITTED           = 'submitted';
    const ACTION_PLACED_UNDER_REVIEW = 'placed_under_review';
    const ACTION_APPROVED            = 'approved';
    const ACTION_REVISION_REQUIRED   = 'revision_required';
    const ACTION_PUBLISHED           = 'published';
    const ACTION_UNPUBLISHED         = 'unpublished';
    const ACTION_DISTRIBUTED         = 'distributed';

    public function standardScheme(): BelongsTo {
        return $this->belongsTo(StandardScheme::class, 'standard_scheme_id');
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Create an immutable audit log record for a workflow transition.
     */
    public static function log(
        StandardScheme $scheme,
        User $actor,
        string $action,
        string $fromStatus,
        string $toStatus,
        ?string $comments = null
    ): self {
        return self::create([
            'standard_scheme_id' => $scheme->id,
            'actor_id'           => $actor->id,
            'action'             => $action,
            'from_status'        => $fromStatus,
            'to_status'          => $toStatus,
            'comments'           => $comments,
            'created_at'         => now(),
        ]);
    }
}
