<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParseOdrGlSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-gl-setting');
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'string', 'max:20'],
            'front_text' => ['nullable', 'string'],
            'disposal_text' => ['nullable', 'string'],
        ];
    }
}
