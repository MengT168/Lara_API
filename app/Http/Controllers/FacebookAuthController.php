<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class FacebookAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            $user = User::updateOrCreate(
                ['email' => $facebookUser->getEmail()],
                [
                    'name' => $facebookUser->getName(),
                    'password' => Hash::make(Str::random(16)),
                    'is_admin' => 0,
                ]
            );

            Auth::login($user);
            $token = $user->createToken('auth-token')->plainTextToken;
            return view('auth.callback', [
                'token' => $token,
                'user' => json_encode($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Laravel Socialite Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
