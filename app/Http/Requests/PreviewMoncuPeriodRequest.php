<?php

namespace App\Http\Requests;

/** Pratinjau hitung periode (read-only): cukup hak lihat. */
class PreviewMoncuPeriodRequest extends MoncuPeriodRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewRosterV2();
    }
}
