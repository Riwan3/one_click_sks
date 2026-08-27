<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperatorUnfitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:150'],
            'perusahaan' => ['nullable', 'string', 'max:150'],
            'departemen' => ['nullable', 'string', 'max:100'],
            'contact' => ['nullable', 'string', 'max:100'],
            'diagnosa' => ['nullable', 'string', 'max:255'],
            'dokter_rujukan' => ['nullable', 'string', 'max:150'],
            'tgl_mulai_unfit' => ['required', 'date'],
            'tgl_akhir_unfit' => ['nullable', 'date', 'after_or_equal:tgl_mulai_unfit'],
            'laporan' => ['nullable', 'string'],
            'bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required' => 'NRP wajib diisi.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'tgl_mulai_unfit.required' => 'Tanggal mulai unfit wajib diisi.',
            'tgl_akhir_unfit.after_or_equal' => 'Tanggal akhir unfit tidak boleh sebelum tanggal mulai.',
            'bukti.mimes' => 'Bukti harus berupa gambar, PDF, atau dokumen Word.',
            'bukti.max' => 'Ukuran file bukti maksimal 5MB.',
        ];
    }
}
