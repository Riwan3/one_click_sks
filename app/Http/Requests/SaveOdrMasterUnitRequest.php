<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveOdrMasterUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-compliance-report');
    }

    public function rules(): array
    {
        return [
            'kode' => ['nullable', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:150'],
            'area' => ['nullable', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['nama.required' => 'Nama unit wajib diisi.'];
    }
}
