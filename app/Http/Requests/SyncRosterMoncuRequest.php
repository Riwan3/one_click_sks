<?php

namespace App\Http\Requests;

/** Terapkan cuti MONCU (CT/ID) ke roster. */
class SyncRosterMoncuRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'nrp' => ['nullable', 'string'],
        ];
    }
}
