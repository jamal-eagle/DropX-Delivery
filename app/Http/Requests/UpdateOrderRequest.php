<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'address_id' => 'nullable|exists:area_user,id',
            'notes' => 'nullable|string',
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
