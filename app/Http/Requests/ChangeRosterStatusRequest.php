<?php

namespace App\Http\Requests;

/** Publish / archive roster (archive wajib verifikasi NRP + password). */
class ChangeRosterStatusRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'auth_nrp' => ['nullable', 'string'],
            'auth_password' => ['nullable', 'string'],
        ];
    }
}
