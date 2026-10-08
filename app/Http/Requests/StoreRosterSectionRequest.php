<?php

namespace App\Http\Requests;

/** Tambah section. */
class StoreRosterSectionRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string'],
        ];
    }
}
