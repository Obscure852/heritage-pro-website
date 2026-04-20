<?php

namespace App\Http\Requests\Fee;

use App\Models\Fee\StudentInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('collect-fees');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:student_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,mobile_money,cheque'],
            'payment_date' => ['nullable', 'date', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'cheque_number' => ['required_if:payment_method,cheque', 'nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Please select an invoice.',
            'invoice_id.exists' => 'The selected invoice does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least P 0.01.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
            'payment_date.date' => 'Please enter a valid date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'cheque_number.required_if' => 'Cheque number is required for cheque payments.',
            'cheque_number.max' => 'Cheque number cannot exceed 50 characters.',
            'reference_number.max' => 'Reference number cannot exceed 100 characters.',
            'bank_name.max' => 'Bank name cannot exceed 100 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('invoice_id') && $this->filled('amount')) {
                $invoice = StudentInvoice::find($this->input('invoice_id'));

                if ($invoice) {
                    $amount = (string) $this->input('amount');
                    $balance = (string) $invoice->balance;

                    if (bccomp($amount, $balance, 2) > 0) {
                        $validator->errors()->add(
                            'amount',
                            'Payment amount cannot exceed invoice balance (P ' . number_format($invoice->balance, 2) . ').'
                        );
                    }

                    if ($invoice->isCancelled()) {
                        $validator->errors()->add(
                            'invoice_id',
                            'Cannot record payment for a cancelled invoice.'
                        );
                    }

                    if ($invoice->isPaid()) {
                        $validator->errors()->add(
                            'invoice_id',
                            'This invoice is already fully paid.'
                        );
                    }
                }
            }
        });
    }
}
