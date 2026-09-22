<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['اطلاعات احراز هویت اشتباه است.']]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => ['حساب کاربری غیرفعال است.']]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $cookie = Cookie::make(
            'auth_token',
            $token,
            60 * 24, // 24 hours
            '/',
            null,
            true, // secure (HTTPS)
            true, // httpOnly
            false, // raw
            'strict' // SameSite
        );

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
        ])->withCookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = Cookie::make('auth_token', '', -1, '/', null, true, true, false, 'strict');

        return response()->json(['message' => 'خروج موفقیت‌آمیز'])->withCookie($cookie);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
        ]);
    }
}