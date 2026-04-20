<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest{

    public function authorize(){
        return true;
    }

    public function rules(){
        return [
            'code' => 'required|string|max:255',
            'product' => 'required|string|max:255',
            'vendor' => 'required|string|max:255',
            'price' => 'required|numeric|max:999999.99',
            'active' => 'required|boolean',
        ];
    }
    
}
