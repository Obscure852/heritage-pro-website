<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCalendarEvent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'calendar_id',
        'owner_id',
        'created_by_id',
        'updated_by_id',
        'lead_id',
        'customer_id',
        'contact_id',
        'request_id',
        'title',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'all_day',
        'status',
        'visibility',
        'timezone',
        'reminders',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'all_day' => 'boolean',
        'reminders' => 'array',
        'metadata' => 'array',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(CrmCalendar::class, 'calendar_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CrmRequest::class, 'request_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(CrmCalendarEventAttendee::class, 'event_id');
    }
}
