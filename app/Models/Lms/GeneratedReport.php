<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedReport extends Model {
    protected $table = 'lms_generated_reports';

    protected $fillable = [
        'definition_id',
        'name',
        'type',
        'parameters',
        'file_path',
        'format',
        'status',
        'error_message',
        'generated_by',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'completed_at' => 'datetime',
    ];

    public static array $formats = [
        'pdf' => 'PDF',
        'csv' => 'CSV',
        'xlsx' => 'Excel',
    ];

    public static array $statuses = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ];

    public function definition(): BelongsTo {
        return $this->belongsTo(ReportDefinition::class, 'definition_id');
    }

    public function generator(): BelongsTo {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query) {
        return $query->where('status', 'failed');
    }

    public function markProcessing(): void {
        $this->update(['status' => 'processing']);
    }

    public function markCompleted(string $filePath): void {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $error): void {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    public function getStatusColorAttribute(): string {
        return match($this->status) {
            'completed' => 'success',
            'processing' => 'info',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusIconAttribute(): string {
        return match($this->status) {
            'completed' => 'check-circle',
            'processing' => 'spinner fa-spin',
            'failed' => 'times-circle',
            default => 'clock',
        };
    }
}
