<?php

namespace App\Http\Requests;

use App\Models\GmpFinding;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignGmpFindingPicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'pic_nrp' => ['required', 'string', 'max:100'],
            'prioritas' => ['required', Rule::in(GmpFinding::PRIORITIES)],
            'deadline' => ['nullable', 'date'],
            'observer' => ['nullable', 'string', 'max:255'],
            'rekomendasi_awal' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return ['pic_nrp.required' => 'PIC wajib dipilih.'];
    }
}
