<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{

    public function index()
    {
        $user_id = Auth::user()->id;
    }

    public function store(StoreBookingRequest $request)
    {
        $data = $request->all();
        $validated = $request->validated();

        $extra = collect(array_keys($data))->diff(array_keys($validated));
        if ($extra->isNotEmpty()) {
            return response()->json([
                'error' => "You are not allowed to set these attributes: " . $extra->implode(', ')
            ], 422);
        }

        $validated['user_id'] = Auth::user()->id;

        try {
            $booking = Bookings::create($validated);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Something Went Wrong",
                'details' => $e->getMessage()
            ], 500);
        }

        return new BookingResource($booking);
    }


    public function show(Bookings $bookings)
    {
        //
    }


    public function update(UpdateBookingRequest $request, $booking_id)
    {
        //
        $user = Auth::user();
        $booking = null;
        $data = $request->all();
        $validated = $request->validated();

        $extra = collect(array_keys($data))->diff(array_keys($validated));
        if ($extra->isNotEmpty()) {
            return response()->json([
                'error' => "You are not allowed to Update these attributes: " . $extra->implode(', ')
            ], 422);
        }

        try {
            $booking = Bookings::findOrFail($booking_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "Booking Not Found",
                'details' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Something Went Wrong",
                'details' => $e->getMessage()
            ], 404);
        }

        if ($booking->user_id != $user->id) {
            return response()->json(['message' => "Unauthorized"], 403);
        }

        $booking->update($validated);
        return new BookingResource($booking);
    }


    public function destroy(Bookings $bookings)
    {
        //
    }



    public function rateBooking(HttpRequest $request, $booking_id)
    {

        $allowed = ['rate'];

        $data = $request->all();

        $user = Auth::user();
        $booking = null;

        $validator = Validator::make($data, [
            'rate' => "required|integer|min:1|max:10"
        ]);

        $extra = collect(array_keys($data))->diff($allowed);
        if ($extra->isNotEmpty())
            return response()->json([
                'error' => "Unknown Attributes : " . $extra->implode(', ')
            ], 422);

        $validated_data = $validator->validated();



        try {
            $booking = Bookings::findOrFail($booking_id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => "Booking Not Found",
                'details' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => "Something Went Wrong",
                'details' => $e->getMessage()
            ], 404);
        }

        if ($booking->user_id != $user->id) {
            return response()->json(['message' => "Unauthorized"], 403);
        }
        if ($booking->booking_status != 'completed')
            return response()->json(['error' => 'You cannot rate Uncompleated Booking'], 403);


        $booking->update($validated_data);
        return response()->json(
            'Rated Successfully'
        );
    }
}
