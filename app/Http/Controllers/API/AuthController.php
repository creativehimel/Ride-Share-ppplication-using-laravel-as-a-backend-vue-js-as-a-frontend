<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|min:11',
        ]);

        $user = User::findOrCreate([
            'phone' => $request->phone
        ]);

        if(!$user){
            return response()->json([
                'message' => 'Could not process a user with this phone number'
            ], 401);
        }

        $user->notify(new LoginNeedsVerification());

        return response()->json([
            'message' => 'Login code sent'
        ], 200);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|min:11',
            'login_code' => 'required|numeric|between:111111,999999'
        ]);

        $user = User::where('phone', $request->phone)
                ->where('login_code', $request->login_code)
                ->first();

        if ($user) {
            $user->update(['login_code' => null]);
            $token = $user->createToken($request->login_code)->plainTextToken;
            return response()->json([
                'message' => 'Login code verified',
                'token' => $token
            ], 200);
        }

        if(!$user){
            return response()->json([
                'message' => 'Invalid login code'
            ], 401);
        }

    }
}
