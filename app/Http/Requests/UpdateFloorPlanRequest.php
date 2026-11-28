<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFloorPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'floor_area_summary' => ['nullable', 'numeric', 'min:0'],
            'parking_spaces' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'building_id' => [
                'required',
                'integer',
                Rule::exists('buildings', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'floor_id' => [
                'required',
                'integer',
                Rule::exists('floors', 'id')->where(fn ($query) => $query
                    ->where('building_id', $this->input('building_id'))
                    ->whereNull('deleted_at')),
            ],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:10240',
            ],
            'flats' => ['nullable', 'array', 'max:500'],
            'flats.*.id' => ['nullable', 'integer'],
            'flats.*._delete' => ['nullable', 'boolean'],
            'flats.*.flat_number' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^flats\.(\d+)\.flat_number$/', $attribute, $matches)
                        && empty($this->input('flats.'.$matches[1].'._delete'))
                        && trim((string) $value) === '') {
                        $fail('Each active flat must have a flat number.');
                    }
                },
            ],
            'flats.*.name' => ['nullable', 'string', 'max:255'],
            'flats.*.flat_type' => ['nullable', 'string', 'max:32'],
            'flats.*.area' => ['nullable', 'numeric', 'min:0'],
            'flats.*.area_unit' => ['required', 'string', 'in:sqft,sqm'],
            'flats.*.bedroom_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.master_bedroom_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.bathroom_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.balcony_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.kitchen_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.living_room_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.dining_room_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.description' => ['nullable', 'string'],
            'flats.*.notes' => ['nullable', 'string'],
            'flats.*.status' => ['nullable', 'string', 'max:32'],
            'flats.*.rooms' => ['nullable', 'array', 'max:500'],
            'flats.*.rooms.*.id' => ['nullable', 'integer'],
            'flats.*.rooms.*._delete' => ['nullable', 'boolean'],
            'flats.*.rooms.*.name' => ['nullable', 'string', 'max:255'],
            'flats.*.rooms.*.room_type' => ['required', 'string', 'max:32'],
            'flats.*.rooms.*.length' => ['nullable', 'numeric', 'min:0'],
            'flats.*.rooms.*.width' => ['nullable', 'numeric', 'min:0'],
            'flats.*.rooms.*.area' => ['nullable', 'numeric', 'min:0'],
            'flats.*.rooms.*.area_unit' => ['nullable', 'string', 'in:sqft,sqm'],
            'flats.*.rooms.*.floor_level' => ['nullable', 'string', 'max:255'],
            'flats.*.rooms.*.attached_bathroom' => ['nullable', 'boolean'],
            'flats.*.rooms.*.door_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.rooms.*.window_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flats.*.rooms.*.flooring_type' => ['nullable', 'string', 'max:255'],
            'flats.*.rooms.*.ceiling_type' => ['nullable', 'string', 'max:255'],
            'flats.*.rooms.*.wall_finish' => ['nullable', 'string', 'max:255'],
            'flats.*.rooms.*.ac_provision' => ['nullable', 'string', 'max:32'],
            'flats.*.rooms.*.electrical_provision' => ['nullable', 'string', 'max:32'],
            'flats.*.rooms.*.plumbing_provision' => ['nullable', 'string', 'max:32'],
            'flats.*.rooms.*.ventilation_details' => ['nullable', 'string'],
            'flats.*.rooms.*.description' => ['nullable', 'string'],
            'flats.*.rooms.*.notes' => ['nullable', 'string'],
            'flats.*.rooms.*.properties' => ['nullable', 'array'],
            'common_areas' => ['nullable', 'array', 'max:500'],
            'common_areas.*.id' => ['nullable', 'integer'],
            'common_areas.*._delete' => ['nullable', 'boolean'],
            'common_areas.*.name' => ['required', 'string', 'max:255'],
            'common_areas.*.area_type' => ['required', 'string', 'max:32'],
            'common_areas.*.length' => ['nullable', 'numeric', 'min:0'],
            'common_areas.*.width' => ['nullable', 'numeric', 'min:0'],
            'common_areas.*.area' => ['nullable', 'numeric', 'min:0'],
            'common_areas.*.area_unit' => ['required', 'string', 'in:sqft,sqm'],
            'common_areas.*.description' => ['nullable', 'string'],
            'common_areas.*.notes' => ['nullable', 'string'],
        ];
    }
}
