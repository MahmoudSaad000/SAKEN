<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone_number' => 'required|regex:/^09\d{8}$/|unique:users,Phone_Number',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => [
                'required',
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
            'picture' => 'required|image|mimes:png,jpg,jpeg,gif',
            'id_card_image' => 'required|image|mimes:png,jpg,jpeg,gif',
            'role' => 'required|in:apartment_owner,renter',
        ];
    }

    public function messages(): array
{
    return [
        'firstname.required' => 'The first name field is required.',
        'firstname.string'   => 'The first name must be a string.',
        'firstname.max'      => 'The first name must not exceed 255 characters.',

        'lastname.required'  => 'The last name field is required.',
        'lastname.string'    => 'The last name must be a string.',
        'lastname.max'       => 'The last name must not exceed 255 characters.',

        'phone_number.required' => 'The phone number field is required.',
        'phone_number.regex'    => 'The phone number must start with 09 and be 10 digits long.',
        'phone_number.unique'   => 'This phone number is already registered.',

        'password.required'  => 'The password field is required.',
        'password.string'    => 'The password must be a string.',
        'password.min'       => 'The password must be at least 8 characters.',
        'password.confirmed' => 'The password confirmation does not match.',

        'date_of_birth.required' => 'The date of birth field is required.',
        'date_of_birth.date'     => 'The date of birth must be a valid date.',

        'picture.required' => 'The profile picture is required.',
        'picture.image'    => 'The profile picture must be an image.',
        'picture.mimes'    => 'The profile picture must be of type: png, jpg, jpeg, gif.',

        'id_card_image.required' => 'The ID card image is required.',
        'id_card_image.image'    => 'The ID card image must be an image.',
        'id_card_image.mimes'    => 'The ID card image must be of type: png, jpg, jpeg, gif.',

        'role.required' => 'The role field is required.',
        'role.in'       => 'The role must be either apartment_owner or renter.',
    ];
}









}
