<?php

namespace App\Http\Requests;

/** Aksi tulis Master Unit Fleet. Akses sama seperti Setting Manpower. */
class FleetMasterRequest extends ManpowerV201Request
{
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50'],
            'unit_type' => ['nullable', 'string', 'max:10'],
            'category' => ['nullable', 'string', 'max:120'],
            'default_group' => ['nullable', 'string', 'max:100'],
            'default_area' => ['nullable', 'string', 'max:100'],
            'default_front' => ['nullable', 'string', 'max:100'],
            'default_refueling_time' => ['nullable', 'string', 'max:50'],
            'default_distance' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*.name' => ['required_with:categories', 'string'],
            'categories.*.type' => ['required_with:categories', 'string'],
        ];
    }

    public function unitFields(): array
    {
        return $this->only([
            'code', 'unit_type', 'category', 'default_group', 'default_area',
            'default_front', 'default_refueling_time', 'default_distance',
        ]);
    }
}
