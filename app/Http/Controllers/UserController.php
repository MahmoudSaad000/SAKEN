<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\OTPNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        $locale = $request->header('lang', 'ar');
        $user->notify(new OTPNotification('phoneVerify', $locale));

        return response()->json([
            'message' => 'User Registered Successfully.',
            'data' => [
                'user' => [
                    $user
                ]
            ],
            'status_code' => 201
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
                    'status_code' => 400
                ],
                400
            );
        }


        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }


        if (is_null($user->phone_verified_at)) {
            return response()->json([
                'message' => 'Phone number is not verified.',
                'need_verification' => true,
                'status_code' => 403
            ], 403);
        }


        if ($user->is_approved != 1) {
            return response()->json([
                'message' => 'Account not approved.',
                'status_code' => 403
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successfully.',
            'data' => [
                'user' => $user,
            ],
            'Token' => $token,
            'status_code' => 200
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Successfuly. ',
            'status_code' => 200
        ], 200);
    }

    public function getAllUsersis_approved_false()
    {
        $users = User::where('is_approved', false)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'All users retrieved successfully.',
            'data' => [
                'users' => $users,
            ],
            'count' => $users->count(),
            'status_code' => 200
        ], 200);
    }

    public function getAllUsersis_approved_true()
    {
        $users = User::where('is_approved', true)->orderBy('created_at', 'asc')->get();

        return response()->json([
            'message' => 'All users approved successfully.',
            'data' => [
                'users' => $users,
            ],
            'count' => $users->count(),
            'status_code' => 200
        ], 200);
    }

    public function update(UpdateUserRequest $request, $id)
    {

        $currentUser = Auth::user();
        if (! $currentUser) {
            return response()->json([
                'message' => 'User not authenticated',
                'status_code' => 401
            ], 401);
        }

        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'message' => 'User not found',
                'status_code' => 404
            ], 404);
        }

        if ($user->id != $currentUser->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'status_code' => 403
            ], 403);
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

        return response()->json([
            'data' => [
                'user' => $user
            ],
            'status_code' => 200
        ], 200);
    }

    public function approveUser($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
                'status_code' => 404
            ], 404);
        }

        $user->update(['is_approved' => '1']);

        // هنا يمكنك إرسال إشعار أو email للمستخدم
        // $this->sendApprovalNotification($user);

        return response()->json([
            'message' => 'User approved successfully.',
            'data' => [
                'user' => $user,
            ],
            'status_code' => 200
        ], 200);
    }

    public function rejectUser($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        // $user->update(['is_approved' => '2']);

        // إرسال إشعار الرفض
        // $this->sendRejectionNotification($user);

        return response()->json([
            'message' => 'User rejected successfully',
            'data' => [
                'user' => $user,
            ],
            'status_code' => 200
        ], 200);
    }

    public function approveAllUsers(Request $request)
    {
        // تحديث جميع المستخدمين غير الموافق عليهم مباشرة
        $updatedCount = User::where('is_approved', false)->update([
            'is_approved' => true,
        ]);

        if ($updatedCount === 0) {
            return response()->json([
                'message' => 'No pending users to approve',
                'status_code' => 200
            ], 200);
        }

        return response()->json([
            'message' => 'All pending users approved successfully',
            'approved_count' => $updatedCount,
            'status_code' => 200
        ], 200);
    }

    public function rejectAllUsers(Request $request)
    {

        $totalCount = User::count();

        if ($totalCount === 0) {
            return response()->json([
                'message' => 'No users to rejected',
                'status_code' => 200
            ], 200);
        }

        User::query()->delete();

        return response()->json([
            'message' => 'All users rejected successfully',
            'deleted_count' => $totalCount,
            'status_code' => 200
        ], 200);
    }

    public function GetUser()
    {
        $user_id = Auth::user()->id;
        $userData = User::findorfail($user_id);

        return new UserResource($userData);

        // return all users//
        // $userData = User::with('profile')->get();
        // return  UserResource::collection($userData);
    }

    public function deleteMyAccount(Request $request)
    {
        $user = Auth::user();

        // تحقق من إدخال كلمة المرور
        $request->validate([
            'password' => 'required',
        ]);

        // تأكيد كلمة المرور قبل الحذف
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Incorrect password',
                'status_code' => 401
            ], 401);
        }

        // إذا كلمة المرور صحيحة → احذف الحساب
        $user->delete();

        return response()->json([
            'message' => 'Your account has been deleted successfully.',
            'status_code' => 200
        ], 200);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'otp' => 'required'
        ]);

        $otpService = new Otp;

        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }


        $verification = $otpService->validate($request->phone_number, $request->otp);


        if (! $verification->status) {


            $user->otp_attempts += 1;
            $user->save();


            if ($user->otp_attempts >= 5) {

                $user->delete();

                return response()->json([
                    'message' => 'Too many invalid OTP attempts. Account has been deleted.',
                    'status_code' => 410
                ], 410);
            }

            return response()->json([
                'message' => 'OTP is invalid or expired.',
                'remaining_attempts' => 5 - $user->otp_attempts,
                'status_code' => 422
            ], 422);
        }

        $user->otp_attempts = 0;
        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Phone number verified successfully.',
            'status_code' => 200
        ], 200);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number'
        ]);


        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }


        $locale = $request->header('lang', 'ar');


        $user->notify(new OTPNotification('phoneVerify', $locale));

        return response()->json([
            'message' => 'OTP resent successfully.',
            'status_code' => 200
        ], 200);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number'
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }

        $locale = $request->header('lang', 'ar');

        $user->notify(new OTPNotification('passwordReset', $locale));

        return response()->json([
            'message' => 'OTP sent for password reset.',
            'status_code' => 200
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404
            ], 404);
        }


        if (is_null($user->phone_verified_at)) {
            return response()->json([
                'message' => 'OTP verification required.',
                'status_code' => 403
            ], 403);
        }


        $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Password reset successfully.',
            'status_code' => 200
        ], 200);
    }
}
