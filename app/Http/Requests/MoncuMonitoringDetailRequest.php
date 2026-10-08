<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoncuMonitoringDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewRosterV2();
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:20'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.*' => 'Parameter nrp wajib diisi dengan benar.',
            'year.*' => 'Parameter year tidak valid.',
        ];
    }
}
