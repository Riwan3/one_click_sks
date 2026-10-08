<?php

namespace App\Http\Requests;

/** Tambah / ubah detail jadwal (tab Daftar Detail). */
class SaveRosterDetailRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'nrp' => ['nullable', 'string'],
            'work_date' => ['nullable', 'string'],
            'shift_code' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'section' => ['nullable', 'string'],
        ];
    }
}
