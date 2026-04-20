<?php

namespace App\Models\Schemes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyllabusTopic extends Model {
    use HasFactory;

    protected $table = 'syllabus_topics';

    protected $fillable = [
        'syllabus_id',
        'sequence',
        'name',
        'description',
        'suggested_weeks',
    ];

    public function syllabus(): BelongsTo {
        return $this->belongsTo(Syllabus::class, 'syllabus_id');
    }

    public function objectives(): HasMany {
        return $this->hasMany(SyllabusObjective::class, 'syllabus_topic_id')->orderBy('sequence', 'asc');
    }
}
