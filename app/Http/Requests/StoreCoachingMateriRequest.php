<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachingMateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:pribadi,keluarga,lingkungan,pekerjaan'],
            'ringkasan' => ['required', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'file_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:51200'],
            'link_sop' => ['nullable', 'string', 'url'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'is_revisi' => ['nullable', 'boolean'],
            'revisi_note' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'kategori.in' => 'Kategori tidak valid.',
            'ringkasan.required' => 'Ringkasan wajib diisi.',
            'file_pdf.mimes' => 'File PDF harus berformat PDF.',
            'file_pdf.max' => 'File PDF tidak boleh lebih dari 10MB.',
            'video.mimes' => 'Video harus berformat MP4, WebM, atau MOV.',
            'video.max' => 'Video tidak boleh lebih dari 50MB.',
            'thumbnail.mimes' => 'Thumbnail harus berformat JPG atau PNG.',
            'thumbnail.max' => 'Thumbnail tidak boleh lebih dari 2MB.',
        ];
    }
}
