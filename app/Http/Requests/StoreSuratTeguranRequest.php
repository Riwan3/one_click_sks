<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratTeguranRequest extends FormRequest
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
            'tanggal_kejadian' => ['required', 'date'],
            'tempat_kejadian' => ['nullable', 'string', 'max:150'],
            'jenis_absen' => ['nullable', 'boolean'],
            'jenis_perilaku' => ['nullable', 'boolean'],
            'jenis_ceroboh' => ['nullable', 'boolean'],
            'jenis_hasil_kerja' => ['nullable', 'boolean'],
            'jenis_menolak_perintah' => ['nullable', 'boolean'],
            'jenis_pelanggaran_peraturan' => ['nullable', 'boolean'],
            'penjelasan_singkat' => ['nullable', 'string'],
            'kejadian_tanggal' => ['nullable', 'date'],
            'kejadian_jam' => ['nullable', 'date_format:H:i'],
            'kejadian_lokasi' => ['nullable', 'string', 'max:150'],
            'dikeluarkan_di' => ['nullable', 'string', 'max:100'],
            'dikeluarkan_tanggal' => ['required', 'date'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_atasan' => ['nullable', 'string'],
            'ttd_atasan_atasan' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'tanggal_kejadian.required' => 'Tanggal kejadian wajib diisi.',
            'dikeluarkan_tanggal.required' => 'Tanggal dikeluarkan wajib diisi.',
        ];
    }
}
