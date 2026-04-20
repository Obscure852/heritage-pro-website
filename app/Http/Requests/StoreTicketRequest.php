<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest{
    public function authorize(){
        return true;
    }

    public function rules(){
        return [
            'task_owner' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'due_date' => 'required|date',
            'contact' =>'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'priority' => 'required|string|max:255',
            'task_type' => 'required|string|max:255',
            'next_action' => 'required|string|max:255',
            'next_action_date' => 'required|date',
            'collaborator' => 'nullable',
            'send_email' => 'nullable',
            'ticket_description' => 'nullable',
        ];
    }
}
