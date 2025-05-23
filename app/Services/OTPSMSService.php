<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OTPSMSService
{
    public function send($phoneNumber, $otp): bool
    {
        $instanceId = config('services.hypersender_sms.device_id');
        $url = "https://app.hypersender.com/api/sms/v1/{$instanceId}/send-message";

        $response = Http::withOptions([
            'verify' => false, // مؤقتًا لتجاوز مشكلة SSL على ويندوز
        ])
            ->withToken(config('services.hypersender_sms.token'))
            ->post($url, [
                'to' => $phoneNumber, // ✅ اسم الحقل الصحيح
                'content' => "رمز التحقق الخاص بهذا الرقم هو: $otp", // ✅ اسم الحقل الصحيح
            ]);

        // تسجيل الرد الكامل للمراجعة
        Log::info('Hypersender Final Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->successful();
    }
}
