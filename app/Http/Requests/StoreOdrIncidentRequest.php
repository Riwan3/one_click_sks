<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOdrIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-compliance-report');
    }

    public function rules(): array
    {
        return [
            'tanggal_insiden' => ['required', 'date'],
            'judul_insiden' => ['required', 'string', 'max:200'],
            'area' => ['nullable', 'string', 'max:150'],
            'deskripsi_insiden' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        $message = 'Tanggal insiden dan judul wajib diisi.';

        return ['tanggal_insiden.required' => $message, 'judul_insiden.required' => $message];
    }
}
