<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi tambah & edit materi prosedur (tab Procedure Evaluasi Prosedur). */
class SaveEvalProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-procedure-evaluation');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'revision' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'risk_level' => ['nullable', 'in:low,medium,high,critical'],
            'status' => ['nullable', 'in:active,revision_needed,expired'],
            'effective_date' => ['nullable', 'date'],
            'expired_date' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul prosedur wajib diisi',
            'file.mimes' => 'Tipe file tidak didukung. Gunakan PDF, JPG, PNG, atau DOCX.',
            'file.max' => 'Ukuran file melebihi 20 MB',
            'file.uploaded' => 'Upload gagal',
        ];
    }
}
