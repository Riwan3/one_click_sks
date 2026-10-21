<?php

namespace App\Http\Requests;

use App\Models\OpdPengajuanGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AjukanGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:50'],
            'termin' => ['required', 'integer', Rule::in(OpdPengajuanGrade::TERMINS)],
        ];
    }

    public function messages(): array
    {
        return ['termin.*' => 'Pilih termin 1 sampai 4.'];
    }
}
