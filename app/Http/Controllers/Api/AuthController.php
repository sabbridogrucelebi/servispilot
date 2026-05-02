<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::with('company')
            ->where('email', $request->email)
            ->orWhere('username', $request->email)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Girdiğiniz bilgiler hatalı.'
            ], 401);
        }
        
        if (! $user->is_active) {
            return response()->json([
                'message' => 'Hesabınız pasif durumda.'
            ], 401);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'user_type' => $user->user_type,
                'company_id' => $user->company_id,
                'company_name' => $user->company ? $user->company->name : 'ServisPilot',
                'student_name' => $user->user_type === 'customer_portal' 
                    ? (\App\Models\PilotCell\PcStudent::where('parent_user_id', $user->id)->orWhere('parent2_user_id', $user->id)->value('name')) 
                    : null,
                'is_company_admin' => $user->isCompanyAdmin(),
                'permissions' => $user->permissions ? $user->permissions->pluck('key')->toArray() : [],
                'permissions_updated_at' => $user->permissions_updated_at,
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('company');
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'user_type' => $user->user_type,
                'company_id' => $user->company_id,
                'company_name' => $user->company ? $user->company->name : 'ServisPilot',
                'student_name' => $user->user_type === 'customer_portal' 
                    ? (\App\Models\PilotCell\PcStudent::where('parent_user_id', $user->id)->orWhere('parent2_user_id', $user->id)->value('name')) 
                    : null,
                'is_company_admin' => $user->isCompanyAdmin(),
                'permissions' => $user->permissions ? $user->permissions->pluck('key')->toArray() : [],
                'permissions_updated_at' => $user->permissions_updated_at,
                'profile_photo_url' => $user->profile_photo ? url('storage/' . $user->profile_photo) : null,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Başarıyla çıkış yapıldı.'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mevcut şifreniz yanlış.',
                'errors' => [
                    'current_password' => ['Mevcut şifreniz yanlış.']
                ]
            ], 422);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Şifreniz başarıyla güncellendi.'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Hesap bilgileriniz başarıyla güncellendi.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
                'is_company_admin' => $user->isCompanyAdmin(),
            ]
        ]);
    }

    public function updatePushToken(Request $request)
    {
        $request->validate([
            'push_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->expo_push_token = $request->push_token;
        $user->save();

        return response()->json([
            'message' => 'Push token başarıyla güncellendi.'
        ]);
    }
}
