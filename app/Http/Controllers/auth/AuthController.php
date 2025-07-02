<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Area;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use App\Services\OTPSMSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    protected function formatPhoneNumberToE164($localPhone)
    {
        $localPhone = preg_replace('/\D/', '', $localPhone);

        if (str_starts_with($localPhone, '09')) {
            return '+963' . substr($localPhone, 1);
        }

        if (str_starts_with($localPhone, '9') && strlen($localPhone) == 9) {
            return '+963' . $localPhone;
        }

        if (str_starts_with($localPhone, '+')) {
            return $localPhone;
        }

        return $localPhone;
    }
    protected function normalizePhoneForStorage($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '9639')) {
            return '0' . substr($phone, 3);
        }

        if (str_starts_with($phone, '9') && strlen($phone) === 9) {
            return '0' . $phone;
        }

        return $phone;
    }

    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $normalizedPhone = $this->normalizePhoneForStorage($request->phone);

            $user = User::create([
                'fullname'    => $request->fullname,
                'phone'       => $normalizedPhone,
                'password'    => Hash::make($request->password),
                'is_verified' => false,
                // 'fcm_token' => $request->fcm_token,
            ]);

            $area = Area::firstOrCreate(
                ['city' => $request->city],
                ['neighborhood' => $request->neighborhood]
            );

            $user->areas()->attach($area->id);

            $formattedPhone = $this->formatPhoneNumberToE164($user->phone);
            $otp = rand(100000, 999999);
            Cache::put("otp_{$user->phone}", $otp, now()->addMinutes(5));

            $sent = (new OTPSMSService())->send($formattedPhone, $otp);
            if (! $sent) {
                throw new \Exception("فشل في إرسال كود التحقق");
            }

            // ✅ الاستجابة
            return response()->json([
                'message' => 'تم إنشاء الحساب. الرجاء إدخال رمز التحقق المرسل إلى هاتفك.',
                'user_id' => $user->id
            ], 201);
        });
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|numeric',
        ]);

        $phone = $request->phone;
        $attemptKey = "otp_attempts_{$phone}";
        $attempts = Cache::get($attemptKey, 0);

        if ($attempts >= 3) {
            return response()->json(['message' => 'تم تجاوز عدد المحاولات المسموح بها'], 403);
        }

        $cachedOTP = Cache::get("otp_{$phone}");

        if ($cachedOTP && $cachedOTP == $request->otp) {
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return response()->json(['message' => 'المستخدم غير موجود'], 404);
            }

            $user->update(['is_verified' => true]);
            Cache::forget("otp_{$phone}");
            Cache::forget($attemptKey);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم التحقق من الحساب بنجاح',
                'token' => $token,
                'user' => $user
            ]);
        }

        // زيادة المحاولة إذا كانت خاطئة
        Cache::put($attemptKey, $attempts + 1, now()->addMinutes(10));

        return response()->json(['message' => 'رمز غير صحيح. تبقّى ' . (2 - $attempts) . ' محاولات.'], 401);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة.'
            ], 401);
        }

        if (! $user->is_verified) {
            return response()->json([
                'status' => false,
                'message' => 'هذا الحساب لم يتم التحقق منه يرجى تاكيد الحساب '
            ]);
        }
        if (! $user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'هذا الحساب قيد الايقاف يرجى التواصل مع الشركة من اجل ذلك !!!! '
            ]);
        }

        $user = User::where('phone', $request->phone)->FirstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ], 204);
    }

    public function index()
    {
        $areas = Area::select('id', 'city', 'neighborhood')->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة المناطق',
            'data' => $areas
        ], 200);
    }

    public function userInfo()
    {
        $user = auth()->user();
        $userWithArea = $user->areas;
        return response()->json([
            'user' => $user,
            'area' => $userWithArea
        ], 200);
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
        $title = 'تم تحديث بياناتك';
        $body  = '✅ تم تعديل معلومات حسابك بنجاح.';
        $data  = ['type' => 'profile_update'];

        if ($user->fcm_token) {
            app(FirebaseNotificationService::class)
                ->sendToToken($user->fcm_token, $title, $body, $data, $user->id);
        }

        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);



        return response()->json([
            'message' => 'تم تحديث بيانات المستخدم بنجاح.',
            'user' => $user->load('areas'),
        ], 201);
    }
}
