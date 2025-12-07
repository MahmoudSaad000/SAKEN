<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $apartment = new ApartmentResource($this->whenLoaded('apartment'));
        $renter = new UserResource($this->whenLoaded('renter'));
        $duration_in_days = $this->check_in_date->diffInDays($this->check_out_date);
        $total_price = $duration_in_days * $this->apartment->rental_price;

        return [
            'booking' => [
                'id' => $this->id,
                'booking_status' => $this->when($this->booking_status !== null, $this->booking_status),
                'rate' => $this->when($this->rate !== null, $this->rate),
                'payment_method' => $this->payment_method,
                'check_in_date' => $this->check_in_date->setTimezone('Asia/Damascus')->toDateTimeString(),
                'check_out_date' => $this->check_out_date->setTimezone('Asia/Damascus')->toDateTimeString(),
                'duration_in_days' => $duration_in_days,
                'total_price' => $total_price,
                'booking_date' => $this->created_at->setTimezone('Asia/Damascus')->toDateTimeString(),
                'apartment' => $apartment,
                'renter' => $renter,
            ],
        ];
    }
}
