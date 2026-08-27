<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMppPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user() && in_array(auth()->user()->role, ['admin', 'pimpinan', 'gl_manpower', 'operator']);
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(['OJK', 'Checkpoint'])],
            'unit_code' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'shift' => ['required', Rule::in(['D', 'N'])],
            'nrp' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'file' => ['required', 'image', 'max:5120'], // max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis foto wajib dipilih.',
            'jenis.in' => 'Jenis foto harus OJK atau Checkpoint.',
            'unit_code.required' => 'Kode unit wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'shift.required' => 'Shift wajib dipilih.',
            'nrp.required' => 'NRP operator wajib diisi.',
            'nama.required' => 'Nama operator wajib diisi.',
            'file.required' => 'File foto wajib diunggah.',
            'file.image' => 'File harus berupa gambar.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
