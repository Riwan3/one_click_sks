<?php

/**
 * Menu sidebar One-GMP — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'LayoutGrid', 'href' => '/one-gmp'],
    ['menu_key' => 'dashboard-monitoring-v2', 'label' => 'Dashboard Monitoring v2', 'icon' => 'Gauge', 'href' => null],
    ['menu_key' => 'trend-gmp', 'label' => 'Trend GMP', 'icon' => 'BarChart2', 'href' => null],
    ['menu_key' => 'temuan-tindak-lanjut', 'label' => 'Temuan & Tindak Lanjut', 'icon' => 'ClipboardCheck', 'href' => null],
];
