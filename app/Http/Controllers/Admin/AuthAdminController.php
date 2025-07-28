<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthAdminController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة.'
            ], 401);
        }

        if ($user->user_type !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'هذا المستخدم غير مخوّل لتسجيل الدخول كأدمن.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
        ], 200);
    }

    public function toggleActiveStatus($userId)
    {
        $user = User::findOrFail($userId);

        if (auth()->user()->user_type !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بتنفيذ هذا الإجراء.',
            ], 403);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث حالة الحساب بنجاح.',
            'is_active' => $user->is_active,
        ], 200);
    }

    public function getCustomers()
    {
        if (auth()->user()->user_type !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بعرض المستخدمين.',
            ], 403);
        }

        $customers = User::where('user_type', 'customer')
            ->select('id', 'fullname', 'phone', 'location_text', 'is_active', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب كل الزبائن  بنجاح',
            'data' => $customers,
        ]);
    }

    public function adminProfile(Request $request)
    {
        $admin = $request->user();

        if (!$admin || $admin->user_type !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'معلومات الأدمن',
            'data' => [
                'id'       => $admin->id,
                'fullname' => $admin->fullname,
                'phone'    => $admin->phone,
                'created_at' => $admin->created_at,
            ],
        ]);
    }
}
