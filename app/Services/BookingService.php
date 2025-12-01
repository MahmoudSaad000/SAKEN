<?php

namespace App\Services;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            throw new NotFoundHttpException("Booking Not Found", $e);
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
            throw new HttpException(422, "Extra attributes: " . $extra->implode(', '));
        }

        if (!$this->doesBookingBelongToUser(Auth::user(), $booking)) {
            throw new Exception(403, "Unauthorized");
        }

        $booking->update($validated);
        return new BookingResource($booking);
    }



    public function getExtraAttributes($request, $validated_data)
    {
        if (is_null($request)) {
            return collect([]);
        }
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

    public function isThereDateConflict($data)
    {
        $apartment = $this->apartmentService->findApartment($data['apartment_id']);
        return $apartment->bookings()
            ->where('check_in_date', '<=', $data['check_out_date'])
            ->where('check_out_date', '>=', $data['check_in_date'])
            ->exists();
    }
}
