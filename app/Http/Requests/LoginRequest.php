<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
            'fcm_token'  => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'phone.exists' => 'رقم الهاتف غير مسجل لدينا.',
        ];
    }
}
