<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function scopeCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    // ✅ فلترة حسب السعر
    public function scopePriceBetween($query, $min = null, $max = null)
    {
        return $query->when($min, fn ($q) => $q->where('price', '>=', $min))
                     ->when($max, fn ($q) => $q->where('price', '<=', $max));
    }

    // ✅ فلترة حسب المساحة
    public function scopeAreaBetween($query, $min = null, $max = null)
    {
        return $query->when($min, fn ($q) => $q->where('area', '>=', $min))
                     ->when($max, fn ($q) => $q->where('area', '<=', $max));
    }

    // ✅ فلترة حسب عدد الغرف
    public function scopeRooms($query, $rooms)
    {
        return $query->when($rooms, fn ($q) => $q->where('rooms', $rooms));
    }

     public function scopeGovernorate($query, $governorateId)
    {
        return $query->when($governorateId, function ($q) use ($governorateId) {
            $q->whereHas('city', function ($cityQuery) use ($governorateId) {
                $cityQuery->where('governorate_id', $governorateId);
            });
        });
    }
}
