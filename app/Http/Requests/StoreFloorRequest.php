<?php

namespace App\Http\Requests;

use App\Models\Floor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $building = $this->route('building');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('floors', 'name')->where(fn ($query) => $query->where('building_id', $building->getKey())),
            ],
            'floor_number' => [
                'required',
                'integer',
                'min:-10',
                'max:200',
                Rule::unique('floors', 'floor_number')->where(fn ($query) => $query->where('building_id', $building->getKey())),
            ],
            'floor_type' => ['required', 'string', Rule::in(array_keys(Floor::FLOOR_TYPES))],
            'floor_area' => ['nullable', 'numeric', 'min:0'],
            'floor_area_unit' => ['required', Rule::in(array_keys(Floor::FLOOR_AREA_UNITS))],
            'common_area' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'configuration' => [
                'nullable',
                'json',
                function ($attribute, $value, $fail) {
                    if (! is_string($value)) {
                        $fail('The configuration field must be a JSON object or array.');

                        return;
                    }

                    $decoded = json_decode($value);
                    if (! is_array($decoded) && ! is_object($decoded)) {
                        $fail('The configuration field must be a JSON object or array.');
                    }
                },
            ],
            'status' => ['required', 'string', Rule::in(array_keys(Floor::STATUS_LABELS))],
        ];
    }
}
