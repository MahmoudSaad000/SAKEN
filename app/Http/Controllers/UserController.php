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
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'phone_number' => $validated['phone_number'],
            'date_of_birth' => $validated['date_of_birth'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ];


        if ($request->hasFile('picture')) {
            $picturePath = $request->file('picture')->store('profile_pictures', 'public');
            $userData['picture'] = $picturePath;
        }

        if ($request->hasFile('id_card_image')) {
            $idCardPath = $request->file('id_card_image')->store('id_cards', 'public');
            $userData['id_card_image'] = $idCardPath;
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
            'phone_number' => 'required|digits:10|exists:users,phone_number',
            'password' => 'required'
        ]);
        if (!Auth::attempt($request->only('phone_Number', 'password')))
            return response()->json(
                [
                    'message' => 'Envalid Phone_Number Or Password. ',
                ],
                401
            );

        if ($user = User::where('phone_Number', $request->phone_Number)->where('is_approved', 'true')->first()) {
            $token = $user->createToken('auth_Token')->plainTextToken;
            return response()->json([
                'message' => 'Login Successfuly. ',
                'User'    => $user,
                'Token'   => $token
            ], 201);
        } else {
            return response()->json([
                'message' => 'Account not approved. ',
            ], 403);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout Successfuly. '
        ]);
    }

    public function getAllUsers()
    {
        $users = User::all();

        return response()->json([
            'message' => 'All users retrieved successfully.',
            'users' => $users,
            'count' => $users->count()
        ], 200);
    }
}
