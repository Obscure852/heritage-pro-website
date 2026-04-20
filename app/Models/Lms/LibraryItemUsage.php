<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LibraryItemUsage extends Model {
    protected $table = 'lms_library_item_usages';

    protected $fillable = [
        'item_id',
        'usable_type',
        'usable_id',
        'used_by',
    ];

    public function item(): BelongsTo {
        return $this->belongsTo(LibraryItem::class, 'item_id');
    }

    public function usable(): MorphTo {
        return $this->morphTo();
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'used_by');
    }
}
