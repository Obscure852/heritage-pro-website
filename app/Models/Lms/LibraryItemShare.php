<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LibraryItemShare extends Model {
    protected $table = 'lms_library_item_shares';

    protected $fillable = [
        'item_id',
        'shareable_type',
        'shareable_id',
        'permission',
    ];

    public static array $permissions = [
        'view' => 'View Only',
        'edit' => 'Edit',
        'manage' => 'Manage (Full Access)',
    ];

    public function item(): BelongsTo {
        return $this->belongsTo(LibraryItem::class, 'item_id');
    }

    public function shareable(): MorphTo {
        return $this->morphTo();
    }
}
