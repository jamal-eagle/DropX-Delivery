<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

            return [
                'meals' => 'required|array',
                'meals.*.id' => 'required|exists:meals,id',
                'meals.*.quantity' => 'required|integer|min:1',
                'delivery_address' => 'required|string',
                'notes' => 'nullable|string',
                'promo_code' => 'nullable|string',
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
