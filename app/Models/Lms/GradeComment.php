<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeComment extends Model {
    protected $table = 'lms_grade_comments';

    protected $fillable = [
        'grade_id',
        'user_id',
        'comment',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    // Relationships
    public function grade(): BelongsTo {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopePublic($query) {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query) {
        return $query->where('is_private', true);
    }
}
