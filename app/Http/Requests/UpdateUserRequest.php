<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            // 'phone_number' => 'sometimes|digits:10|unique:users,Phone_Number',
            'password' => 'sometimes|string|min:8|confirmed',
            'date_of_birth' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;

                    if ($age < 18) {
                        $fail('You must be at least 18 years old.');
                    }

                    if ($age > 100) {
                        $fail('Please enter a valid date of birth.');
                    }
                },
            ],
            'picture' => 'sometimes|image|mimes:png,jpg,jpeg,gif|max:2048',
            'id_card_image' => 'sometimes|image|mimes:png,jpg,jpeg,gif|max:2048',
            'role' => 'sometimes|apartment_owner,renter',
        ];
    }
}
