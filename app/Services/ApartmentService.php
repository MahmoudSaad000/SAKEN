<?php

namespace App\Services;

use App\Models\Apartment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ApartmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function findApartment($apartment_id)
    {
        return Apartment::findOrFail($apartment_id);
    }
    public function doesApartmentBelongToUser($apartment){
        return (Auth::user()->id == $apartment->user_id);
    }
}
