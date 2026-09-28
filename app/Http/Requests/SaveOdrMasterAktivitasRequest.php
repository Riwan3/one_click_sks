<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveOdrMasterAktivitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-compliance-report');
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:200'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'dokumen' => ['nullable', 'array'],
            'dokumen.*' => ['string', 'max:255', 'exists:prosedur_kerja,file_dokumen'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['nama.required' => 'Nama aktivitas wajib diisi.'];
    }
}
