<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use function Symfony\Component\Clock\now;

class ReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $duration_in_days = $this->check_in_date->diffInDays($this->check_out_date);
        $total_price = $duration_in_days * $this->apartment->rental_price;

        return [
            'tenant_name'       => $this->renter->firstname . " ". $this->renter->lastname,
            'reservation_number'=> $this->id,
            'issue_date'        => now(),
            
            'payment_details' => [
                'status'         => 'Payment received successfully',
                'method'         => $this->payment_method,
                'amount_paid'    => $total_price,
            ],

            'access_code' => [
                'code'           => mt_rand(100000,999999),
                'validity'       => 'One-time use at check-in',
            ],

            'notes' => [
                'keep_safe'      => 'Do not share this code with unauthorized parties.'
            ],
        ];
    }
}