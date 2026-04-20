<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetImage extends Model{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'image_path',
        'title',
        'description',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function setAsPrimary(){
        self::where('asset_id', $this->asset_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        $this->update(['is_primary' => true]);
        
        return $this;
    }

    public function getImageUrlAttribute(){
        return asset('storage/' . $this->image_path);
    }
}
