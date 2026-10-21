<?php

namespace App\Http\Requests;

use App\Models\OpdPengajuanGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengajuanGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nrp' => ['required', 'string', 'max:50'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'termin' => ['required', 'integer', Rule::in(OpdPengajuanGrade::TERMINS)],
        ];
    }
}
