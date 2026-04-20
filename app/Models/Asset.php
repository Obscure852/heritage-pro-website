<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'asset_code',
        'category_id',
        'contact_id',
        'vendor_id',
        'venue_id',
        'status',
        'purchase_price',
        'purchase_date',
        'warranty_expiry',
        'specifications',
        'notes',
        'make',
        'model',
        'expected_lifespan',
        'current_value',
        'condition',
        'invoice_number',
        'image_path',
        'custom_fields',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'custom_fields' => 'json',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    public function category(){
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function contact(){
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function vendor(){
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function venue(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function assignments(){
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function isDisposed(){
        return $this->status === 'Disposed';
    }

    public function currentAssignment(){
        return $this->hasOne(AssetAssignment::class, 'asset_id')
            ->whereNull('actual_return_date')
            ->where('status', 'Assigned')
            ->latest();
    }

    public function maintenances(){
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    public function disposal(){
        return $this->hasOne(AssetDisposal::class, 'asset_id');
    }

    public function auditItems(){
        return $this->hasMany(AssetAuditItem::class, 'asset_id');
    }

    public function images(){
        return $this->hasMany(AssetImage::class, 'asset_id');
    }

    public function documents(){
        return $this->hasMany(AssetDocument::class, 'asset_id');
    }

    public function logs(){
        return $this->hasMany(AssetLog::class, 'asset_id');
    }


    public function isAvailable(){
        return $this->status === 'Available';
    }


    public function isAssigned(){
        return $this->status === 'Assigned';
    }


    public function isInMaintenance(){
        return $this->status === 'In Maintenance';
    }
}
