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

        // Define allowed statuses for updates
        $updatableStatuses = ['payment_pending', 'pending', 'modified'];

        if (!in_array($booking->booking_status, $updatableStatuses)) {
            throw new Exception("You can't update this booking because its current status is $booking->booking_status.", 422);
        }

        //$this->checkExtraAttributes($request, $validated_request);
        $this->checkUserAuthrization($booking);

        // Use existing apartment_id if not provided
        $apartmentId = $validated_request['apartment_id'] ?? $booking->apartment_id;
        $validated_request['apartment_id'] = $apartmentId;

        // Only check date conflict if dates are being changed
        $datesChanged = isset($validated_request['check_in_date']) ||
            isset($validated_request['check_out_date']);

        if ($datesChanged) {
            $checkIn = $validated_request['check_in_date'] ?? $booking->check_in_date;
            $checkOut = $validated_request['check_out_date'] ?? $booking->check_out_date;

            // Validate that check-out is after check-in
            if ($checkOut <= $checkIn) {
                throw new Exception("Check-out date must be after check-in date.", 422);
            }

            $conflict = Booking::conflicting(
                $apartmentId,
                $checkIn,
                $checkOut,
                $booking_id
            )->exists();

            if ($conflict) {
                throw new DateConflictException("The apartment is already booked for the selected dates.");
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

        $apartment_id = $booking->apartment_id;
        $averageRate = app(ApartmentService::class)->getApartmentRating($apartment_id);
        $apartment = Apartment::findOrFail($apartment_id);
        $apartment->update(['average_rate' => $averageRate]);

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

    public function  createBooking($request, $validated_request)
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
