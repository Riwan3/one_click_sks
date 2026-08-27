<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePemanggilanKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'nrp' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'alasan_pemanggilan' => ['nullable', 'string'],
            'tempat' => ['nullable', 'string', 'max:150'],
            'jam_pemanggilan' => ['nullable', 'date_format:H:i'],
            'tanggal_pemanggilan' => ['required', 'date'],
            'nama_manpower' => ['nullable', 'string', 'max:150'],
            'fatigue_periode' => ['nullable', 'string', 'max:20'],
            'ttd_pemanggil' => ['nullable', 'string'],
            'ttd_karyawan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'tanggal_pemanggilan.required' => 'Tanggal pemanggilan wajib diisi.',
        ];
    }
}
