<?php

namespace App\Services;

use App\Exceptions\CanceledBookingException;
use App\Exceptions\CompletedBookingException;
use App\Exceptions\DateConflictException;
use App\Exceptions\ExtraAttributesException;
use App\Models\Apartment;
use App\Models\Booking;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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

    public function updateBooking($request, $validated_request, $booking_id)
    {
        $booking = Booking::findOrFail($booking_id);

        if (
            $booking->booking_status !== 'payment_pending' &&
            $booking->booking_status !== 'pending' &&
            $booking->booking_status !== 'modified'
        ) {
            throw new Exception("You can't update this booking becouse it's currntly status is $booking->booking_status.", 422);
        }


        $this->checkExtraAttributes($request, $validated_request);
        $this->checkUserAuthrization($booking);

        $validated_request['apartment_id'] = $booking->apartment_id;

        // Only check date conflict if the user provided both dates
        if (isset($validated_request['check_in_date'], $validated_request['check_out_date'])) {
            $conflict = Booking::conflicting(
                $validated_request['apartment_id'],
                $validated_request['check_in_date'],
                $validated_request['check_out_date'],
                $booking_id
            )->exists();

            if ($conflict) {
                throw new DateConflictException;
            }
        }

        $booking->update($validated_request);

        return $booking;
    }

    public function getExtraAttributes($request, $validated_data)
    {
        if (is_null($request)) {
            return collect([]);
        }

        return collect(array_keys($request->all()))->diff(array_keys($validated_data));
    }

    public function cancelBooking($booking_id)
    {
        $booking = Booking::findOrFail($booking_id);
        $this->checkUserAuthrization($booking);
        $this->checkBookingStatus($booking);
        $booking->booking_status = self::STATUS_CANCELLED;
        $booking->save();

        return $booking;
    }

    public function rateBooking($Validated, $booking_id)
    {
        $booking = Booking::findOrFail($booking_id);
        $this->checkUserAuthrization($booking);

        if ($booking->booking_status !== self::STATUS_COMPLETED) {
            throw new CompletedBookingException("You Can't Rate Uncompleted Bookings");
        }

        $booking->rate = $Validated['rate'];
        $booking->save();

        return $booking;
    }

    public function checkBookingStatus($booking)
    {
        if ($booking->booking_status === self::STATUS_COMPLETED) {
            throw new CompletedBookingException;
        } elseif ($booking->booking_status === self::STATUS_CANCELLED) {
            throw new CanceledBookingException;
        }
    }

    public function checkUserAuthrization($booking)
    {
        if (Auth::user()->id !== $booking->user_id) {
            throw new AuthorizationException;
        }
    }

    public function checkExtraAttributes($request, $validated_request)
    {
        $extra = $this->getExtraAttributes($request, $validated_request);

        if ($extra->isNotEmpty()) {
            throw new ExtraAttributesException($extra);
        }
    }

    public function createBooking($request, $validated_request)
    {
        $this->checkExtraAttributes($request, $validated_request);

        $conflict = Booking::conflicting(
            $validated_request['apartment_id'],
            $validated_request['check_in_date'],
            $validated_request['check_out_date']
        )->exists();

        if ($conflict) {
            throw new DateConflictException;
        }

        $apartment = Apartment::findOrFail($validated_request['apartment_id']);
        $apartment->status = 'Booked';
        $apartment->save();
        return Booking::create($validated_request);
    }
}
