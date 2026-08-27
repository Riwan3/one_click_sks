<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'posisi' => ['nullable', 'string', 'max:150'],
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2000'],
            'atr_persen' => ['nullable', 'numeric'],
            'work_day' => ['nullable', 'integer', 'min:0'],
            'sakit' => ['nullable', 'integer', 'min:0'],
            'ijin' => ['nullable', 'integer', 'min:0'],
            'alpa' => ['nullable', 'integer', 'min:0'],
            'timesheet_persen' => ['nullable', 'numeric'],
            'hm_ratio' => ['nullable', 'numeric'],
            'hm_jam' => ['nullable', 'numeric'],
            'hm_target' => ['nullable', 'numeric'],
            'fuel_persen' => ['nullable', 'numeric'],
            'fuel_actual' => ['nullable', 'numeric'],
            'fuel_limit' => ['nullable', 'numeric'],
            'pembinaan' => ['nullable', 'integer', 'min:0'],
            'total_poin' => ['nullable', 'numeric'],
            'ranking' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'bulan.required' => 'Bulan wajib diisi.',
            'tahun.required' => 'Tahun wajib diisi.',
        ];
    }
}
