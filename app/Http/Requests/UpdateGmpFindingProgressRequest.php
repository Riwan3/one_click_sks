<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGmpFindingProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'action_type' => ['required', 'in:save_progress,submit_verifikasi'],
            'analisis_penyebab' => ['nullable', 'string'],
            'rencana_tindakan' => ['nullable', 'string'],
            'tindakan_aktual' => ['nullable', 'string'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai_aktual' => ['nullable', 'date'],
            'kendala' => ['nullable', 'string', 'max:255'],
            'foto_progress' => ['nullable', 'image', 'max:10240'],
            'foto_sesudah' => ['nullable', 'image', 'max:10240'],
        ];
    }
}
