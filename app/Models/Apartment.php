<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    protected $guarded = [];

     public function bookings()
    {
        return $this->hasMany(Bookings::class);
    }
}
