<?php

namespace App\Http\Controllers;

use App\Exceptions\CanceledBookingException;
use App\Exceptions\CompletedBookingException;
use App\Exceptions\DateConflictException;
use App\Exceptions\ExtraAttributesException;
use App\Http\Requests\RateBookingRequest;
use App\Http\Requests\StoreBookingRequest;
// use App\Http\Requests\RateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ReceiptResource;
use App\Jobs\CompleteBooking;
use App\Models\Apartment;
use App\Models\Booking;
use App\Services\ApartmentService;
use App\Services\BookingService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $bookingService;

    protected $apartmentService;

    public function __construct(BookingService $bookingService, ApartmentService $apartmentService)
    {
        $this->bookingService   = $bookingService;
        $this->apartmentService = $apartmentService;
    }

    public function index()
    {
        $bookings = Auth::user()->bookings;

        foreach($bookings as $booking){
            $booking->load('apartment');
        }

        return response()->json([
            'message' => 'Bookings retrieved successfully',
            'bookings' => BookingResource::collection($bookings),
            'status_code' => 200
        ]);
    }

    // create new booking by the renter
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        try {
            $booking = $this->bookingService->createBooking($request, $data);
            CompleteBooking::dispatch($booking)->delay($booking->check_out_date);
            return response()->json(
                [
                    'message' => "Booking Created Successfully",
                    'booking' => new BookingResource($booking),
                    'status_code' => 201
                ],
                201
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 404], 404);
        } catch (ExtraAttributesException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'attributes' => $e->getAttributes(),
                'status_code' => $e->getCode()
            ], $e->getCode());
        } catch (DateConflictException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status_code' => $e->getCode()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status_code' => 500
            ], 500);
        }
    }

    // get booking by the renter
    public function show($booking_id)
    {
        try {
            $booking = Booking::with('apartment')->findOrFail($booking_id);
            $this->bookingService->checkUserAuthrization($booking);
            return response()->json(
                [
                    'message' => "Booking retrieved Successfully",
                    'bookings' => new BookingResource($booking),
                    'status_code' => 200
                ],
                200
            );
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }

    // Update booking by the renter
    public function update(UpdateBookingRequest $request, $booking_id)
    {
        $validated_data = $request->validated();
        $validated_data['booking_status'] = 'modified';
        try {
            $booking = $this->bookingService->updateBooking($request, $validated_data, $booking_id);
            CompleteBooking::dispatch($booking)->delay($booking->check_out_date);
            $booking->load('apartment');

            return response()->json(
                [
                    'message' => "Booking Updated Successfully",
                    'bookings' => new BookingResource($booking),
                    'status_code' => 200
                ],
                200
            );
        } catch (ExtraAttributesException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'attributes' => $e->getAttributes(),
                'status_code' => $e->getCode()
            ], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (DateConflictException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => $e->getCode()], $e->getCode());
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                return response()->json(['error' => $e->getMessage(), 'status_code' => $e->getCode()], $e->getCode());
            }
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }

    // cancle booking by the renter
    public function destroy($booking_id)
    {
        try {
            $this->bookingService->cancelBooking($booking_id);
            return response()->json(['message' => 'Booking Canceled Successfully.', 'status_code' => 200]);
        } catch (CompletedBookingException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 422], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (CanceledBookingException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 422], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }

    // rate booking by the renter
    public function rate(RateBookingRequest $request, $booking_id)
    {
        $validated = $request->validated();
        try {
            $this->bookingService->checkExtraAttributes($request, $validated);
            $this->bookingService->rateBooking($validated, $booking_id);

            return response()->json(['message' => 'Rating submitted successfully', 'status_code' => 200]);
        } catch (ExtraAttributesException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'attributes' => $e->getAttributes(),
                'status_code' => $e->getCode()
            ], $e->getCode());
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (CompletedBookingException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 422], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }


    // pay booking by the renter
    public function pay($booking_id)
    {
        try {
            $booking = Booking::findOrFail($booking_id);
            if ($booking->booking_status !== 'payment_pending') {
                return response()->json([
                    'error' => 'Cannot pay for this booking because its status is currently "' . $booking->booking_status . '"' . " it should be payment_pending.",
                    'status_code' => 400
                ], 400);
            }
            $booking->booking_status = 'checked_in';
            $booking->save();

            return response()->json(['message' => "Payment submitted successfully", "status_code" => 200], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found.", "status_code" => 404], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), "status_code" => 500], 500);
        }
    }

    // get all booking by the admin
    public function getAllBookings()
    {
        return response()->json([
            'message' => "Bookings retrieved successfully",
            'bookings' => BookingResource::collection(Booking::with(['renter', 'apartment'])->all()),
            'status_code' => 200
        ], 200);
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
            return response()->json(
                [
                    'message' => "Unconfirmed Bookings retrieved successfully for apartment with id $apartment_id",
                    'bookings' => BookingResource::collection($unconfirmedBookings),
                    'status_code' => 200
                ],
                200
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }

    // confirm renter booking by apartment owner
    public function confirmBooking($booking_id)
    {

        $booking = Booking::findOrFail($booking_id);
        $booking->booking_status = 'confirmed';
        $booking->save();

        return response()->json(['message' => 'Booking Confirmed Successfully', 'status_code' => 200]);

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
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }

    // reject reneter booking by apartment owner
    public function rejectBooking($booking_id)
    {

        $booking = Booking::findOrFail($booking_id);
        $booking->booking_status = 'rejected';
        $booking->save();

        return response()->json(['message' => 'Booking Rejected Successfully', 'status_code' => 200]);

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
            return response()->json(['error' => "Booking Not Found.", 'status_code' => 404], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 403], 403);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
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
            return response()->json(
                [
                    'message' => "Unconfirmed Bookings retrieved successfully",
                    'bookings' => BookingResource::collection($unconfirmedBookings),
                    'status_code' => 200
                ],
                200
            );
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status_code' => 500], 500);
        }
    }
}
