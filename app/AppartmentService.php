<?php

namespace App;

use App\Models\Apartment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppartmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function findAppartment($appartment_id)
    {

        try {
            return Apartment::findOrFail($appartment_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "Appartment Not Found",
                'details' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Something Went Wrong",
                'details' => $e->getMessage()
            ], 404);
        }
    }
}
