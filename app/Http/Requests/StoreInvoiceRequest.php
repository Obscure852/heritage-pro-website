<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest{
    public function authorize(){
        return true;
    }
    public function rules()
    {
        return [
            // 'invoice_owner' => 'required|string|max:255',
            // 'order_name' => 'required|string|max:255',
            // 'invoice_status' => 'required|string|max:255',
            // 'account_name' => 'required|string|max:255',
            // 'contact_name' => 'required|string|max:255',
            // 'order_number' => [
            //     'required',
            //     'integer',
            // ],
        ];
    }
    
}
