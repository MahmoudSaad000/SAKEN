<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Http\Requests\RateBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\User;
use App\Services\ApartmentService;
use App\Services\BookingService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class BookingController extends Controller
{
    protected $bookingService;
    protected $apartmentService;

    public function __construct(BookingService $bookingService, ApartmentService $apartmentService)
    {
        $this->bookingService = $bookingService;
        $this->apartmentService = $apartmentService;
    }

    public function index()
    {
        $user = Auth::user();

        return BookingResource::collection($user->bookings);
    }

    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $validated['renter_id'] = Auth::user()->id;
        $booking = $this->bookingService->createBooking($request, $validated);
        return new BookingResource($booking);
    }

    public function show($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);
        $this->bookingService->checkUserAuthrization($booking);
        return new BookingResource($booking);
    }

    public function update(UpdateBookingRequest $request, $booking_id)
    {
        $validated_data = $request->validated();
        $validated_data['booking_status'] = 'modified';
        $booking = $this->bookingService->updateBooking($request, $validated_data, $booking_id);
        return new BookingResource($booking);
    }

    public function destroy($booking_id)
    {
        $this->bookingService->cancelBooking($booking_id);
        return response()->json(['message' => 'Booking Cancelled Successfully.']);
    }

    public function rate(RateBookingRequest $request, $booking_id)
    {
        $validated = $request->validated();
        $this->bookingService->checkExtraAttributes($request,$validated);
        $this->bookingService->rateBooking($validated,$booking_id);
        return response()->json('Rated Successfully');
    }

    public function getAllBookings()
    {
        try {
            return BookingResource::collection(Booking::all());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e);
        }
    }

    public function getUnConfirmedBookings($apartment_id)
    {

        try {
            $apartment = $this->apartmentService->findApartment($apartment_id);
            if (!$this->apartmentService->doesApartmentBelongToUser($apartment)) {
                return response()->json(['error' => "Unauthorized"], 403);
            }

            $unconfirmedBookings = $apartment->bookings()->where('booking_status', '=', 'pending')->get();
            return BookingResource::collection($unconfirmedBookings);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function confirmBooking($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $booking->booking_status = 'confirmed';
            $booking->save();
            return response()->json(['message' => 'Booking Confirmed Successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e);
        }
    }

    public function rejectBooking($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $booking->booking_status = 'rejected';
            $booking->save();
            return response()->json(['message' => 'Booking Rejected Successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e);
        }
    }
}
