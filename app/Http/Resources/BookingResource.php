<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Apartment;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Calculate duration
        $durationInDays = $this->check_in_date->diffInDays($this->check_out_date);

        // Initialize apartment and renter resources
        $apartment = $this->whenLoaded(
            'apartment',
            fn() => ApartmentResource::make($this->apartment)
        );

        $renter = $this->whenLoaded(
            'renter',
            fn() => UserResource::make($this->renter)
        );

        // Calculate total price
        $totalPrice = $this->calculateTotalPrice($durationInDays);

        // Format dates with timezone
        $checkInDate  = $this->check_in_date->copy()->setTimezone('Asia/Damascus')->toDateTimeString();
        $checkOutDate = $this->check_out_date->copy()->setTimezone('Asia/Damascus')->toDateTimeString();
        $createdAt    = $this->created_at->copy()->setTimezone('Asia/Damascus')->toDateTimeString();

        // Build booking data
        return [
            'id'               => $this->id,
            'booking_status'   => $this->whenNotNull($this->booking_status),
            'rate'             => $this->whenNotNull($this->rate),
            'payment_method'   => $this->payment_method,
            'check_in_date'    => $checkInDate,
            'check_out_date'   => $checkOutDate,
            'duration_in_days' => $durationInDays,
            'total_price'      => $totalPrice,
            'createdAt'        => $createdAt,
            'renter'           => $renter,
            'apartment'        => $apartment,
        ];

    }

//     public function toArray(Request $request): array
// {
//     $checkIn  = $this->check_in_date?->copy();
//     $checkOut = $this->check_out_date?->copy();
//     $created  = $this->created_at?->copy();

//     $durationInDays = $checkIn && $checkOut
//         ? $checkIn->diffInDays($checkOut)
//         : 0;

//     return [
//         'id'               => $this->id,
//         'booking_status'   => $this->booking_status,
//         'payment_method'   => $this->payment_method,

//         'check_in_date' => $checkIn
//             ? $checkIn->setTimezone('Asia/Damascus')->toDateTimeString()
//             : null,

//         'check_out_date' => $checkOut
//             ? $checkOut->setTimezone('Asia/Damascus')->toDateTimeString()
//             : null,

//         'duration_in_days' => $durationInDays,

//         'total_price' => $this->relationLoaded('apartment') && $this->apartment
//             ? $durationInDays * $this->apartment->rental_price
//             : 0,

//         'createdAt' => $created
//             ? $created->setTimezone('Asia/Damascus')->toDateTimeString()
//             : null,
//     ];
// }


    /**
     * Calculate the total price for the booking
     */
    private function calculateTotalPrice(int $durationInDays): float
    {
        if ($this->relationLoaded('apartment') && $this->apartment) {
            return $durationInDays * $this->apartment->rental_price;
        }

        if ($this->apartment_id) {
            $apartment = Apartment::find($this->apartment_id);
            if ($apartment) {
                return $durationInDays * $apartment->rental_price;
            }
        }

        return 0;
    }
}
