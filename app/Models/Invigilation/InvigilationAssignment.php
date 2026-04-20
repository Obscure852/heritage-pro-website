<?php

namespace App\Models\Invigilation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvigilationAssignment extends Model
{
    use HasFactory;

    public const SOURCE_AUTO = 'auto';
    public const SOURCE_MANUAL = 'manual';

    protected $table = 'invigilation_assignments';

    protected $fillable = [
        'session_room_id',
        'user_id',
        'assignment_order',
        'assignment_source',
        'locked',
        'notes',
    ];

    protected $casts = [
        'assignment_order' => 'integer',
        'locked' => 'boolean',
    ];

    public function sessionRoom(): BelongsTo
    {
        return $this->belongsTo(InvigilationSessionRoom::class, 'session_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
