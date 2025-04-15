<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
{
    $user = User::create([
        'fullname'   => $request->fullname,
        'phone'      => $request->phone,
        'password'   => Hash::make($request->password),
        //'fcm_token'  => $request->fcm_token,
    ]);

    $user->areas()->attach($request->area_id);


    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user'    => $user,
        'message' => 'تم انشاء الحساب بنجاح',
        //'token'   => $token,

    ]);
}


public function login(LoginRequest $request)
{
    $user = User::where('phone', $request->phone)->first();

    if (!Auth::attempt($request->only('phone','password'))) {
        return response()->json([
            'status' => false,
            'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة.'
        ], 401);
    }

    $user = User::where('phone',$request->phone)->FirstOrFail();
    $token = $user->createToken('auth_token')->plainTextToken;
    return response()->json([
        'user' => $user,
        'message' => 'تم تسجيل الدخول بنجاح',
        'token' => $token,
    ]);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->json([
        'status' => true,
        'message' => 'تم تسجيل الخروج بنجاح',
    ]);
}

}
