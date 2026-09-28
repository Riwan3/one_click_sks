<?php

namespace App\Http\Requests;

use App\Enums\OdrIncidentActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOdrIncidentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-compliance-report');
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::enum(OdrIncidentActionType::class)],
            'uraian_tindakan' => ['required', 'string'],
            'pic' => ['nullable', 'string', 'max:150'],
            'evidence' => ['nullable', 'array'],
            'evidence.*' => ['file', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return ['uraian_tindakan.required' => 'Uraian tindakan wajib diisi.'];
    }
}
