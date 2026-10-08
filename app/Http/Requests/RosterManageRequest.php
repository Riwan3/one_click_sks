<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Dasar semua aksi tulis Roster V2: hanya yang boleh mengelola (Admin / Admin Produksi / Manpower).
 * Aturan di sini hanya memeriksa bentuk input; validasi bisnis (pesan Indonesia V1) tetap di RosterV2Service.
 */
abstract class RosterManageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageRosterV2();
    }

    public function rules(): array
    {
        return [];
    }
}
