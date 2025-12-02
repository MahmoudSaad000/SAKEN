<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
            [
                'id' => $this->id,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'phone_number' => $this->phone_number,
                'date_of_birth' => $this->date_of_birth,
                'picture' => $this->picture,
                'id_card_image' => $this->id_card_image,
                'is_approved' => $this->is_approved,
                'role' => $this->role
            ];
    }
}
