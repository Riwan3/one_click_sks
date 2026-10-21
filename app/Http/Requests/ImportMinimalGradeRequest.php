<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportMinimalGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sheet_url' => ['nullable', 'url', 'starts_with:https://docs.google.com/spreadsheets/d/', 'required_without:file_sheet'],
            'file_sheet' => ['nullable', 'file', 'mimes:xlsx,csv,txt', 'max:5120', 'required_without:sheet_url'],
            'replace_all' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sheet_url.required_without' => 'Isi link Google Sheet atau pilih file Excel/CSV.',
            'file_sheet.required_without' => 'Isi link Google Sheet atau pilih file Excel/CSV.',
            'sheet_url.starts_with' => 'Link Google Sheet tidak valid. Gunakan link yang diawali https://docs.google.com/spreadsheets/d/...',
            'file_sheet.mimes' => 'Format file harus .xlsx atau .csv.',
            'file_sheet.max' => 'Ukuran file maksimal 5 MB.',
        ];
    }
}
