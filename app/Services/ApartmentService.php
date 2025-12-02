<?php

namespace App\Services;

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
    public function doesApartmentBelongToUser()
    {
        
    }

    public function findApartment($apartment_id)
    {

        try {
            return Apartment::findOrFail($apartment_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Apartment Not Found',
                'details' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Something Went Wrong',
                'details' => $e->getMessage(),
            ], 404);
        }
    }
}
