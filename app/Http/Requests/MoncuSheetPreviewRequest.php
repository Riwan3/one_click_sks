<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoncuSheetPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:1000'],
            'roster_id' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
