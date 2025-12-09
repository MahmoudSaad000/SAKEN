<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'sometimes|required|in:credit,bank_transfer,cash,digital_wallet',

            // Dates are validated only if provided
            'check_in_date' => ['sometimes', 'date', 'after:today', 'before:2030-01-01'],
            'check_out_date' => ['sometimes', 'date', 'after:check_in_date', 'before:2030-01-01'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $checkIn = $this->input('check_in_date');
            $checkOut = $this->input('check_out_date');

            if (($checkIn && ! $checkOut) || (! $checkIn && $checkOut)) {
                $validator->errors()->add('check_in_date', 'Both check-in and check-out dates must be provided to change the dates.');
                $validator->errors()->add('check_out_date', 'Both check-in and check-out dates must be provided to change the dates.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'The payment method is invalid. Options: credit, bank_transfer, cash, digital_wallet.',
            'check_in_date.after' => 'Check-in date must be after today.',
            'check_out_date.after' => 'Check-out date must be after the check-in date.',
            'check_in_date.before' => 'Check-in date must be before January 1, 2030.',
            'check_out_date.before' => 'Check-out date must be before January 1, 2030.',
        ];
    }
}
