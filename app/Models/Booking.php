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
        'booking_status',
        'payment_method',
        'check_in_date',
        'check_out_date',
        'apartment_id',
        'user_id',
    ];

    protected $table = 'bookings';

    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
    ];

    public function renter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function scopeConflicting($query, $apartmentId, $checkIn, $checkOut, $excludeBookingId = null)
    {
        return $query->where('apartment_id', $apartmentId)
            // Check conflicts with bookings that could potentially occupy the apartment
            ->whereIn('booking_status', ['checked_in', 'payment_pending', 'pending', 'modified'])
            ->where('check_in_date', '<=', $checkOut)
            ->where('check_out_date', '>=', $checkIn)
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId));
    }
}
