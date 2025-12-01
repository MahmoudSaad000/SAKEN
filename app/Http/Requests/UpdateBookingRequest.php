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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'sometimes|required|in:credit,bank_transfer,cash,digital_wallet',
            'check_in_date' => 'sometimes|required|date|after:today',
            'check_out_date' => 'sometimes|required|date|after:check_in_date',
        ];
    }
}
