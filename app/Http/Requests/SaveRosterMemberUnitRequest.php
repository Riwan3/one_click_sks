<?php

namespace App\Http\Requests;

/** Ubah Unit anggota. */
class SaveRosterMemberUnitRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'unit' => ['nullable', 'string'],
        ];
    }
}
