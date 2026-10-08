<?php

namespace App\Http\Requests;

/** Pratinjau import Google Sheets (tab roster). */
class PreviewRosterSheetRequest extends RosterManageRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'string'],
            'roster_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return ['url.required' => 'Isi link Google Sheets terlebih dahulu.'];
    }
}
