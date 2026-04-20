<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolClassRequest extends FormRequest{
    public function authorize(){
        return true;
    }

    public function rules(){
        return [
            'name' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id', // Assuming you have a users table
            'term_id' => 'required|integer|exists:terms,id', // Assuming you have a terms table
            'grade' => 'required|string|max:255',
            'year' => 'required|integer|digits:4',
        ];
    }
}
