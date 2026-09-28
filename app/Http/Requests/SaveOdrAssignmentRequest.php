<?php

namespace App\Http\Requests;

use App\Models\OdrAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOdrAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-compliance-assignment');
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'shift' => ['required', Rule::in(OdrAssignment::SHIFTS)],
            'area' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:100'],
            'pengawas' => ['required', 'string', 'max:150'],
            'aktivitas' => ['nullable', 'array', 'max:'.OdrAssignment::MAX_TASKS],
            'aktivitas.*' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['nullable', 'in:belum,progress,selesai'],
        ];
    }

    public function messages(): array
    {
        $required = 'Semua field wajib diisi (Tanggal, Area, Unit, Pengawas).';

        return [
            'tanggal.required' => $required,
            'area.required' => $required,
            'unit.required' => $required,
            'pengawas.required' => $required,
            'aktivitas.max' => 'Maksimal '.OdrAssignment::MAX_TASKS.' task per assignment.',
        ];
    }
}
