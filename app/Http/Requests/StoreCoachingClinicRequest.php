<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachingClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'coach_id' => ['nullable', 'integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['required', 'string', 'max:255'],
            'waktu_pertemuan' => ['required', 'date_format:H:i'],
            'materi_pribadi' => ['nullable', 'string'],
            'materi_keluarga' => ['nullable', 'string'],
            'materi_lingkungan' => ['nullable', 'string'],
            'materi_pekerjaan' => ['nullable', 'string'],
            'fatigue_level' => ['nullable', 'integer', 'min:0', 'max:3'],
            'deskripsi' => ['nullable', 'string'],
            'rekomendasi' => ['nullable', 'string'],
            'file_bukti' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'ttd_anak_asuh' => ['nullable', 'string'],
            'ttd_bapak_asuh' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Pegawai wajib dipilih.',
            'employee_id.exists' => 'Pegawai tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'waktu_pertemuan.required' => 'Waktu pertemuan wajib diisi.',
            'waktu_pertemuan.date_format' => 'Format waktu harus HH:mm.',
            'file_bukti.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            'file_bukti.max' => 'File tidak boleh lebih dari 5MB.',
        ];
    }
}
