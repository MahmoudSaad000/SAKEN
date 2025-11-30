<?php

namespace App;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    protected $apartmentService;

    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function __construct(ApartmentService $apartmentService)
    {
        $this->apartmentService = $apartmentService;
    }

    public function findBooking($booking_id)
    {
        try {
            return Booking::findOrFail($booking_id);
        } catch (ModelNotFoundException $e) {
            throw new Exception("Booking Not Found", 404);
        } catch (Exception $e) {
            throw new Exception("Something Went Wrong", 500);
        }
    }

    public function doesBookingBelongToUser($user, $booking)
    {
        return $user->id == $booking->user_id;
    }

    public function updateBooking($request, $validated, $booking_id)
    {
        $booking = $this->findBooking($booking_id);

        $extra = $this->getExtraAttributes($request, $validated);
        if ($extra->isNotEmpty()) {
            throw new Exception("Extra attributes: " . $extra->implode(', '), 422);
        }

        if (!$this->doesBookingBelongToUser(Auth::user(),$booking)) {
            throw new Exception("Unauthorized", 403);
        }

        $booking->update($validated);
        return new BookingResource($booking);
    }



    public function getExtraAttributes($request, $validated_data)
    {
        return collect(array_keys($request->all()))->diff(array_keys($validated_data));
    }

    public function isBookingCompleted($booking_id)
    {
        $booking = $this->findBooking($booking_id);
        return $booking->booking_status === self::STATUS_COMPLETED;
    }

    public function isBookingCancelled($booking_id)
    {
        $booking = $this->findBooking($booking_id);
        return $booking->booking_status === self::STATUS_CANCELLED;
    }

    public function datesConflict($startA, $endA, $startB, $endB)
    {
        return ($startA <= $endB) && ($endA >= $startB);
    }

    public function isThereDateConflict($data)
    {
        $appartment = $this->appartmentService->findApartment($data['appartment_id']);
        $appartment_bookings = $appartment->bookings();

        foreach ($appartment_bookings as $appartment_booking) {
            if ($this->datesConflict(
                $data->check_in_date,
                $data->check_out_date,
                $appartment_booking->check_in_date,
                $appartment_booking->check_out_date
            )) {
                return true;
            }
        }

        return false;
    }
}
