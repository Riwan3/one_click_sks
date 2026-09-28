<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOdrMasterAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-compliance-report');
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150', Rule::unique('odr_master_area', 'nama')->ignore($this->route('area'))],
            'keterangan' => ['nullable', 'string'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama area wajib diisi.',
            'nama.unique' => 'Gagal: nama area sudah ada atau terjadi error.',
        ];
    }
}
