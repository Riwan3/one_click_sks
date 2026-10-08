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
            'parking_code' => ['nullable', 'string', 'max:1'],
            'is_active' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*.csa' => ['nullable', 'string'],
            'categories.*.name' => ['required_with:categories', 'string'],
            'categories.*.type' => ['required_with:categories', 'string'],
        ];
    }

    public function unitFields(): array
    {
        return $this->only(['code', 'unit_type', 'category', 'parking_code']);
    }
}
