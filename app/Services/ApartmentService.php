<?php

namespace App\Services;

namespace App\Services;

use App\Models\Apartment;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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

    public function doesApartmentBelongToUser() {}

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

    public function checkUserAuthrization($apartment)
    {
        if (Auth::user()->id !== $apartment->user_id) {
            throw new AuthorizationException;
        }
    }


    public function getUnconfirmedModifiedBookings($apartment)
    {
        return $apartment->bookings()
            ->whereIn('booking_status', ['pending', 'modified'])
            ->get();
    }

     public function getApartmentRating($apartment_id)
{
    try {
       
        $apartment = Apartment::with(['bookings' => function($query) {
            $query->whereNotNull('rate')  
                  ->where('rate', '>', 0) 
                  ->select('id', 'apartment_id', 'rate');
        }])->findOrFail($apartment_id);
        
       
        if ($apartment->bookings->isEmpty()) {
            return response()->json([
                'message' => 'There is no rates yet',
                'average_rating' => 0,
            ], 200);
        }
        
       
        $totalRatings = $apartment->bookings->count();
        $sumRatings = $apartment->bookings->sum('rate');
        $averageRating = round($sumRatings / $totalRatings, 2);
        
        return $averageRating;
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Apartment Not Found',
            'error' => $e->getMessage()
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Apartment Not Found',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
