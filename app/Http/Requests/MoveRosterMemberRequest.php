<?php

namespace App\Http\Requests;

/** Pindah section / ubah nomor urut anggota. */
class MoveRosterMemberRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'section_id' => ['required', 'integer', 'min:1'],
            'position' => ['nullable'],
        ];
    }
}
