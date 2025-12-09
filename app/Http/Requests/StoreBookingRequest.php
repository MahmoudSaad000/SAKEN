<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:credit,bank_transfer,cash,digital_wallet',
            'check_in_date' => 'required|date|after:today|before:2030-01-01',
            'check_out_date' => 'required|date|after:check_in_date|before:2030-01-01',
            'apartment_id' => 'required|integer|exists:apartments,id',
        ];
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
