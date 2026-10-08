<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Cari karyawan (autocomplete) atau lookup NRP exact. Hanya baca. */
class SearchRosterEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewRosterV2();
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string'],
            'nrp' => ['nullable', 'string'],
        ];
    }
}
