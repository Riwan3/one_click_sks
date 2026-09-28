<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignBestEmployeeParticipantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageBestEmployee();
    }

    public function rules(): array
    {
        return [
            'select_all' => ['boolean'],
            'user_ids' => ['required_if:select_all,false', 'array'],
            'user_ids.*' => ['integer'],
            'q' => ['nullable', 'string'],
            'jabatan' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'lokasi' => ['nullable', 'string'],
            'force' => ['nullable', 'boolean'],
            'reason' => ['required_if:force,1', 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'Alasan override wajib diisi.',
        ];
    }
}
