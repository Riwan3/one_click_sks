<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBestEmployeeCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageBestEmployee();
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul kegiatan wajib diisi.',
        ];
    }
}
