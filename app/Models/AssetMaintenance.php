<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'maintenance_date',
        'next_maintenance_date',
        'contact_id',
        'vendor_id',
        'cost',
        'description',
        'status',
        'results',
        'performed_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function contact(){
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function vendor(){
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function performedByUser(){
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function isScheduled(){
        return $this->status === 'Scheduled';
    }

    public function isInProgress(){
        return $this->status === 'In Progress';
    }

    public function isCompleted(){
        return $this->status === 'Completed';
    }

    public function markAsCompleted($results = null, $performedBy = null){
        $this->update([
            'status' => 'Completed',
            'results' => $results,
            'performed_by' => $performedBy,
        ]);
        
        return $this;
    }
}
