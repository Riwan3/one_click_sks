<?php

namespace App\Http\Requests;

/** Upload file Excel untuk pratinjau import. */
class PreviewRosterImportRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
        ];
    }

    public function messages(): array
    {
        return ['file.required' => 'File Excel belum dipilih.', 'file.file' => 'Upload gagal. Silakan coba lagi.'];
    }
}
