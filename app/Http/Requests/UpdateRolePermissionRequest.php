<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_mobile' => ['required', 'boolean'],
            'module_keys' => ['nullable', 'array'],
            'module_keys.*' => ['string'],
        ];
    }
}
