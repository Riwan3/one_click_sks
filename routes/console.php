<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('berita-acara:sync', function () {
    $site = \App\Models\Setting::getValue('berita_acara_site_code', 'ADW');
    \App\Models\BeritaAcara::updateSiteCode($site);
    $row = \App\Models\BeritaAcara::find(1);
    $this->info("Synced to: " . $site . " | Result: " . ($row ? $row->nomor_surat : 'none'));
});

Artisan::command('mor:recalc {tahun} {quartal}', function (int $tahun, int $quartal) {
    $this->info("Menghitung ulang PDTY + Raport MOR Q{$quartal} {$tahun}...");
    $mulai = microtime(true);
    $hasil = app(\App\Services\QorService::class)->runRecalculation($tahun, $quartal);
    $this->info(sprintf(
        'Selesai: %d operator PDTY, bulan %s, %d periode raport, %.0f detik.',
        $hasil['pdty']['operator'], implode(',', $hasil['pdty']['bulan']) ?: '-', $hasil['periode_raport'], microtime(true) - $mulai
    ));
})->purpose('Hitung ulang PDTY dan Raport MOR satu quartal');

Artisan::command('mor:refresh', function () {
    $n = app(\App\Services\Mor\MorInputBuilder::class)->refreshDirty();
    $this->info("Raport MOR dihitung ulang untuk {$n} periode yang datanya berubah.");
})->purpose('Hitung ulang Raport MOR untuk bulan yang ditandai berubah (setelah import/reset)');

// Mode push ke OneClick Control Center: hanya berjalan bila URL-nya diisi di .env.
Schedule::command('cc:push')->everyFiveMinutes()->withoutOverlapping()
    ->when(fn () => filled(config('services.control_center.url')));
