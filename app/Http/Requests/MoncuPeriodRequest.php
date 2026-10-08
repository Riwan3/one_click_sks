<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Simpan / hapus periode cuti MONCU. Aturan bisnis & pesan validasi tetap di MoncuService (sama seperti V1). */
class MoncuPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:20'],
            'year' => ['required', 'integer', 'min:1'],
            'period_no' => ['required', 'integer', 'min:1'],
            'awal' => ['nullable', 'string', 'max:10'],
            'tahunan' => ['nullable', 'regex:/^-?\d+$/'],
            'izin_resmi' => ['nullable', 'regex:/^-?\d+$/'],
        ];
    }
}
