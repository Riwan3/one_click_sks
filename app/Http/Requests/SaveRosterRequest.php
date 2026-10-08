<?php

namespace App\Http\Requests;

/** Buat / ubah header roster. */
class SaveRosterRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'roster_group' => ['nullable', 'string'],
            'year' => ['nullable'],
            'period_start' => ['nullable', 'string'],
            'period_end' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'pic_name' => ['nullable', 'string'],
            'pic_jabatan' => ['nullable', 'string'],
        ];
    }
}
