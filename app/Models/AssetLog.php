<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'action',
        'description',
        'changes',
        'performed_by',
    ];

    protected $casts = [
        'changes' => 'json',
    ];

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function performedByUser(){
        return $this->belongsTo(User::class, 'performed_by');
    }

    public static function createLog($assetId, $action, $description, $changes = null, $performedBy = null){
        return self::create([
            'asset_id' => $assetId,
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }
}
