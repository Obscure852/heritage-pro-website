<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest{

    public function authorize(){
        return false;
    }

    public function rules(){
        return [
            'business_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255|unique:users,email',
            'business_phone' => 'required|string|max:30',
            'business_other_phone' => 'nullable|string|max:30',
            'business_mobile' => 'nullable|string|max:30',
            'business_fax' => 'nullable|string|max:30',
            'business_website' => 'nullable|url|max:255',
            'business_facebook_page' => 'nullable|url|max:255',
            'mailing_address' => 'nullable|string|max:500',
            'location_address' => 'nullable|string|max:500',
            'contact_firstname' => 'required|string|max:255',
            'contact_lastname' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_fax' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'secondary_email' => 'nullable|email|max:255',
            'skype_handle' => 'nullable|string|max:255',
            'twitter_handle' => 'nullable|string|max:255',
            'facebook_handle' => 'nullable|string|max:255',
            'business_description' => 'nullable|string|max:1000',
        ];
        
    }
}
