<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademyAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-academy-evaluation');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            ...StoreAcademyAssessmentRequest::settingRules(),
            'signed_by' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
