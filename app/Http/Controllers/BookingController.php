<?php

namespace App\Http\Controllers;


use App\Http\Requests\RateBookingRequest;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        $extra = $this->bookingService->getExtraAttributes($request, $validated);
        if ($extra->isNotEmpty()) {
            return response()->json([
                'error' => "Extra attributes: " . $extra->implode(', ')
            ], 422);
        }

        $validated['user_id'] = Auth::user()->id;

        try {

            if ($this->bookingService->isThereDateConflict($validated)) {
                return response()->json([
                    'error' => "Date Conflict",
                    'message' => "The date you selected for booking is not available because it conflicts with an existing reservation. Please choose another date."
                ], 422);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e);
        }

        try {
            $booking = Booking::create($validated);
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
        try {
            $booking = $this->bookingService->findBooking($booking_id);
            if (!$this->bookingService->doesBookingBelongToUser(Auth::user(), $booking)) {
                return response()->json(['error' => "Unauthorized"]);
            }
            return new BookingResource($booking);
        } catch (Exception $e) {
            $statusCode = 500;

            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
            }

            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }


    public function update(UpdateBookingRequest $request, $booking_id)
    {
        $validated_data = $request->validated();
        $validated_data['booking_status'] = 'modified';
        try {
            return $this->bookingService->updateBooking($request, $validated_data, $booking_id);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function destroy($booking_id)
    {
        try {
            $booking = $this->bookingService->findBooking($booking_id);

            if (!$this->bookingService->doesBookingBelongToUser(Auth::user(), $booking)) {
                return response()->json(['error' => "Unauthorized"],403);
            }

            if ($this->bookingService->isBookingCompleted($booking_id)) {
                return response()->json(['error' => "The Booking is already completed"],422);
            }

            if ($this->bookingService->isBookingCancelled($booking_id)) {
                return response()->json(['error' => "The Booking is already cancelled"],422);
            }

            $validated = ['booking_status' => 'cancelled'];

            $this->bookingService->updateBooking(null, $validated, $booking_id);
            return response()->json(['message' => 'Booking Cancelled Successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e);
        }
    }



    public function rateBooking(RateBookingRequest $request, $booking_id)
    {
        if (!$this->bookingService->isBookingCompleted($booking_id))
            return response()->json(['error' => 'You cannot rate Uncompleated Booking'], 422);
        try {
            $this->bookingService->updateBooking($request, $request->validated(), $booking_id);
            return response()->json('Rated Successfully');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
