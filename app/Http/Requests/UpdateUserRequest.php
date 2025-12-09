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
            'picture' => 'sometimes|image|mimes:png,jpg,jpeg,gif',
            'id_card_image' => 'sometimes|image|mimes:png,jpg,jpeg,gif',
            'role' => 'sometimes|apartment_owner,renter',
        ];
    }

    public function messages(): array
    {
        return [

            'firstname.string' => 'The first name must be a string.',
            'firstname.max' => 'The first name must not exceed 255 characters.',

            'lastname.string' => 'The last name must be a string.',
            'lastname.max' => 'The last name must not exceed 255 characters.',

            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',

            'date_of_birth.date' => 'The date of birth must be a valid date.',

            'picture.image' => 'The profile picture must be an image.',
            'picture.mimes' => 'The profile picture must be of type: png, jpg, jpeg, gif.',

            'id_card_image.image' => 'The ID card image must be an image.',
            'id_card_image.mimes' => 'The ID card image must be of type: png, jpg, jpeg, gif.',

            'role.in' => 'The role must be either apartment_owner or renter.',
        ];
    }
}
