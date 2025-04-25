<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
