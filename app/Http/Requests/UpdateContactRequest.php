<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest{

    public function authorize(){
        return true;
    }

    public function rules(){
        return [
            'business_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'contact_firstname' => 'required|string|max:255',
            'contact_lastname' => 'required|string|max:255',
            'business_email' => 'required|email|max:255|unique:contacts,business_email',
            'business_phone' => 'nullable',
            'business_other_phone' => 'nullable',
            'business_mobile' => 'nullable',
            'business_fax' => 'nullable',
            'business_website' => 'nullable',
            'business_facebook_page' => 'nullable',
            'mailing_address' => 'nullable',
            'location_address' => 'nullable',
            'contact_title' => 'nullable',
            'department' => 'nullable',
            'job_title' => 'nullable',
            'contact_phone' => 'nullable',
            'contact_email' => 'nullable',
            'skype_handle' => 'nullable',
            'twitter_handle' => 'nullable',
            'business_description' => 'nullable',
        ];
        
    }
}
