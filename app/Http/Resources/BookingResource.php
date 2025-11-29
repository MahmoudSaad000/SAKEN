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
        // $appartment = new AppartmentRecource($this->whenLoaded('appartment'));
        // $renter = new UserRecource($this->whenLoaded('user'));
        $duration_in_days = $this->check_out_date->diffInDays($this ->check_in_date);
        // $tatal_price = $duration_in_days * $appartment->rental_price;
 
        return [
            'id' => $this ->id,
            'booking_status' => $this ->booking_status,
            'rate' => $this ->rate,
            'payment_method' => $this ->payment_method,
            'check_in_date' => $this ->check_in_date->setTimezone('Asia/Damascus')->toDateTimeString(),
            'check_out_date' => $this ->check_out_date->setTimezone('Asia/Damascus')->toDateTimeString(),
            'duration_in_days' => $duration_in_days,
            'total_price' => null,
            'createdAt'  => $this->created_at->setTimezone('Asia/Damascus')->toDateTimeString(),
            'renter' => null,
            'appartment' => null
        ];
    }
}
