<?php

namespace App\Http\Requests;

/** Tambah anggota ke section. */
class StoreRosterMemberRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'nrp' => ['nullable', 'string'],
            'unit' => ['nullable', 'string'],
            'kode_off' => ['nullable', 'string'],
            'work_pattern' => ['nullable', 'string'],
        ];
    }
}
