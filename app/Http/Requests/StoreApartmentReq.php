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
           'description'=>'string|nullable',
           'area'=>'smallInteger|required|min:5',
           'rooms'=>'tinyInteger|min:1|required',
           'living_rooms'=>'tinyInteger|min:1|required',
           'bathrooms'=>'tinyInteger|min:1|required',
           'rental_price'=>'Integer|min:1|required',
           'address'=>'required|string|max:50',
           'status'=>'required|in:Booked,Free',
           'user_id'=>'required|integer'
           
        ];
    }
}
