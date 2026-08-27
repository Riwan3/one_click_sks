<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoachingProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'clinic_id' => ['required', 'integer', 'exists:coaching_clinics,id'],
            'tahapan_ids' => ['required', 'array', 'min:1'],
            'tahapan_ids.*' => ['integer', 'exists:coaching_tahapans,id'],
            'progress_pct' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
