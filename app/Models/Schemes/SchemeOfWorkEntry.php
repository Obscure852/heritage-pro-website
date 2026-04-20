<?php

namespace App\Models\Schemes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchemeOfWorkEntry extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'scheme_of_work_entries';

    protected $fillable = [
        'scheme_of_work_id',
        'week_number',
        'syllabus_topic_id',
        'standard_scheme_entry_id',
        'topic',
        'sub_topic',
        'learning_objectives',
        'status',
    ];

    public function scheme(): BelongsTo {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_of_work_id');
    }

    public function syllabusTopic(): BelongsTo {
        return $this->belongsTo(SyllabusTopic::class, 'syllabus_topic_id');
    }

    public function objectives(): BelongsToMany {
        return $this->belongsToMany(
            SyllabusObjective::class,
            'scheme_entry_objectives',
            'scheme_of_work_entry_id',
            'syllabus_objective_id'
        );
    }

    public function standardSchemeEntry(): BelongsTo {
        return $this->belongsTo(StandardSchemeEntry::class, 'standard_scheme_entry_id');
    }

    public function lessonPlans(): HasMany {
        return $this->hasMany(LessonPlan::class, 'scheme_of_work_entry_id');
    }
}
