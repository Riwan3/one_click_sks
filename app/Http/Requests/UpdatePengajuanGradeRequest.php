<?php

namespace App\Http\Requests;

use App\Models\OpdPengajuanGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePengajuanGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxLength = OpdPengajuanGrade::EDITABLE_FIELDS[$this->input('field')] ?? 255;

        return [
            'field' => ['required', Rule::in(array_keys(OpdPengajuanGrade::EDITABLE_FIELDS))],
            'value' => ['nullable', 'string', "max:{$maxLength}"],
        ];
    }
}
