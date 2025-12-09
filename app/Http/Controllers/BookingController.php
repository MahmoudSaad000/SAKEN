<?php

namespace App\Http\Controllers;

use App\Exceptions\CanceledBookingException;
use App\Exceptions\CompletedBookingException;
use App\Exceptions\DateConflictException;
use App\Exceptions\ExtraAttributesException;
use App\Http\Requests\RateBookingRequest;
use App\Models\Booking;
//use App\Http\Requests\RateBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ReceiptResource;
use App\Models\Apartment;
use App\Models\User;
use App\Services\ApartmentService;
use App\Services\BookingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
        $user_bookings = $user->bookings;

        foreach ($user_bookings as $booking) {
            $booking->load('apartment');
        }

        return BookingResource::collection($user_bookings);
    }

    // create new booking by the renter
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        try {
            $booking = $this->bookingService->createBooking($request, $data);
            $booking->load('apartment');
            return new BookingResource($booking);
        } catch (ExtraAttributesException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'attributes' => $e->getAttributes()
            ], $e->getCode());
        } catch (DateConflictException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get booking by the renter
    public function show($booking_id)
    {
        try {
            $booking = Booking::with('apartment')->findOrFail($booking_id);
            $this->bookingService->checkUserAuthrization($booking);
            return new BookingResource($booking);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
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
            $booking->load('apartment');
            return new BookingResource($booking);
        } catch (ExtraAttributesException $e) {
            return response()->json(['error' => $e->getMessage() . $e->getAttributes()], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (DateConflictException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                return response()->json(['error' => $e->getMessage()], $e->getCode());
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // cancle booking by the renter
    public function destroy($booking_id)
    {
        try {
            $this->bookingService->cancelBooking($booking_id);
            return response()->json(['message' => 'Booking Canceled Successfully.']);
        } catch (CompletedBookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
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
            return response()->json('Rating submitted successfully');
        } catch (ExtraAttributesException $e) {
            return response()->json(['error' => $e->getMessage() . $e->getAttributes()], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (CompletedBookingException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // pay booking by the renter
    public function pay($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            if ($booking->booking_status !== 'payment_pending') {
                return response()->json([
                    'error' => 'Cannot pay for this booking because its status is currently "' . $booking->booking_status . '"' . " it should be payment_pending."
                ], 400);
            }
            $booking->booking_status = 'checked_in';
            $booking->save();

            return new ReceiptResource($booking);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // get all booking by the admin
    public function getAllBookings()
    {
        return BookingResource::collection(Booking::with(['renter', 'apartment'])->all());
    }
    // get unconfirmed and modified bookings by the apartment owner in order to approve them
    public function getUnConfirmedBookings($apartment_id)
    {
        try {
            $apartment = Apartment::findOrFail($apartment_id);
            $this->apartmentService->checkUserAuthrization($apartment);
            $unconfirmedBookings = $this->apartmentService->getUnconfirmedModifiedBookings($apartment);
            foreach ($unconfirmedBookings as $unconfirmedBooking) {
                $unconfirmedBooking->load('renter');
            }
            return BookingResource::collection($unconfirmedBookings);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // confirm renter booking by apartment owner
    public function confirmBooking($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $apartment = Apartment::findOrFail($booking->apartment_id);
            $this->apartmentService->checkUserAuthrization($apartment);
            if ($booking->booking_status !== 'pending' && $booking->booking_status !== 'modified')
                return response()->json(["error" => "Booking cannot be confirmed in its current state"], 400);
            $booking->booking_status = 'payment_pending';
            $booking->save();
            return response()->json(['message' => 'Booking Confirmed Successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
    // reject reneter booking by apartment owner 
    public function rejectBooking($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            $apartment = Apartment::findOrFail($booking->apartment_id);
            $this->apartmentService->checkUserAuthrization($apartment);
            if ($booking->booking_status !== 'pending' && $booking->booking_status !== 'modified')
                return response()->json(["error" => "Booking cannot be confirmed in its current state"], 400);
            $booking->booking_status = 'reject';
            $booking->save();
            return response()->json(['message' => 'Booking Rejected Successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found."], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // get unconfirmed and modified booking for each apartment the user own
    public function getAllUnconfirmedBookings()
    {

        try {
            $user = Auth::user();
            $user_apartments = $user->apartments;

            $unconfirmedBookings = collect();

            foreach ($user_apartments as $apartment) {
                $unconfirmedBookings = $unconfirmedBookings->merge(
                    $this->apartmentService->getUnconfirmedModifiedBookings($apartment)
                );
            }
            foreach ($unconfirmedBookings as $booking) {
                $booking->load(['apartment', 'renter']);
            }

            return BookingResource::collection($unconfirmedBookings);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
