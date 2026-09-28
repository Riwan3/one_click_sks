<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachingCounselingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'coach_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'sp_id' => ['nullable', 'integer', 'exists:surat_peringatans,id'],
            'sk_id' => ['nullable', 'integer', 'exists:sk_pembinaans,id'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'waktu_pertemuan' => ['nullable', 'string', 'max:20'],
            'materi_pribadi' => ['nullable'],
            'materi_keluarga' => ['nullable'],
            'materi_lingkungan' => ['nullable'],
            'materi_pekerjaan' => ['nullable'],
            'deskripsi' => ['required', 'string'],
            'rekomendasi' => ['nullable', 'string'],
            'fatigue_level' => ['nullable', 'integer', 'min:0', 'max:10'],
            'file_bukti' => ['nullable', 'file', 'max:10240'],
            'ttd_anak_asuh' => ['nullable', 'string'],
            'ttd_bapa_asuh' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan / Anak asuh wajib dipilih.',
            'coach_employee_id.required' => 'Coach / Bapak asuh wajib dipilih.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'deskripsi.required' => 'Deskripsi / penjelasan wajib diisi.',
        ];
    }
}
