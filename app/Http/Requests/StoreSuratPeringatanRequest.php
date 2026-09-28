<?php

namespace App\Http\Requests;

use App\Enums\JenisPembinaan;
use App\Models\SuratPeringatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreSuratPeringatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'jenis_pembinaan' => ['required', new Enum(JenisPembinaan::class)],
            'kode_pelanggaran' => ['nullable', 'string', 'max:50'],
            'detail_pelanggaran' => ['nullable', 'string'],
            'tanggal_kejadian' => ['required', 'date'],
            'jam_kejadian' => ['nullable', 'date_format:H:i'],
            'tempat_kejadian' => ['nullable', 'string', 'max:255'],
            'pelanggaran_absen_terlambat' => ['nullable', 'boolean'],
            'pelanggaran_perilaku' => ['nullable', 'boolean'],
            'pelanggaran_ceroboh_lalai' => ['nullable', 'boolean'],
            'pelanggaran_hasil_kerja' => ['nullable', 'boolean'],
            'pelanggaran_menolak_perintah' => ['nullable', 'boolean'],
            'pelanggaran_peraturan' => ['nullable', 'boolean'],
            'peringatan_sebelumnya_status' => ['nullable', 'string', 'max:20'],
            'peringatan_sebelumnya_jenis' => ['nullable', 'string', 'max:100'],
            'peringatan_sebelumnya_mengenai' => ['nullable', 'string'],
            'sanksi_masa_berlaku' => ['nullable', 'integer', 'min:1'],
            'dikeluarkan_di' => ['nullable', 'string', 'max:100'],
            'dikeluarkan_tanggal' => ['required', 'date'],
            'catatan_karyawan' => ['nullable', 'string'],
            'ttd_karyawan' => ['nullable', 'string'],
            'ttd_atasan' => ['nullable', 'string'],
            'pengawas_id' => ['nullable', 'integer', 'exists:employees,id'],
            'koreksi_alpa' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'jenis_pembinaan.required' => 'Jenis pembinaan wajib dipilih.',
            'tanggal_kejadian.required' => 'Tanggal kejadian wajib diisi.',
            'dikeluarkan_tanggal.required' => 'Tanggal dikeluarkan wajib diisi.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled(['employee_id', 'jenis_pembinaan', 'dikeluarkan_tanggal'])) {
                return;
            }

            $excludeId = $this->route('suratPeringatan')?->id;
            $activeSp = SuratPeringatan::activeFor((int) $this->input('employee_id'), $this->input('dikeluarkan_tanggal'));

            if (! $activeSp || $activeSp->id === $excludeId) {
                return;
            }

            $newLevel = JenisPembinaan::from($this->input('jenis_pembinaan'))->level();
            if ($newLevel <= $activeSp->jenis_pembinaan->level()) {
                $validator->errors()->add(
                    'jenis_pembinaan',
                    "Karyawan ini masih memiliki {$activeSp->jenis_pembinaan->value} yang aktif. Tingkat SP baru harus lebih tinggi."
                );
            }
        });
    }
}
