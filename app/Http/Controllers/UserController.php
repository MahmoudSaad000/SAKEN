<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            'User' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits:10|exists:users,phone_number',
            'password' => 'required',
        ]);
        if (! Auth::attempt($request->only('phone_number', 'password'))) {
            return response()->json(
                [
                    'message' => 'Envalid Phone_Number Or Password. ',
                ],
                401
            );
        }

        if ($user = User::where('phone_number', $request->phone_number)->where('is_approved', "1")->first()) {
            $token = $user->createToken('auth_Token')->plainTextToken;

            return response()->json([
                'message' => 'Login Successfuly. ',
                'User' => $user,
                'Token' => $token,
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
            'message' => 'Logout Successfuly. ',
        ]);
    }

    public function getAllUsersis_approved_false()
    {
        $users = User::where("is_approved", false)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'All users retrieved successfully.',
            'users' => $users,
            'count' => $users->count(),
        ], 200);
    }

    public function getAllUsersis_approved_true()
    {
        $users = User::where("is_approved", true)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'All users retrieved successfully.',
            'users' => $users,
            'count' => $users->count()
        ], 200);
    }

    public function update(UpdateUserRequest $request, $id)
    {

        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }


        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        if ($user->id != $currentUser->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }



        if ($request->hasFile('picture')) {

            if ($user->picture && Storage::disk('public')->exists($user->picture)) {
                Storage::disk('public')->delete($user->picture);
            }

            $picturePath = $request->file('picture')->store('profile_pictures', 'public');
            $validated['picture'] = $picturePath;
        }


        if ($request->hasFile('id_card_image')) {

            if ($user->id_card_image && Storage::disk('public')->exists($user->id_card_image)) {
                Storage::disk('public')->delete($user->id_card_image);
            }

            $idCardPath = $request->file('id_card_image')->store('id_cards', 'public');
            $validated['id_card_image'] = $idCardPath;
        }


        $user->update($validated);


        return response()->json($user, 200);
    }

    public function approveUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update(['is_approved' => '1']);

        // هنا يمكنك إرسال إشعار أو email للمستخدم
        // $this->sendApprovalNotification($user);

        return response()->json([
            'message' => 'User approved successfully.',
            'user' => $user
        ], 200);
    }

    public function rejectUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        // $user->update(['is_approved' => '2']);

        // إرسال إشعار الرفض
        // $this->sendRejectionNotification($user);

        return response()->json([
            'message' => 'User rejected successfully'
        ], 200);
    }

    public function approveAllUsers(Request $request)
    {
        // تحديث جميع المستخدمين غير الموافق عليهم مباشرة
        $updatedCount = User::where('is_approved', false)->update([
            'is_approved' => true,
        ]);

        if ($updatedCount === 0) {
            return response()->json(['message' => 'No pending users to approve'], 200);
        }

        return response()->json([
            'message' => 'All pending users approved successfully',
            'approved_count' => $updatedCount
        ], 200);
    }

    public function rejectAllUsers(Request $request)
    {

        $totalCount = User::count();

        if ($totalCount === 0) {
            return response()->json(['message' => 'No users to rejected'], 200);
        }


        User::query()->delete();

        return response()->json([
            'message' => 'All users rejected successfully',
            'deleted_count' => $totalCount
        ], 200);
    }

    public function GetUser()
    {
        $user_id = Auth::user()->id;
        $userData = User::findorfail($user_id);
        return new UserResource($userData);

        //return all users//
        // $userData = User::with('profile')->get();
        // return  UserResource::collection($userData);
    }

    public function deleteMyAccount(Request $request)
    {
        $user = Auth::user();

        // تحقق من إدخال كلمة المرور
        $request->validate([
            'password' => 'required'
        ]);

        // تأكيد كلمة المرور قبل الحذف
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Incorrect password'
            ], 401);
        }

        // إذا كلمة المرور صحيحة → احذف الحساب
        $user->delete();

        return response()->json([
            'message' => 'Your account has been deleted successfully.'
        ], 200);
    }
}
