<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolloverLog extends Model{
    use HasFactory;

    protected $fillable = [
        'from_term_id',
        'to_term_id',
        'actions',
        'rollover_date',
        'is_undone',
    ];

    protected $casts = [
        'actions' => 'array',
        'rollover_date' => 'datetime',
        'is_undone' => 'boolean',
    ];

    public function fromTerm(){
        return $this->belongsTo(Term::class, 'from_term_id');
    }

    public function toTerm(){
        return $this->belongsTo(Term::class, 'to_term_id');
    }

    public function scopeNotUndone($query){
        return $query->where('is_undone', false);
    }


    public function canBeUndone(){
        return !$this->is_undone;
    }

    public function markAsUndone(){
        $this->is_undone = true;
        return $this->save();
    }

}
