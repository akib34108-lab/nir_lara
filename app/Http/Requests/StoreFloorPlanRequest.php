<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFloorPlanRequest extends FormRequest
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
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:10240',
            ],
        ];
    }
}
