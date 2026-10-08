<?php

namespace App\Http\Requests;

/** Simpan hasil pratinjau import Excel. */
class CommitRosterImportRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
