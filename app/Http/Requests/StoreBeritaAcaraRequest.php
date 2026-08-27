<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeritaAcaraRequest extends FormRequest
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
            'kronologi' => ['required', 'string'],
            'diketahui_oleh_id' => ['nullable', 'integer', 'exists:employees,id'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_atasan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'kronologi.required' => 'Kronologi wajib diisi.',
        ];
    }
}
