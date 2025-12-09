<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterReq extends FormRequest
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
     'governorate_id' => 'nullable|exists:governorates,id',
    'city_id' => 'nullable|exists:cities,id',
    'min_price' => 'nullable|numeric',
    'max_price' => 'nullable|numeric',
    'min_area' => 'nullable|numeric',
    'max_area' => 'nullable|numeric',
    'rooms' => 'nullable|integer|min:0',
        ];
    }
}
