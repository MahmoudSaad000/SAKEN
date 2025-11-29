<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function register(RegisterUserRequest $request)
    {

        $validated = $request->validated();

        $userData = [
            'First_Name' => $validated['First_Name'],
            'Last_Name' => $validated['Last_Name'],
            'Phone_Number' => $validated['Phone_Number'],
            'Date_Of_Birth' => $validated['Date_Of_Birth'],
            'Role' => $validated['Role'],
            'password' => Hash::make($validated['password']),
        ];

        if ($request->hasFile('Picture')) {
            $picturePath = $request->file('Picture')->store('profile_pictures', 'public');
            $userData['Picture'] = $picturePath;
        }


        if ($request->hasFile('Id_Card_Image')) {
            $idCardPath = $request->file('Id_Card_Image')->store('id_cards', 'public');
            $userData['Id_Card_Image'] = $idCardPath;
        }

        $user = User::create($userData);

        return response()->json([
            'message' => 'User Registered Successfully.',
            'User' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'Phone_Number' => 'required|digits:10|exists:users,Phone_Number',
            'password' => 'required'
        ]);
        if (!Auth::attempt($request->only('Phone_Number', 'password')))
            return response()->json(
                [
                    'message' => 'Envalid Phone_Number Or Password. ',
                ],
                401
            );
        $user = User::where('Phone_Number', $request->Phone_Number)->first();
        $token = $user->createToken('auth_Token')->plainTextToken;
        return response()->json([
            'message' => 'Login Successfuly. ',
            'User'    => $user,
            'Token'   => $token
        ], 201);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout Successfuly. '
        ]);
    }
}
