<?php

namespace App\Http\Requests;

use App\Models\OpdPengajuanGrade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetPengajuanRemarkRequest extends FormRequest
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
            'remark' => ['nullable', 'string', 'max:255'],
            'warna' => ['nullable', 'string', Rule::in(OpdPengajuanGrade::REMARK_COLORS)],
            'by' => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'nrp.required' => 'nrp, tahun, dan termin (1-4) wajib diisi.',
            'tahun.*' => 'nrp, tahun, dan termin (1-4) wajib diisi.',
            'termin.*' => 'nrp, tahun, dan termin (1-4) wajib diisi.',
            'warna.in' => 'warna harus merah, kuning, hijau, atau kosong.',
            'remark.max' => 'remark maksimal 255 karakter.',
        ];
    }
}
