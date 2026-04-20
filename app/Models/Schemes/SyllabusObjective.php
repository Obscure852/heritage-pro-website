<?php

namespace App\Models\Schemes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SyllabusObjective extends Model {
    use HasFactory;

    protected $table = 'syllabus_objectives';

    protected $fillable = [
        'syllabus_topic_id',
        'sequence',
        'code',
        'objective_text',
        'cognitive_level',
    ];

    public function topic(): BelongsTo {
        return $this->belongsTo(SyllabusTopic::class, 'syllabus_topic_id');
    }

    public function schemeEntries(): BelongsToMany {
        return $this->belongsToMany(
            SchemeOfWorkEntry::class,
            'scheme_entry_objectives',
            'syllabus_objective_id',
            'scheme_of_work_entry_id'
        );
    }
}
