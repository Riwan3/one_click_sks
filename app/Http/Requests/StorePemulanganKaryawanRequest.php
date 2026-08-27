<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePemulanganKaryawanRequest extends FormRequest
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
            'departemen' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'indikasi_fatigue' => ['nullable', 'string'],
            'jam_tidur_kurang_4jam' => ['nullable', 'boolean'],
            'fatigue_berat' => ['nullable', 'boolean'],
            'deskripsi_penyebab' => ['nullable', 'string'],
            'alamat_pengantaran' => ['nullable', 'string'],
            'penanggungjawab_pengantaran' => ['nullable', 'string', 'max:150'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_pengawas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ];
    }
}
