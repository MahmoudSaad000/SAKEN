<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentReq;
use App\Http\Requests\UpdateApartmentReq;
use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\Picture;
use App\Services\ApartmentService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\FilterReq;
use PHPUnit\Event\TestSuite\Filtered;

class ApartmentController extends Controller
{
    protected $apartmentService;

    public function __construct(ApartmentService $apartmentService)
    {
        $this->apartmentService = $apartmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $apartments = Auth::user()->apartments;

        return ApartmentResource::collection($apartments);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApartmentReq $request)
    {
        try {
            $apartment = Apartment::create([
                'description' => $request->description,
                'area' => $request->area,
                'rooms' => $request->rooms,
                'living_rooms' => $request->living_rooms,
                'bathrooms' => $request->bathrooms,
                'rental_price' => $request->rental_price,
                'address' => $request->address,
                'status' => $request->status,
                'city_id' => $request->city_id,
                'user_id' => Auth::id(),
                'average_rate' => null,
            ]);

            foreach ($request->file('images') as $image) {
                $path = $image->store('apartments', 'public');

                Picture::create([
                    'picture' => $path,
                    'apartment_id' => $apartment->id,
                ]);
            }

            return response()->json([
                'message' => 'Apartment created successfully',
                'apartment' => new ApartmentResource($apartment),

                // 'apartment' => $apartment->load('pictures')
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Something Went Wrong',
                'details' => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $apartment = $this->apartmentService->findApartment($id);

        return new ApartmentResource($apartment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateApartmentReq $request, string $id)
    {
        $userId = Auth::user()->id;
        $apartment = $this->apartmentService->findApartment($id);
        if ($apartment->user_id != $userId) {
            return response()->json('Unauthenticated.', 403);
        }
        $apartment->update($request->validated());

        return new ApartmentResource($apartment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $apartment = $this->apartmentService->findApartment($id);
        $userId = Auth::user()->id;
        if ($apartment->user_id != $userId) {
            return response()->json('Unauthenticated.', 403);
        }
        $apartment->delete();

        return response()->json(['massage'=>'deleted successfully']);
    }


     public function getAllApartments()
    {
       $apartments=Apartment::all();
       return response()->json([
        'message'=>'All Apartments :',
        'date'=> ApartmentResource::collection($apartments)
       ]);
    } 


public function filterByGovernorate($governorateId)
    {
        $apartments = Apartment::whereHas('city', function ($query) use ($governorateId) {
            $query->where('governorate_id', $governorateId);
        })
        ->with(['city.governorate'])
        ->get();

        return response()->json([
            'success' => true,
            'data' => ApartmentResource::collection($apartments),
        ]);
    }

    public function filterByCity($cityId)
{
    $apartments = Apartment::where('city_id', $cityId)
        ->with(['city.governorate'])
        ->get();

    return response()->json([
        'success' => true,
        'data' => ApartmentResource::collection($apartments),
    ]);
}

public function filterByRooms($rooms)
{
    $apartments = Apartment::where('rooms', $rooms)
        ->with(['city.governorate'])
        ->get();

    return response()->json([
        'success' => true,
        'data' => ApartmentResource::collection($apartments),
    ]);
}

public function filterByPrice($minPrice = null, $maxPrice = null)
{
    $query = Apartment::query();
    
    // الفلترة حسب السعر الأدنى
    if ($minPrice !== null) {
        $query->where('rental_price', '>=', $minPrice);
    }
    
    // الفلترة حسب السعر الأعلى
    if ($maxPrice !== null) {
        $query->where('rental_price', '<=', $maxPrice);
    }
    
    $apartments = $query->with(['city.governorate'])->get();

    return response()->json([
        'success' => true,
        'data' => ApartmentResource::collection($apartments),
    ]);
}

public function filterByArea($minArea = null, $maxArea = null)
{
    $query = Apartment::query();
    
    // الفلترة حسب المساحة الأدنى
    if ($minArea !== null && is_numeric($minArea)) {
        $query->where('area', '>=', (float)$minArea);
    }
    
    // الفلترة حسب المساحة الأعلى
    if ($maxArea !== null && is_numeric($maxArea)) {
        $query->where('area', '<=', (float)$maxArea);
    }
    
    $apartments = $query->with(['city.governorate'])->get();

    return response()->json([
        'success' => true,
        'data' => ApartmentResource::collection($apartments),
    ]);
}

public function addToFavorites($apartmentId)
{
    $apartment =$this->apartmentService->findApartment($apartmentId);
    Auth::user()->favoriteApartments()->syncWithoutDetaching($apartmentId);
    return response()->json([
        'message' => 'Apartment added successfully',
        'data' => new ApartmentResource($apartment),
         
    ]);
}

public function getFavorites()
{
    $user = auth()->user();
    $favorites = $user->favoriteApartments()->get();
    
    return response()->json([
    'message' => 'Your favorites Apartments :',
    'data'=> ApartmentResource::collection($favorites),
]);
}

public function removeFromFavorites($apartmentId)
{
    $user = auth()->user();
    $user->favoriteApartments()->detach($apartmentId);
    
    return response()->json(['message' => 'Removed from favorites']);
}
}
