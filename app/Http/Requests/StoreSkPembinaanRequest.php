<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkPembinaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'perusahaan' => ['nullable', 'string', 'max:255'],
            'tanggal_pelanggaran' => ['required', 'date'],
            'jam_pelanggaran' => ['nullable', 'string', 'max:20'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'berlaku_sampai' => ['required', 'date', 'after_or_equal:dikeluarkan_tanggal'],
            'spdk_ref' => ['nullable', 'string', 'max:100'],
            'dikeluarkan_di' => ['nullable', 'string', 'max:100'],
            'dikeluarkan_tanggal' => ['required', 'date'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_atasan' => ['nullable', 'string'],
            'ttd_pja' => ['nullable', 'string'],
            'ttd_hse' => ['nullable', 'string'],
            'pengawas_id' => ['nullable', 'integer', 'exists:employees,id'],
            'jenis_pelanggaran' => ['required', 'string'],
            'lubang' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'tanggal_pelanggaran.required' => 'Tanggal pelanggaran wajib diisi.',
            'berlaku_sampai.required' => 'Tanggal berlaku sampai wajib diisi.',
            'berlaku_sampai.after_or_equal' => 'Tanggal berlaku sampai tidak boleh sebelum tanggal dikeluarkan.',
            'dikeluarkan_tanggal.required' => 'Tanggal dikeluarkan wajib diisi.',
            'jenis_pelanggaran.required' => 'Jenis pelanggaran wajib diisi.',
        ];
    }
}
