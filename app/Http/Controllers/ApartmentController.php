<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentReq;
use App\Http\Requests\UpdateApartmentReq;
use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\Picture;
use App\Services\ApartmentService;
use Exception;
// use App\ApartmentService;
use Illuminate\Support\Facades\Auth;

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

        return response()->json('deleted successfully', 204);
    }
}
