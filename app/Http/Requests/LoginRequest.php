<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $validator->errors()
        ], 401));
    }


}
