<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuildingAmenitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amenity_ids' => ['required', 'array', 'min:1'],
            'amenity_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('amenities', 'id')->where('status', 'active'),
            ],
            'quantity' => ['sometimes', 'array'],
            'quantity.*' => ['nullable', 'integer', 'min:0'],
            'notes' => ['sometimes', 'array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
