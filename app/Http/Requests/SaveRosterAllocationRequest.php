<?php

namespace App\Http\Requests;

/** Tambah / hapus alokasi unit pada detail. */
class SaveRosterAllocationRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'unit' => ['nullable', 'string'],
            'section' => ['nullable', 'string'],
        ];
    }
}
