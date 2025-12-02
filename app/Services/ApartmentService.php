<?php

namespace App\Services;

use App\Models\Apartment;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ApartmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function checkUserAuthrization($apartment)
    {
        if (Auth::user()->id !== $apartment->user_id)
            throw new AuthorizationException();
    }

}
