<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('berita-acara:sync', function () {
    $site = \App\Models\Setting::getValue('berita_acara_site_code', 'ADW');
    \App\Models\BeritaAcara::updateSiteCode($site);
    $row = \App\Models\BeritaAcara::find(1);
    $this->info("Synced to: " . $site . " | Result: " . ($row ? $row->nomor_surat : 'none'));
});
