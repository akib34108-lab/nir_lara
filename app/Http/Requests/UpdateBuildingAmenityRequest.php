<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['present', 'nullable', 'integer', 'min:0'],
            'notes' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }
}
