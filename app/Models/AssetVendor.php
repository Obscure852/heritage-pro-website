<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetVendor extends Model{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    public function assets(){
        return $this->hasMany(Asset::class, 'vendor_id');
    }


    public function maintenances(){
        return $this->hasMany(AssetMaintenance::class, 'vendor_id');
    }
}