<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolloverHistory extends Model{
    protected $fillable = [
        'from_term_id',
        'to_term_id',
        'status',
        'rollover_timestamp',
        'reversed_timestamp',
        'performed_by',
        'metadata'
    ];

    protected $casts = [
        'rollover_timestamp' => 'datetime',
        'reversed_timestamp' => 'datetime',
        'metadata' => 'array'
    ];

    const STATUS_COMPLETED = 'completed';
    const STATUS_REVERSED = 'reversed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_FAILED = 'failed';

    public function fromTerm(){
        return $this->belongsTo(Term::class, 'from_term_id');
    }

    public function toTerm(){
        return $this->belongsTo(Term::class, 'to_term_id');
    }


    public function performer(){
        return $this->belongsTo(User::class, 'performed_by');
    }

}
