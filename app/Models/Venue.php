<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'capacity'
    ];


    public function klassSubjects(){
        return $this->hasMany(KlassSubject::class, 'venue_id');
    }

    public function optionalSubjects(){
        return $this->hasMany(OptionalSubject::class, 'venue_id');
    }

    public function getUtilizationPercentageAttribute(){
        if (!$this->capacity) {
            return 0;
        }

        $currentStudents = $this->optionalSubjects()
            ->where('active', 1)
            ->withCount('students')
            ->get()
            ->max('students_count');

        return round(($currentStudents / $this->capacity) * 100, 1);
    }

    public function getIsOverCapacityAttribute(){
        return $this->utilization_percentage > 100;
    }

    public function assets(){
        return $this->hasMany(Asset::class, 'venue_id');
    }
}
