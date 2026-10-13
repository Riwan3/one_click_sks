<?php

namespace App\Console\Commands;

use App\Services\ControlCenterReport;
use App\Support\ControlCenterPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Mengirim laporan site ke OneClick Control Center (mode push). Dipakai
 * saat Control Center tidak bisa menarik data langsung dari site (mis.
 * terhalang firewall). Memakai token yang sama dengan endpoint /api/cc/*.
 */
class PushToControlCenter extends Command
{
    protected $signature = 'cc:push';

    protected $description = 'Kirim status, aktivitas, dan struktur menu site ke OneClick Control Center';

    public function handle(ControlCenterReport $report): int
    {
        $url = rtrim((string) config('services.control_center.url'), '/');
        $code = (string) config('services.control_center.site_code');
        $token = (string) config('services.control_center.token');

        if ($url === '' || $code === '' || $token === '') {
            $this->error('CONTROL_CENTER_URL, CONTROL_CENTER_SITE_CODE, dan CONTROL_CENTER_TOKEN harus diisi di .env.');

            return self::FAILURE;
        }

        try {
            $payload = $report->all();
        } catch (Throwable $e) {
            $this->error('Gagal menyusun laporan: '.$e->getMessage());

            return self::FAILURE;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['X-Site-Code' => $code])
                ->acceptJson()
                ->timeout(20)
                ->post("{$url}/api/cc/report", $payload);
        } catch (Throwable $e) {
            $this->error('Tidak dapat menghubungi Control Center: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error('Control Center menolak laporan: HTTP '.$response->status());

            return self::FAILURE;
        }

        $this->info("Laporan terkirim ke {$url} sebagai {$code}.");

        // Balasan membawa konfigurasi dari Control Center (modul/menu nonaktif, versi target).
        $config = $response->json('config');
        if (is_array($config)) {
            ControlCenterPolicy::store($config);
            $this->line(sprintf(
                'Konfigurasi diterima: %d modul & %d menu dinonaktifkan%s.',
                count($config['disabled_modules'] ?? []),
                array_sum(array_map('count', $config['disabled_menus'] ?? [])),
                ControlCenterPolicy::applying() ? '' : ' (belum diterapkan: CONTROL_CENTER_APPLY_CONFIG=false)'
            ));
        }

        return self::SUCCESS;
    }
}
