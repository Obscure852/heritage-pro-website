<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAudit extends Model{
    use HasFactory;

    protected $fillable = [
        'audit_code',
        'audit_date',
        'next_audit_date',
        'status',
        'notes',
        'conducted_by',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'next_audit_date' => 'date',
    ];

    public function auditItems(){
        return $this->hasMany(AssetAuditItem::class, 'audit_id');
    }

    public function conductedByUser(){
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function isPending(){
        return $this->status === 'Pending';
    }

    public function isInProgress(){
        return $this->status === 'In Progress';
    }

    public function isCompleted(){
        return $this->status === 'Completed';
    }

    public function start(){
        $this->update(['status' => 'In Progress']);
        return $this;
    }

    public function complete(){
        $this->update(['status' => 'Completed']);
        return $this;
    }

    public function getMissingAssetsCount(){
        return $this->auditItems()->where('is_present', false)->count();
    }

    public function getMaintenanceNeededCount(){
        return $this->auditItems()->where('needs_maintenance', true)->count();
    }
}
