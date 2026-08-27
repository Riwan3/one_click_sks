<?php

/**
 * Menu sidebar One-Setting — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'Gauge', 'href' => '/one-setting'],
    ['menu_key' => 'setting-manpower', 'label' => 'Setting Manpower', 'icon' => 'Users', 'href' => null],
    ['menu_key' => 'setting-manpower-v2', 'label' => 'Setting Manpower V2', 'icon' => 'UserCheck', 'href' => null],
    ['menu_key' => 'setting-fleet', 'label' => 'Setting Fleet', 'icon' => 'Truck', 'href' => null],
    ['menu_key' => 'setting-fleet-v2', 'label' => 'Setting Fleet V2', 'icon' => 'Database', 'href' => null],
    ['menu_key' => 'monitoring-setting-manpower', 'label' => 'Monitoring Setting Manpower', 'icon' => 'Activity', 'href' => null],
    ['menu_key' => 'kesiapan-operator', 'label' => 'Kesiapan Operator', 'icon' => 'ShieldCheck', 'href' => null],
    ['menu_key' => 'daily-atr', 'label' => 'Daily ATR', 'icon' => 'CalendarDays', 'href' => null],
];
