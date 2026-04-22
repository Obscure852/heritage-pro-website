<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmUserQualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'level',
        'institution',
        'start_date',
        'completion_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'completion_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CrmUserQualificationAttachment::class, 'qualification_id')->latest();
    }
}
