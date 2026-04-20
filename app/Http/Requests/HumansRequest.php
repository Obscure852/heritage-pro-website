<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HumansRequest extends FormRequest{

    public function authorize(){
        return true;
    }

    public function rules(){
        return [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'gender' => 'required|max:255',
            'date_of_birth' => 'required|max:255',
            'position' => 'required|max:255',
            'phone' => 'required|max:255',
            'id_number' => 'required|max:255',
            'city' => 'required|max:255',
            'address' => 'required|max:255',
        ];
    }
}
