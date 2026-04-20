<?php

namespace App\Models\Library;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InventorySession extends Model {
    protected $fillable = [
        'scope_type',
        'scope_value',
        'status',
        'expected_count',
        'scanned_count',
        'discrepancy_count',
        'started_by',
        'started_at',
        'completed_by',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function items() {
        return $this->hasMany(InventoryItem::class);
    }

    public function startedByUser() {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completedByUser() {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // ==================== ACCESSORS ====================

    public function getScopeDisplayAttribute(): string {
        if ($this->scope_type === 'all') {
            return 'All Books';
        }

        $label = ucfirst($this->scope_type);

        return "{$label}: {$this->scope_value}";
    }
}
