<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
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
}
