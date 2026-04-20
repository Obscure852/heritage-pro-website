<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDocument extends Model{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'document_path',
        'document_type',
        'title',
        'description',
    ];

    public function asset(){
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function getDocumentUrlAttribute(){
        return asset('storage/' . $this->document_path);
    }

    public function isManual(){
        return $this->document_type === 'Manual';
    }

    public function isCertificate(){
        return $this->document_type === 'Certificate';
    }

    public function isInvoice(){
        return $this->document_type === 'Invoice';
    }
}
