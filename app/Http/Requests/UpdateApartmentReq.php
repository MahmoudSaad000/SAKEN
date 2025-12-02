<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApartmentReq extends FormRequest
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
            'description' => 'string|nullable',
            'area' => 'integer|min:5',
            'rooms' => 'integer|min:1',
            'living_rooms' => 'integer|min:1',
            'bathrooms' => 'integer|min:1',
            'rental_price' => 'integer|min:1',
            'address' => 'string|max:50',
            'status' => 'in:Booked,Free',
            'city_id' => 'integer|exists:cities,id',
            'images' => 'array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'average_rate' => 'nullable',
        ];
    }
}
