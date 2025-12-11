<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{

    
    return [
        'area'            => $this->area,
        'rooms'           => $this->rooms,
        'living_rooms'    => $this->living_rooms,
        'bathrooms'       => $this->bathrooms,
        'rental_price'    => $this->rental_price,
        'governorate'     => $this->city?->governorate?->name,
        'city'            => $this->city?->name,
        'address'         => $this->address,
        'status'          => $this->status,
        'average_rate'    => $this->average_rate,
        'owner'           => $this->user?->firstname . ' ' . $this->user?->lastname,
        'description'     => $this->description,
        'pictures'        => $this->pictures?->pluck('picture'),
    ];
}

}
