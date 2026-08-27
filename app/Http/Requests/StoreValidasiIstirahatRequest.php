<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreValidasiIstirahatRequest extends FormRequest
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
            'alasan_kurang_tidur' => ['required', 'string'],
            'alasan_kurang_tidur_berulang' => ['nullable', 'string'],
            'kendala_tidur' => ['required', 'string', Rule::in(['Ya', 'Tidak'])],
            'tinggal_di' => ['required', Rule::in(['Mess', 'Non Mess'])],
            'nama_mess' => ['nullable', 'string', 'max:255'],
            'alamat_non_mess' => ['nullable', 'string'],
            'status_perkawinan' => ['required', 'string', 'max:50', Rule::in(['Single', 'Menikah'])],
            'jumlah_anak_serumah' => ['nullable', 'string', 'max:100'],
            'anak_usia_kurang_2th' => ['nullable', 'string', 'max:100'],
            'pekerjaan_sampingan' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
            'tanggal_validasi' => ['required', 'date'],
            'nama_pengawas' => ['required', 'string', 'max:150'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_pengawas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan tidak ditemukan.',
            'tanggal_validasi.required' => 'Tanggal validasi wajib diisi.',
        ];
    }
}
