<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmCommercialDocumentArtifact extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'quote_id',
        'invoice_id',
        'generated_by_id',
        'shared_discussion_thread_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'source_updated_at',
        'generated_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'source_updated_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CrmQuote::class, 'quote_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CrmInvoice::class, 'invoice_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_id');
    }

    public function sharedDiscussionThread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'shared_discussion_thread_id');
    }
}
