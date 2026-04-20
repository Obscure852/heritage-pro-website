<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionAcademic extends Model{
    use HasFactory,SoftDeletes;

    protected $fillable =['admission_id','science','mathematics','english'];


    function admission(){
        return $this->belongsTo(Admission::class);
    }
}
