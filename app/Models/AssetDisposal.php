<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'disposal_date',
        'disposal_method',
        'disposal_amount',
        'reason',
        'notes',
        'recipient',
        'authorized_by',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'disposal_amount' => 'decimal:2',
    ];

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function authorizedByUser(){
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function wasSold(){
        return $this->disposal_method === 'Sold';
    }

    public function wasDonated(){
        return $this->disposal_method === 'Donated';
    }

    public function wasScrapped(){
        return $this->disposal_method === 'Scrapped';
    }

    public function wasRecycled(){
        return $this->disposal_method === 'Recycled';
    }
}
