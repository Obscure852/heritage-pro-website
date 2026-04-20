<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AccountBalance;
use App\Services\Messaging\SmsCostCalculator;

class SendBulkSmsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('access-communications');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:480'],
            'recipientType' => ['required', 'string', 'in:sponsors,users'],

            // Optional filters for sponsors
            'grade' => ['nullable', 'integer', 'exists:grades,id'],
            'sponsorFilter' => ['nullable', 'integer', 'exists:sponsor_filters,id'],

            // Optional filters for users (department, area_of_work, position are strings from dropdown)
            'department' => ['nullable', 'string', 'max:255'],
            'area_of_work' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'integer', 'exists:user_filters,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'message.required' => 'Please enter your SMS message.',
            'message.min' => 'The SMS message cannot be empty.',
            'message.max' => 'The SMS message must not exceed 480 characters (3 SMS units).',
            'recipientType.required' => 'Please select a recipient type.',
            'recipientType.in' => 'Invalid recipient type. Must be either sponsors or users.',
            'grade.exists' => 'The selected grade does not exist.',
            'sponsorFilter.exists' => 'The selected sponsor filter does not exist.',
            'filter.exists' => 'The selected user filter does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'message' => 'SMS message',
            'recipientType' => 'recipient type',
            'sponsorFilter' => 'sponsor filter',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if there's an account balance set up
            $accountBalance = AccountBalance::first();
            if (!$accountBalance) {
                $validator->errors()->add('balance', 'No SMS package configured. Please set up an SMS package first.');
                return;
            }

            // Check if there's any balance available
            $availableBalance = $accountBalance->balance_bwp - ($accountBalance->pending_amount ?? 0);
            if ($availableBalance <= 0) {
                $validator->errors()->add('balance', 'Insufficient SMS balance. Please top up your account.');
                return;
            }

            // Get current cost per SMS unit
            $costCalculator = app(SmsCostCalculator::class);
            $costPerUnit = $costCalculator->getCostPerUnit();

            // Calculate SMS units for this message
            $message = $this->input('message', '');
            $smsUnits = $costCalculator->calculateSmsUnits($message);

            // Check if at least one SMS can be sent
            $minCost = $smsUnits * $costPerUnit;
            if ($availableBalance < $minCost) {
                $validator->errors()->add('balance',
                    "Insufficient balance. This message requires at least BWP " . number_format($minCost, 2) .
                    " per recipient, but you only have BWP " . number_format($availableBalance, 2) . " available."
                );
            }
        });
    }
}
