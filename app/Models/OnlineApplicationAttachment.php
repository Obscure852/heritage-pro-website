<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineApplicationAttachment extends Model{
    use HasFactory;
    protected $fillable = [
        'admission_id',
        'attachment_type',
        'file_path',
    ];
}
