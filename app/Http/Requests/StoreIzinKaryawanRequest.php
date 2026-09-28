<?php

namespace App\Http\Requests;

use App\Enums\IzinJenis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreIzinKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_izin' => ['required', new Enum(IzinJenis::class)],
            'nama' => ['required', 'string', 'max:150'],
            'nrp' => ['required', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'departemen' => ['nullable', 'string', 'max:100'],

            'alasan' => ['required_if:jenis_izin,izin,emergency', 'nullable', 'string'],
            'tujuan' => ['nullable', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'tgl_berangkat' => ['required_if:jenis_izin,izin,emergency', 'nullable', 'date'],
            'tgl_kembali' => ['nullable', 'date', 'after_or_equal:tgl_berangkat'],

            'no_telepon' => ['nullable', 'string', 'max:100'],
            'keluhan' => ['nullable', 'string'],
            'hasil_td' => ['nullable', 'string', 'max:20'],
            'hasil_rr' => ['nullable', 'string', 'max:20'],
            'hasil_t' => ['nullable', 'string', 'max:20'],
            'hasil_p' => ['nullable', 'string', 'max:20'],
            'hasil_spo' => ['nullable', 'string', 'max:20'],
            'diagnosa' => ['nullable', 'string'],
            'terapi' => ['nullable', 'string'],
            'keterangan_status' => ['nullable', 'integer'],
            'rujuk_ke' => ['nullable', 'string', 'max:150'],
            'rumah_sakit_rujukan' => ['nullable', 'string', 'max:150'],
            'dokter_rujukan' => ['nullable', 'string', 'max:150'],
            'dipulangkan_tidur' => ['nullable', 'boolean'],

            'file_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_izin.required' => 'Jenis izin wajib dipilih.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'nrp.required' => 'NRP wajib diisi.',
            'alasan.required_if' => 'Alasan wajib diisi untuk jenis izin ini.',
            'tgl_berangkat.required_if' => 'Tanggal berangkat wajib diisi untuk jenis izin ini.',
            'tgl_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal berangkat.',
            'file_bukti.mimes' => 'Bukti harus berupa gambar, PDF, atau dokumen Word.',
            'file_bukti.max' => 'Ukuran file bukti maksimal 10MB.',
        ];
    }
}
