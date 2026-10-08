<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveMoncuEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:20'],
            'hiring_date' => ['required', 'date_format:Y-m-d'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'plan_interval_days' => ['nullable', 'regex:/^\d+$/'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required' => 'NRP tidak valid.',
            'hiring_date.required' => 'Hiring Date wajib diisi (YYYY-MM-DD).',
            'hiring_date.date_format' => 'Hiring Date wajib diisi (YYYY-MM-DD).',
            'plan_interval_days.regex' => 'Interval PLAN harus 1-365 hari (kosongkan untuk memakai aturan jabatan).',
        ];
    }
}
