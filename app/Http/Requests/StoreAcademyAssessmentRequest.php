<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademyAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-academy-evaluation');
    }

    public function rules(): array
    {
        return [
            'procedure_id' => ['required', 'exists:academy_materi,id'],
            'title' => ['required', 'string', 'max:255'],
            ...self::settingRules(),
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'procedure_id.required' => 'Materi dan judul wajib diisi',
            'title.required' => 'Materi dan judul wajib diisi',
        ];
    }

    /** Aturan pengaturan assessment yang sama untuk buat & edit settings. */
    public static function settingRules(): array
    {
        return [
            'passing_grade' => ['nullable', 'integer', 'min:0', 'max:100'],
            'timer_minutes' => ['nullable', 'integer', 'min:1'],
            'retry_limit' => ['nullable', 'integer', 'in:1,2,3,5,99'],
            'validity_months' => ['nullable', 'integer', 'in:0,3,6,12,24,36'],
            'randomize_questions' => ['boolean'],
            'mandatory_read' => ['boolean'],
        ];
    }
}
