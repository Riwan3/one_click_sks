<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceMonthlyRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'digits:4'],
            'dept' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'posisi' => ['nullable', 'string', 'max:150'],
            'comp' => ['nullable', 'string', 'max:50'],
            'd' => ['nullable', 'integer', 'min:0'],
            'n' => ['nullable', 'integer', 'min:0'],
            'io' => ['nullable', 'integer', 'min:0'],
            'nc' => ['nullable', 'integer', 'min:0'],
            'l' => ['nullable', 'integer', 'min:0'],
            't' => ['nullable', 'integer', 'min:0'],
            'o' => ['nullable', 'integer', 'min:0'],
            's' => ['nullable', 'integer', 'min:0'],
            'i' => ['nullable', 'integer', 'min:0'],
            'a' => ['nullable', 'integer', 'min:0'],
            'k' => ['nullable', 'integer', 'min:0'],
            'p' => ['nullable', 'integer', 'min:0'],
            'r' => ['nullable', 'integer', 'min:0'],
            'h' => ['nullable', 'integer', 'min:0'],
            'nr' => ['nullable', 'integer', 'min:0'],
            'atr_percentage' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'bulan.required' => 'Bulan wajib diisi.',
            'tahun.required' => 'Tahun wajib diisi.',
        ];
    }
}
