<?php

namespace App\Http\Requests;

/** Ubah Kode Off / pola kerja anggota dan (opsional) terapkan putaran. */
class SaveRosterMemberRotationRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'kode_off' => ['nullable', 'string'],
            'work_pattern' => ['nullable', 'string'],
            'date_from' => ['nullable', 'string'],
            'date_to' => ['nullable', 'string'],
            'apply' => ['nullable', 'boolean'],
        ];
    }
}
