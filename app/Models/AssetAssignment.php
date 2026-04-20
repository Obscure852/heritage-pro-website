<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model{
    use HasFactory;
    protected $fillable = [
        'asset_id',
        'assignable_id',
        'assignable_type',
        'assigned_date',
        'expected_return_date',
        'actual_return_date',
        'status',
        'assignment_notes',
        'return_notes',
        'condition_on_assignment',
        'condition_on_return',
        'assigned_by',
        'received_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assignable(){
        return $this->morphTo();
    }

    public function assignedByUser(){
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function receivedByUser(){
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isActive(){
        return $this->status === 'Assigned' && is_null($this->actual_return_date);
    }

    public function isOverdue(){
        if (is_null($this->expected_return_date) || !is_null($this->actual_return_date)) {
            return false;
        }
        return $this->expected_return_date < now() && $this->status !== 'Returned';
    }

    public function markAsReturned($condition, $notes = null, $receivedBy = null){
        $this->update([
            'actual_return_date' => now(),
            'status' => 'Returned',
            'condition_on_return' => $condition,
            'return_notes' => $notes,
            'received_by' => $receivedBy,
        ]);

        $this->asset->update(['status' => 'Available']);
        return $this;
    }
}
