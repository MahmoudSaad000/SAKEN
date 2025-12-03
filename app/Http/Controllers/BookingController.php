<?php

namespace App\Http\Controllers;

use App\Exceptions\CanceledBookingException;
use App\Exceptions\CompletedBookingException;
use App\Exceptions\DateConflictException;
use App\Exceptions\ExtraAttributesException;
use App\Http\Requests\RateBookingRequest;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Apartment;
use App\Models\User;
use App\Services\ApartmentService;
use App\Services\BookingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    // get all renter bookings
    public function index()
    {
        $user = Auth::user();
        return BookingResource::collection($user->bookings);
    }

    // create new booking by the renter
    public function store(StoreBookingRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::user()->id;
        try {
            $booking = $this->bookingService->createBooking($request, $validated);
            return new BookingResource($booking);
        } catch (ExtraAttributesException $e) {
            return response()->json(['error' => $e->getMessage() . $e->getAttributes()], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (DateConflictException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // get booking by the renter
    public function show($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $this->bookingService->checkUserAuthrization($booking);
            return new BookingResource($booking);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update booking by the renter
    public function update(UpdateBookingRequest $request, $booking_id)
    {
        $validated_data = $request->validated();
        $validated_data['booking_status'] = 'modified';
        try {
            $booking = $this->bookingService->updateBooking($request, $validated_data, $booking_id);
            return new BookingResource($booking);
        } catch (ExtraAttributesException $e) {
            return response()->json(['error' => $e->getMessage() . $e->getAttributes()], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (DateConflictException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // cancle booking by the renter
    public function destroy($booking_id)
    {
        try{
            $this->bookingService->cancelBooking($booking_id);
            return response()->json(['message' => 'Booking Cancelled Successfully.']);
        }catch (CompletedBookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (CanceledBookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // rate booking by the renter
    public function rate(RateBookingRequest $request, $booking_id)
    {
        $validated = $request->validated();
        try {
            $this->bookingService->checkExtraAttributes($request, $validated);
            $this->bookingService->rateBooking($validated, $booking_id);
            return response()->json('Rated Successfully');
        } catch (ExtraAttributesException $e) {
            return response()->json(['error' => $e->getMessage() . $e->getAttributes()], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (CompletedBookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (CanceledBookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // get all booking by the admin
    public function getAllBookings()
    {
        return BookingResource::collection(Booking::all());
    }
    // get unconfirmed and modified bookings by the apartment owner in order to approve them
    public function getUnConfirmedBookings($apartment_id)
    {
        $apartment = Apartment::findOrFail($apartment_id);

        $this->apartmentService->checkUserAuthrization($apartment);

        $unconfirmedBookings = $apartment->bookings()
            ->whereIn('booking_status', ['pending', 'modified'])
            ->get();

        return BookingResource::collection($unconfirmedBookings);
    }
    // confirm renter booking by apartment owner
    public function confirmBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);
        $booking->booking_status = 'confirmed';
        $booking->save();
        return response()->json(['message' => 'Booking Confirmed Successfully']);
    }
    // reject reneter booking by apartment owner 
    public function rejectBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);
        $booking->booking_status = 'rejected';
        $booking->save();
        return response()->json(['message' => 'Booking Rejected Successfully']);
    }
}
