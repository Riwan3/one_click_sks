<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveMinimalGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit' => ['required', 'string', 'max:150'],
            'grade_min_max' => ['nullable', 'string', 'max:100'],
            'grade_min' => ['nullable', 'string', 'max:50'],
            'cluster_unit' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return ['unit.required' => 'UNIT wajib diisi.'];
    }
}
