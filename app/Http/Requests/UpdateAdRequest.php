<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateAdRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
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
