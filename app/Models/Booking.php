<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingsFactory> */
    use HasFactory;

    protected $fillable = [
        'rate',
        'payment_method',
        'check_in_date',
        'check_out_date',
        'appartment_id',
        'user_id'
    ];

    protected $table = 'bookings';

    public function renter(){
        $this->belongsTo(User::class);
    }

     public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
