<?php

namespace App\Http\Requests;

/** Simpan hasil pratinjau import Google Sheets (tab roster). */
class CommitRosterSheetRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'roster_id' => ['required', 'integer', 'min:1'],
            'sync_moncu' => ['nullable', 'boolean'],
        ];
    }
}
