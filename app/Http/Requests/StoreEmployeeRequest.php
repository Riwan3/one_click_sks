<?php

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:20', 'unique:employees,nrp'],
            'name' => ['required', 'string', 'max:150'],
            'jabatan_ss6' => ['nullable', 'string', 'max:100'],
            'jabatan_roster' => ['nullable', 'string', 'max:100'],
            'status_karyawan' => ['required', new Enum(EmployeeStatus::class)],
            'lokasi_kerja' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:50'],
            'no_telepon' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'efektif_bekerja' => ['nullable', 'date'],
            'input_here' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required' => 'NRP wajib diisi.',
            'nrp.unique' => 'NRP sudah terdaftar untuk karyawan lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'status_karyawan.required' => 'Status karyawan wajib dipilih.',
        ];
    }
}
