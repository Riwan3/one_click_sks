<?php

namespace App\Http\Requests;

/** Target SIANG/MALAM per section (kosong = tanpa target). */
class SaveRosterSectionTargetRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'target_siang' => ['nullable'],
            'target_malam' => ['nullable'],
        ];
    }
}
