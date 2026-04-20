<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LibraryCollectionShare extends Model {
    protected $table = 'lms_library_collection_shares';

    protected $fillable = [
        'collection_id',
        'shareable_type',
        'shareable_id',
        'permission',
    ];

    public static array $permissions = [
        'view' => 'View Only',
        'edit' => 'Edit',
        'manage' => 'Manage (Full Access)',
    ];

    public function collection(): BelongsTo {
        return $this->belongsTo(LibraryCollection::class, 'collection_id');
    }

    public function shareable(): MorphTo {
        return $this->morphTo();
    }
}
