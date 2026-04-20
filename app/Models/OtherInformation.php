<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class OtherInformation extends Model{
    protected $table = 'sponsors_other_information';

    protected $fillable = [
        'sponsor_id',
        'address',
        'family_situation',
        'issues_to_note',
    ];

    public function sponsor(){
        return $this->belongsTo(Sponsor::class);
    }
}
