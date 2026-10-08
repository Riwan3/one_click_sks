<?php

namespace App\Http\Requests;

/** Isi / kosongkan rentang tanggal satu anggota. */
class FillRosterCellsRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'nrp' => ['nullable', 'string'],
            'date_from' => ['nullable', 'string'],
            'date_to' => ['nullable', 'string'],
            'shift_code' => ['nullable', 'string'],
        ];
    }
}
