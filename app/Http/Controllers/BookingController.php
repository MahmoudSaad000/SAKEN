<?php

namespace App\Http\Controllers;

use App\BookingService;
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

    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }


    public function index()
    {
        $user = Auth::user();
        return BookingResource::collection($user->bookings);
    }

    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();

        $extra = $this->getExtraAttributes($request,$validated);
        if ($extra->isNotEmpty()) {
            return response()->json([
                'error' => "Extra attributes: " . $extra->implode(', ')
            ], 422);
        }

        $validated['user_id'] = Auth::user()->id;

        // if($this->isThereDateConflict($validated)){
        //     return response()->json([  'error' => "Date Conflict",
        //                                'message' => "The date you selected for booking is not available because it conflicts with an existing reservation. Please choose another date."
        // ]);
        // }

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


    public function show($booking_id)
    {
        $booking = null;
        // if(Auth::user()->id != )
    }


    public function update(UpdateBookingRequest $request, $booking_id)
    {
        //
        $validated_data = $request->validated();
        $validated_data['role'] = 'modified';
        return $this->updateBooking($request, $validated_data, $booking_id);
    }


    public function destroy(Bookings $bookings)
    {
        //
    }



    public function rateBooking(HttpRequest $request, $booking_id)
    {
        $validator = Validator::make($request->all(), ['rate' => "required|integer|min:1|max:10"]);


        if (!$this->isBookingCompleated($booking_id))
            return response()->json(['error' => 'You cannot rate Uncompleated Booking'], 403);

        $this->updateBooking($request, $validator->validated(), $booking_id);

        return response()->json('Rated Successfully');
    }

    // Hellper functions

    private function findBooking($booking_id)
    {
        $booking = null;
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

        return $booking;
    }

    private function updateBooking($request, $validated, $booking_id)
    {
        $user = Auth::user();
        $booking = $this->findBooking($booking_id);

        $extra = $this->getExtraAttributes($request, $validated);
        if ($extra->isNotEmpty()) {
            return response()->json([
                'error' => "Extra attributes: " . $extra->implode(', ')
            ], 422);
        }

        if ($booking->user_id != $user->id) {
            return response()->json(['message' => "Unauthorized"], 403);
        }


        $booking->update($validated);
        return new BookingResource($booking);
    }

    private function getExtraAttributes($request, $validated_data)
    {
        return collect(array_keys($request->all()))->diff(array_keys($validated_data));
    }
    
    private function isBookingCompleated($booking_id){
        return ($this->findBooking($booking_id)->booking_status === 'completed');
    }

    private function datesConflict($startA, $endA, $startB, $endB)
    {
        return ($startA <= $endB) && ($endA >= $startB);
    }

    private function findAppartment($appartment_id) {
        try {
            // return Appartment::findOrFail($appartment_id);
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

    private function isThereDateConflict($data){
        // $appartment = $this->findAppartment($data->appartment_id);
        // $appartment_bookings = $appartment->bookings;
        // foreach ($appartment_bookings as $appartment_booking) {
        //     if($this->datesConflict($data->check_in_date,$data->check_out_date,$appartment_booking->check_in_date,$appartment_booking->check_out_date))
        //         return true;
        // }
        // return false;
    }
}
