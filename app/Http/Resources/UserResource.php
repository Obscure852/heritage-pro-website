<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource{
    
    public function toArray($request){
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'department' => $this->department,
            'position' => $this->position,
            'area_of_work' => $this->area_of_work,
            'nationality' => $this->nationality,
            'phone' => $this->formatted_phone,
            'id_number' => $this->formatted_id_number,
            'city' => $this->city,
            'address' => $this->address,
            'status' => $this->status,
            'username' => $this->username,
            'year' => $this->year,

            'roles' => $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name
                ];
            }),

            'logs' => $this->logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'ip_address' => $log->ip_address,
                    'action' => $log->method,
                    'changes' => $log->changes,
                    'timestamp' => $log->created_at,
                ];
            }),

            'qualifications' => $this->qualifications->map(function ($qualification) {
                return [
                    'id' => $qualification->id,
                    'name' => $qualification->qualification,
                    'level' => $qualification->pivot->level,
                    'college' => $qualification->pivot->college,
                    'start_date' => $qualification->pivot->start_date,
                    'completion_date' => $qualification->pivot->completion_date,
                ];
            }),

            'work_history' => $this->workHistory->map(function ($work) {
                return [
                    'id' => $work->id,
                    'workplace' => $work->workplace,
                    'role' => $work->role,
                    'start_date' => $work->start,
                    'end_date' => $work->end,
                ];
            }),
        ];
    }
}
