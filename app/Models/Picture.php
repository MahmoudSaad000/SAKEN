<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    protected $fillable = ['picture', 'apartment_id'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getUrlAttribute()
{
    return asset(Storage::url($this->picture));
}

}
