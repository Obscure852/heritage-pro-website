<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmImportEntityLock extends Model
{
    use HasFactory;

    protected $table = 'crm_import_entity_locks';
    protected $primaryKey = 'entity';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'entity',
    ];
}
