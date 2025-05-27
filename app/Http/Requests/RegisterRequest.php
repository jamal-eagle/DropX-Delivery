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
            'city'          => 'required|string|max:100',
            'neighborhood'  => 'nullable|string|max:200',
            'fcm_token'     => 'nullable|string',
        ];
    }
}
