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
            'description' => $this->description,
            'area' => $this->area,
            'rooms' => $this->rooms,
            'living_rooms' => $this->living_rooms,
            'bathrooms' => $this->bathrooms,
            'rental price' => $this->rental_price,
            'address' => $this->address,
            'status' => $this->status,
            'average rate' => $this->average_rate,
            'city' => $this->city->name,
            'governorate' => $this->city->governorate->name,
            'owner' => $this->user->firstname.' '.$this->user->lastname,
            'pictures' => $this->pictures->pluck('picture'),
        ];
    }
}
