<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAnalysisTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    /** Angka target (0-999, kosong = ikut roster) divalidasi RosterV2Service agar pesan sama dengan V1. */
    public function rules(): array
    {
        return [
            'roster_id' => ['required', 'integer', 'min:1'],
            'section_id' => ['required', 'integer', 'min:1'],
            'target_siang' => ['nullable'],
            'target_malam' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'roster_id.required' => 'Parameter roster_id wajib diisi dengan benar.',
            'section_id.required' => 'Parameter section_id wajib diisi dengan benar.',
        ];
    }
}
