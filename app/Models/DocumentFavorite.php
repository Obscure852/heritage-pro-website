<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for user document favorites (bookmarks).
 *
 * Pivot-style model for the document_favorites table with composite primary key.
 * No updated_at column — favorites are simple toggles.
 *
 * @property int $user_id
 * @property int $document_id
 * @property \Carbon\Carbon|null $created_at
 * @property-read User $user
 * @property-read Document $document
 */
class DocumentFavorite extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_favorites';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates that the model does not have an updated_at column.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'document_id',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who favorited the document.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited document.
     */
    public function document(): BelongsTo {
        return $this->belongsTo(Document::class);
    }
}
