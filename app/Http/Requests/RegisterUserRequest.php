<?php

namespace App\Http\Requests;

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
            'First_Name' => 'required|string|max:255',
            'Last_Name' => 'required|string|max:255',
            'Phone_Number' => 'required|digits:10|unique:users,Phone_Number',
            'password' => 'required|string|min:8|confirmed',
            'Date_Of_Birth' => 'required|date|max:10',
            'Picture' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048',
            'Id_Card_Image' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048',
            'Role' => 'required|in:Admin,Apartment_Owner,Renter',
        ];
    }
}
