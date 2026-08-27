<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaktaIntegritasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'tanggal' => ['required', 'date'],
            'tempat' => ['nullable', 'string', 'max:100'],
            'pernyataan' => ['required', 'string'],
            'atasan_nama' => ['nullable', 'string', 'max:150'],
            'mengetahui_nama' => ['nullable', 'string', 'max:150'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_atasan' => ['nullable', 'string'],
            'ttd_mengetahui' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'pernyataan.required' => 'Pernyataan wajib diisi.',
        ];
    }
}
