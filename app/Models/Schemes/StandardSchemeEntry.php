<?php

namespace App\Models\Schemes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandardSchemeEntry extends Model {
    use SoftDeletes;

    protected $table = 'standard_scheme_entries';

    protected $fillable = [
        'standard_scheme_id',
        'week_number',
        'syllabus_topic_id',
        'topic',
        'sub_topic',
        'learning_objectives',
        'status',
    ];

    public function standardScheme(): BelongsTo {
        return $this->belongsTo(StandardScheme::class, 'standard_scheme_id');
    }

    public function syllabusTopic(): BelongsTo {
        return $this->belongsTo(SyllabusTopic::class, 'syllabus_topic_id');
    }

    public function objectives(): BelongsToMany {
        return $this->belongsToMany(
            SyllabusObjective::class,
            'standard_scheme_entry_objectives',
            'standard_scheme_entry_id',
            'syllabus_objective_id'
        );
    }
}
