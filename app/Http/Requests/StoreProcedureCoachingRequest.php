<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcedureCoachingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-procedure-evaluation');
    }

    public function rules(): array
    {
        return [
            'mentor_id' => ['required', 'integer', 'exists:employees,id'],
            'weak_material_notes' => ['nullable', 'string', 'max:5000'],
            'coaching_target_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'mentor_id.required' => 'Mentor wajib dipilih.',
            'coaching_target_date.required' => 'Tanggal target coaching wajib diisi.',
        ];
    }
}
