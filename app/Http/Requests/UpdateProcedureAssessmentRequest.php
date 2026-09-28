<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProcedureAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-procedure-evaluation');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            ...StoreProcedureAssessmentRequest::settingRules(),
            'signed_by' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
