<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Area;
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
        'token'   => $token,

    ],201);
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
    ],200);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();
    return response()->json([
        'status' => true,
        'message' => 'تم تسجيل الخروج بنجاح',
    ],204);
}

public function index()
    {
        $areas = Area::select('id', 'city', 'neighborhood')->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة المناطق',
            'data' => $areas
        ],200);
    }

public function userInfo()
    {
        $user = auth()->user();
        $userWithArea = $user->areas;
        return response()->json([
            'user'=>$user ,
            'area'=>$userWithArea
        ],200);
    }

public function updateUserInfo(Request $request)
    {
        $request->validate([
            'fullname' => 'nullable|string|max:75',
            'phone' => 'nullable|string|max:15|unique:users,phone,' . auth()->id(),
            'city' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();

        if ($request->filled('fullname')) {
            $user->fullname = $request->fullname;
        }

        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        if ($request->filled('city') && $request->filled('neighborhood')) {
            if ($user->areas()->count() >= 5) {
                return response()->json([
                    'message' => 'لا يمكنك إضافة أكثر من 5 مناطق.',
                ], 422);
            }

            $area = Area::firstOrCreate([
                'city' => $request->city,
                'neighborhood' => $request->neighborhood,
            ]);

            if (!$user->areas()->where('area_id', $area->id)->exists()) {
                $user->areas()->attach($area->id);
            }
        }

        return response()->json([
            'message' => 'تم تحديث بيانات المستخدم بنجاح.',
            'user' => $user->load('areas'),
        ],201);
    }



}
