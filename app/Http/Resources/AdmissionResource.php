<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionResource extends JsonResource{
    public function toArray($request){
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'nationality' => $this->nationality,
            'phone' => $this->formatted_phone,
            'id_number' => $this->formatted_id_number,
            'grade_applying_for' => $this->grade_applying_for,
            'application_date' => $this->application_date,
            'status' => $this->status,
            'year' => $this->year,
            
            'sponsor' => $this->sponsor ? [
                'id' => $this->sponsor->id,
                'full_name' => $this->sponsor->full_name,
                'email' => $this->sponsor->email,
                'phone' => $this->sponsor->formatted_phone,
                'telephone' => $this->sponsor->formatted_telephone
            ] : null,
        ];
    }
}
