<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAuditItem extends Model{
    use HasFactory;

    protected $fillable = [
        'audit_id',
        'asset_id',
        'is_present',
        'condition',
        'needs_maintenance',
        'notes',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'needs_maintenance' => 'boolean',
    ];

    public function audit(){
        return $this->belongsTo(AssetAudit::class, 'audit_id');
    }

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function markAsPresent($condition = null){
        $this->update([
            'is_present' => true,
            'condition' => $condition ?? $this->asset->condition,
        ]);
        return $this;
    }

    public function markAsMissing($notes = null){
        $this->update([
            'is_present' => false,
            'notes' => $notes,
        ]);
        return $this;
    }

    public function flagForMaintenance($notes = null){
        $this->update([
            'needs_maintenance' => true,
            'notes' => $notes,
        ]);
        return $this;
    }
}
