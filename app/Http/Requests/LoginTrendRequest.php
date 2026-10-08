<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginTrendRequest extends FormRequest
{
    private const YEARS_BACK = 2;

    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewLoginTrend();
    }

    public function rules(): array
    {
        return [
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'page_never' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function month(): int
    {
        return (int) ($this->validated('bulan') ?? date('n'));
    }

    public function year(): int
    {
        return (int) ($this->validated('tahun') ?? date('Y'));
    }

    public function neverPage(): int
    {
        return (int) ($this->validated('page_never') ?? 1);
    }

    /** Pilihan tahun di filter: tahun ini dan beberapa tahun ke belakang. */
    public function yearOptions(): array
    {
        return range((int) date('Y'), (int) date('Y') - self::YEARS_BACK);
    }
}
