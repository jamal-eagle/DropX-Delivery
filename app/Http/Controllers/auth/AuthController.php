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

    const OTP_TTL_MINUTES         = 5;
    const OTP_ATTEMPT_TTL_MINUTES = 10;
    const OTP_RESEND_TTL_MINUTES  = 15;
    const MAX_OTP_ATTEMPTS        = 3;
    const MAX_OTP_RESEND          = 3;

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

            $phone = $this->normalizePhoneForStorage($request->phone);

            $user = User::create([
                'fullname'    => $request->fullname,
                'phone'       => $phone,
                'password'    => Hash::make($request->password),
                'is_verified' => false,
                'fcm_token'   => $request->fcm_token,
            ]);

            $area = Area::firstOrCreate(
                ['city' => $request->city],
                ['neighborhood' => $request->neighborhood]
            );
            $user->areas()->attach($area->id);

            $otpKey      = "otp_{$phone}";
            $attemptKey  = "otp_attempts_{$phone}";
            $resendKey   = "otp_resend_{$phone}";

            Cache::forget($otpKey);
            Cache::forget($attemptKey);
            Cache::put($resendKey, 1, now()->addMinutes(self::OTP_RESEND_TTL_MINUTES));

            $otp = rand(100000, 999999);
            Cache::put($otpKey, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));

            $sent = (new OTPSMSService())->send(
                $this->formatPhoneNumberToE164($phone),
                $otp
            );

            if (! $sent) {
                throw new \Exception('فشل في إرسال كود التحقق');
            }

            return response()->json([
                'message' => 'تم إنشاء الحساب. الرجاء إدخال رمز التحقق المرسل إلى هاتفك.',
                'user_id' => $user->id,
            ], 201);
        });
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|numeric',
        ]);

        $phone      = $this->normalizePhoneForStorage($request->phone);
        $otpKey     = "otp_{$phone}";
        $attemptKey = "otp_attempts_{$phone}";
        $resendKey  = "otp_resend_{$phone}";

        $attempts = Cache::get($attemptKey, 0);
        if ($attempts >= self::MAX_OTP_ATTEMPTS) {
            return response()->json([
                'message' => 'تم تجاوز عدد المحاولات المسموح بها، الرجاء المحاولة بعد قليل.'
            ], 403);
        }

        $cachedOTP = Cache::get($otpKey);
        if (! $cachedOTP) {
            return response()->json([
                'message' => 'انتهت صلاحية الرمز، اطلب رمزًا جديدًا.'
            ], 400);
        }

        if ($cachedOTP == $request->otp) {
            $user = User::where('phone', $phone)->first();
            if (! $user) {
                return response()->json(['message' => 'المستخدم غير موجود'], 404);
            }

            $user->update(['is_verified' => true]);

            Cache::forget($otpKey);
            Cache::forget($attemptKey);
            Cache::forget($resendKey);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم التحقق من الحساب بنجاح',
                'token'   => $token,
                'user'    => $user,
            ], 200);
        }

        Cache::put($attemptKey, $attempts + 1, now()->addMinutes(self::OTP_ATTEMPT_TTL_MINUTES));

        return response()->json([
            'message' => 'رمز غير صحيح. تبقّى ' .
                max(0, self::MAX_OTP_ATTEMPTS - ($attempts + 1)) .
                ' محاولات.'
        ], 401);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $phone  = $this->normalizePhoneForStorage($request->phone);
        $user   = User::where('phone', $phone)->first();

        if ($user->is_verified) {
            return response()->json([
                'status'  => false,
                'message' => 'تم التحقق من الحساب مسبقاً.',
            ], 400);
        }

        $otpKey    = "otp_{$phone}";
        $resendKey = "otp_resend_{$phone}";

        $resendCount = Cache::get($resendKey, 0);
        if ($resendCount >= self::MAX_OTP_RESEND) {
            return response()->json([
                'status'  => false,
                'message' => 'لقد تجاوزت الحد الأقصى لطلبات الكود، الرجاء المحاولة لاحقًا.',
            ], 429);
        }

        // توليد كود جديد
        Cache::forget($otpKey);
        $otp = rand(100000, 999999);
        Cache::put($otpKey, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));

        $sent = (new OTPSMSService())->send(
            $this->formatPhoneNumberToE164($phone),
            $otp
        );

        if (! $sent) {
            return response()->json([
                'status'  => false,
                'message' => 'فشل في إرسال كود التحقق.',
            ], 500);
        }

        Cache::put($resendKey, $resendCount + 1, now()->addMinutes(self::OTP_RESEND_TTL_MINUTES));

        return response()->json([
            'status'  => true,
            'message' => 'تم إرسال كود التحقق بنجاح.',
        ], 200);
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

        if ($request->filled('fcm_token') && $request->fcm_token !== $user->fcm_token) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }
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
