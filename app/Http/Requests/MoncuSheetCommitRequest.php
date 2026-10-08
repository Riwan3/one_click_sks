<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoncuSheetCommitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    public function rules(): array
    {
        return ['token' => ['required', 'string', 'max:200']];
    }
}
