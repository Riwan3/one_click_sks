<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHmDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'string', 'max:10'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'unit' => ['required', 'string', 'max:50'],
            'dept' => ['nullable', 'string', 'max:100'],
            'hm_start' => ['required', 'numeric', 'min:0'],
            'hm_stop' => ['required', 'numeric', 'gte:hm_start'],
            'activity' => ['nullable', 'string', 'max:150'],
            'parking' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'unit.required' => 'Unit wajib diisi.',
            'hm_stop.gte' => 'HM Stop tidak boleh lebih kecil dari HM Start.',
        ];
    }
}
