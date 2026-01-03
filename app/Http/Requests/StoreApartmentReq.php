<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApartmentReq extends FormRequest
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
            'area' => 'integer|required|min:5',
            'rooms' => 'integer|min:1|required',
            'living_rooms' => 'integer|min:1|required',
            'bathrooms' => 'integer|min:1|required',
            'rental_price' => 'integer|min:1|required',
            'address' => 'required|string|max:50',
            'city_id' => 'required|integer|exists:cities,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'average_rate' => 'nullable',
        ];
    }
}
