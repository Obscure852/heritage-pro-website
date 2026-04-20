<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource{
    
    public function toArray($request){
        return [
            'id' => $this->id,
            'exam_number' => $this->exam_number,
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null,
            'nationality' => $this->nationality,
            'id_number' => $this->when(
                $request->user()?->tokenCan('students.sensitive'),
                $this->id_number,
                $this->formatted_id_number ?? '***'
            ),
            'email' => $this->email,
            'status' => $this->status,
            'year' => $this->year,
            'credit' => $this->when(
                $request->user()?->tokenCan('finance.read'),
                $this->credit
            ),
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'sponsor' => $this->when($this->relationLoaded('sponsor'), function () {
                return [
                    'id' => $this->sponsor->id,
                    'name' => $this->sponsor->full_name,
                    'email' => $this->sponsor->email,
                    'phone' => $this->sponsor->formatted_phone,
                ];
            }),
            
            'current_grade' => $this->when($this->relationLoaded('currentGrade'), function () {
                return [
                    'id' => $this->currentGrade->id ?? null,
                    'name' => $this->currentGrade->name ?? null,
                    'level' => $this->currentGrade->level ?? null,
                ];
            }),
            
            'current_class' => $this->when($this->relationLoaded('currentClassRelation'), function () {
                $class = $this->currentClassRelation->first();
                return $class ? [
                    'id' => $class->id,
                    'name' => $class->name,
                    'teacher' => $this->when($class->relationLoaded('teacher'), [
                        'id' => $class->teacher->id ?? null,
                        'name' => $class->teacher->full_name ?? null,
                    ])
                ] : null;
            }),
            
            'house' => $this->when($this->relationLoaded('houses'), function () {
                $house = $this->houses->first();
                return $house ? [
                    'id' => $house->id,
                    'name' => $house->name,
                    'color' => $house->color,
                ] : null;
            }),
            
            'student_type' => $this->when($this->relationLoaded('type'), function () {
                return [
                    'id' => $this->type->id ?? null,
                    'name' => $this->type->name ?? null,
                ];
            }),
            
            'filter' => $this->when($this->relationLoaded('filter'), function () {
                return [
                    'id' => $this->filter->id ?? null,
                    'name' => $this->filter->name ?? null,
                ];
            }),
            
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
