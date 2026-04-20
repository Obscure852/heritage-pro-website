<?php

namespace App\Models\Invigilation;

use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvigilationSessionRoom extends Model
{
    use HasFactory;

    public const SOURCE_KLASS_SUBJECT = 'klass_subject';
    public const SOURCE_OPTIONAL_SUBJECT = 'optional_subject';
    public const SOURCE_MANUAL = 'manual';

    protected $table = 'invigilation_session_rooms';

    protected $fillable = [
        'session_id',
        'venue_id',
        'source_type',
        'klass_subject_id',
        'optional_subject_id',
        'group_label',
        'candidate_count',
        'required_invigilators',
    ];

    protected $casts = [
        'candidate_count' => 'integer',
        'required_invigilators' => 'integer',
    ];

    public static function sourceTypes(): array
    {
        return [
            self::SOURCE_KLASS_SUBJECT => 'Class Subject',
            self::SOURCE_OPTIONAL_SUBJECT => 'Optional Subject',
            self::SOURCE_MANUAL => 'Manual / Mixed Room',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InvigilationSession::class, 'session_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function klassSubject(): BelongsTo
    {
        return $this->belongsTo(KlassSubject::class, 'klass_subject_id');
    }

    public function optionalSubject(): BelongsTo
    {
        return $this->belongsTo(OptionalSubject::class, 'optional_subject_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(InvigilationAssignment::class, 'session_room_id')->orderBy('assignment_order');
    }

    public function getResolvedGroupLabelAttribute(): string
    {
        if ($this->group_label) {
            return $this->group_label;
        }

        if ($this->source_type === self::SOURCE_KLASS_SUBJECT) {
            return $this->klassSubject?->klass?->name ?? 'Class Group';
        }

        if ($this->source_type === self::SOURCE_OPTIONAL_SUBJECT) {
            return $this->optionalSubject?->name ?? 'Optional Group';
        }

        return 'Manual Group';
    }
}
