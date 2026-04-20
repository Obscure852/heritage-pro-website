<?php

namespace App\Models\Library;

use App\Models\Copy;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model {
    public $timestamps = false;

    protected $fillable = [
        'inventory_session_id',
        'copy_id',
        'scanned_by',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function session() {
        return $this->belongsTo(InventorySession::class, 'inventory_session_id');
    }

    public function copy() {
        return $this->belongsTo(Copy::class);
    }

    public function scannedByUser() {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
