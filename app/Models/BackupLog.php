<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model{
    use HasFactory;

    protected $table = 'backup_logs';

    protected $fillable = [
        'database_name',
        'file_path',
        'file_size',
        'backup_time',
        'status',
    ];
}
