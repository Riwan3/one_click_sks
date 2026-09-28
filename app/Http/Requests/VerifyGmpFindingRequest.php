<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyGmpFindingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'hasil_verifikasi' => ['required', 'in:closed,rework'],
            'komentar_verifikator' => ['nullable', 'required_if:hasil_verifikasi,rework', 'string'],
            'evidence_verifikasi' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'hasil_verifikasi.required' => 'Data verifikasi tidak lengkap.',
            'komentar_verifikator.required_if' => 'Komentar wajib diisi jika menolak (rework).',
        ];
    }
}
