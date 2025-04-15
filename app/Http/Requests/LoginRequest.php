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
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'phone.exists' => 'رقم الهاتف غير مسجل لدينا.',
        ];
    }
}
