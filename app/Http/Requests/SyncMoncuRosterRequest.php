<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncMoncuRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    public function rules(): array
    {
        return [
            'roster_id' => ['required', 'integer', 'min:1'],
            'nrp' => ['nullable', 'string', 'max:20'],
        ];
    }
}
