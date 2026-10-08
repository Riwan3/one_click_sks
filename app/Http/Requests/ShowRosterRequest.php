<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Query halaman daftar & detail roster (semua opsional; nilai tak valid diabaikan oleh service). */
class ShowRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewRosterV2();
    }

    public function rules(): array
    {
        return [];
    }

    /** Query string sebagai teks; nilai non-string (mis. array) dianggap kosong, sama seperti V1. */
    public function text(string $key, string $default = ''): string
    {
        $value = $this->query($key);

        return is_string($value) ? trim($value) : $default;
    }
}
