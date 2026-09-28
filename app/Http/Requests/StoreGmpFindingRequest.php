<?php

namespace App\Http\Requests;

use App\Models\GmpFinding;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Temuan manual (Temuan Lapangan). */
class StoreGmpFindingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'tahun' => ['nullable', 'integer'],
            'week' => ['nullable', 'string', 'max:50'],
            'area_id' => ['required', 'string', 'max:50'],
            'location_id' => ['nullable', 'string', 'max:255'],
            'aspect_id' => ['nullable', 'string', 'max:255'],
            'checklist_id' => ['nullable', 'string', 'max:255'],
            'finding_description' => ['required', 'string'],
            'foto_sebelum' => ['required', 'image', 'max:10240'],
            'pic_user_id' => ['required', 'string', 'max:100'],
            'priority' => ['required', Rule::in(GmpFinding::PRIORITIES)],
            'due_date' => ['required', 'date'],
            'recommendation' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        $required = 'Kolom Area, Deskripsi Temuan, PIC, dan Deadline wajib diisi.';

        return [
            'area_id.required' => $required,
            'finding_description.required' => $required,
            'pic_user_id.required' => $required,
            'due_date.required' => $required,
            'foto_sebelum.required' => 'Evidence before (Foto Sebelum) wajib untuk temuan manual.',
        ];
    }
}
