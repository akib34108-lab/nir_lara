<?php

namespace App\Http\Requests;

use App\Models\Building;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:buildings,code'],
            'building_type' => ['required', 'string', Rule::in(Building::BUILDING_TYPES)],
            'structure_type' => ['required', 'string', Rule::in(Building::STRUCTURE_TYPES)],
            'total_floors' => ['required', 'integer', 'min:0'],
            'total_flats' => ['required', 'integer', 'min:0'],
            'basement_count' => ['required', 'integer', 'min:0'],
            'elevator_count' => ['required', 'integer', 'min:0'],
            'parking_capacity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(array_keys(Building::STATUS_LABELS))],
            'description' => ['nullable', 'string'],
        ];
    }
}
