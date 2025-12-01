<?php

namespace App\Services;

use App\Models\Apartment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
}
