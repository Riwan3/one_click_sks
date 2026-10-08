<?php

namespace App\Http\Requests;

/** Duplikat roster ke tahun lain. */
class DuplicateRosterRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'roster_group' => ['nullable', 'string'],
            'year' => ['nullable'],
            'create_moncu' => ['nullable', 'boolean'],
        ];
    }
}
