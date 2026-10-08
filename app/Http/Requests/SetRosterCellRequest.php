<?php

namespace App\Http\Requests;

/** Isi / kosongkan satu sel matriks. */
class SetRosterCellRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'nrp' => ['nullable', 'string'],
            'work_date' => ['nullable', 'string'],
            'shift_code' => ['nullable', 'string'],
        ];
    }
}
