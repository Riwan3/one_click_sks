<?php

namespace App\Http\Requests;

use App\Services\ManpowerV201Service;
use App\Services\ManpowerV201SettingService;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

/**
 * Akses Setting Manpower v2.01 (Admin, Admin Produksi, GL Manpower; sama seperti V1) + normalisasi query
 * tanggal/shift/bulan/status. Dipakai langsung untuk halaman & aksi tanpa body.
 */
class ManpowerV201Request extends FormRequest
{
    public function authorize(): bool
    {
        return ManpowerV201Service::canManage();
    }

    public function rules(): array
    {
        return [];
    }

    public function text(string $key, string $default = ''): string
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $default;
    }

    /** Tanggal kerja (Y-m-d); tidak valid/kosong -> hari ini. */
    public function workDate(): string
    {
        $date = $this->text('date');

        return ManpowerV201SettingService::validDate($date) ? $date : date('Y-m-d');
    }

    /** Shift D/N; kosong/tidak valid -> menurut jam sekarang (06-18 = D), seperti V1. */
    public function workShift(): string
    {
        $hour = (int) date('H');
        $shift = strtoupper($this->text('shift', ($hour >= 6 && $hour < 18) ? 'D' : 'N'));

        return ManpowerV201SettingService::validShift($shift) ? $shift : 'D';
    }

    public function month(): string
    {
        $month = $this->text('month');

        return ManpowerV201SettingService::validMonth($month) ? $month : date('Y-m');
    }

    /** Jenis monitoring: manpower (default) | fleet. */
    public function monitoringType(): string
    {
        return $this->text('type') === 'fleet' ? 'fleet' : 'manpower';
    }

    /** Nomor halaman monitoring; null = otomatis (halaman yang memuat hari ini). */
    public function pageNumber(): ?int
    {
        $page = $this->text('page');

        return ctype_digit($page) ? (int) $page : null;
    }

    /** Parameter id positif wajib (pesan sama seperti V1). */
    public function requiredId(string $key): int
    {
        $value = $this->input($key);
        $isValid = is_scalar($value) && preg_match('/^\d+$/', (string) $value) && (int) $value > 0;
        if (! $isValid) {
            throw new InvalidArgumentException("Parameter $key wajib diisi dengan benar.");
        }

        return (int) $value;
    }
}
