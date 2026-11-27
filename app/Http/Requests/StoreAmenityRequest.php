<?php

namespace App\Http\Requests;

use App\Models\Amenity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:amenities,name'],
            'category' => ['required', 'string', Rule::in(array_keys(Amenity::CATEGORIES))],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(array_keys(Amenity::STATUS_LABELS))],
        ];
    }
}
