<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
