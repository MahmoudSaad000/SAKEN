<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Apartment;
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
        // $tatal_price = $duration_in_days * $apartment['rental_price'];
 
     $totalPrice = 0;
    if ($this->relationLoaded('apartment') && $this->apartment) {
        $totalPrice = $duration_in_days * $this->apartment->rental_price;
    } else if ($this->apartment_id) {
        // إذا كنت تريد حساب السعر حتى بدون تحميل العلاقة
        // قد تحتاج لجلب السعر من قاعدة البيانات
        $apartment = Apartment::find($this->apartment_id);
        if ($apartment) {
            $totalPrice = $duration_in_days * $apartment->rental_price;
        }
    }
        return [
            'id' => $this->id,
            'booking_status' => $this->when($this->booking_status !== null, $this->booking_status),
            'rate' => $this->when($this->rate !== null, $this->rate),
            'payment_method' => $this->payment_method,
            'check_in_date' => $this->check_in_date->setTimezone('Asia/Damascus')->toDateTimeString(),
            'check_out_date' => $this->check_out_date->setTimezone('Asia/Damascus')->toDateTimeString(),
            'duration_in_days' => $duration_in_days,
            'total_price' => $totalPrice,
            'createdAt' => $this->created_at->setTimezone('Asia/Damascus')->toDateTimeString(),
            'renter' => $this->whenLoaded('renter', UserResource::make($this->renter)),
            'apartment' => $this->whenLoaded('apartment', ApartmentResource::make($this->apartment)),
        ];
    }
}
