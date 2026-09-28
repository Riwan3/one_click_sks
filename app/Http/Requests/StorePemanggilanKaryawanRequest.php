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
            'no_surat' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable'],
            'nrp' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'alasan_pemanggilan' => ['required', 'string'],
            'tempat' => ['nullable', 'string', 'max:150'],
            'jam_pemanggilan' => ['nullable', 'string', 'max:20'],
            'tanggal_pemanggilan' => ['required', 'date'],
            'ttd_pemanggilan' => ['nullable', 'string', 'max:150'],
            'nama_manpower' => ['nullable', 'string', 'max:150'],
            'fatigue_periode' => ['nullable', 'string', 'max:20'],
            'ttd_pemanggil' => ['nullable', 'string'],
            'ttd_karyawan' => ['nullable', 'string'],
            'eviden_1' => ['nullable'],
            'eviden_2' => ['nullable'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required' => 'NRP karyawan wajib diisi.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'alasan_pemanggilan.required' => 'Alasan pemanggilan wajib diisi.',
            'tanggal_pemanggilan.required' => 'Tanggal pemanggilan wajib diisi.',
        ];
    }
}
