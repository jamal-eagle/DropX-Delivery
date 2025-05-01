<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegisterRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullname'      => 'required|string|max:75',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'area_id'       => 'required|exists:areas,id',
            'fcm_token'     => 'nullable|string',
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
