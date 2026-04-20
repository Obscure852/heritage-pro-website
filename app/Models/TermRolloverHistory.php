<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermRolloverHistory extends Model{

    protected $table = 'term_rollover_histories';
    
    protected $fillable = [
        'from_term_id',
        'to_term_id',
        'performed_by',
        'mappings',
        'status',
        'reversed_at'
    ];

    protected $casts = [
        'mappings' => 'array',
        'reversed_at' => 'datetime'
    ];

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
