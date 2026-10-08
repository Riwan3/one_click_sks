<?php

namespace App\Http\Requests;

/** Bulan roster yang dibuka di Share Roster. */
class SaveRosterVisibilityRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'months' => ['nullable', 'array'],
            'months.*' => ['nullable', 'string'],
        ];
    }
}
