<?php

/**
 * Menu sidebar One-Reporting — dipandu oleh kebutuhan V1.
 * menu_key wajib unik dalam file ini.
 */
return [
    ['menu_key' => 'dashboard-compliance', 'label' => 'Dashboard Compliance', 'icon' => 'LayoutGrid', 'href' => '/one-reporting/dashboard'],
    [
        'menu_key' => 'compliance', 'label' => 'Compliance', 'icon' => 'ShieldCheck', 'children' => [
            ['menu_key' => 'daily-compliance', 'label' => 'Daily Compliance', 'icon' => 'ClipboardCheck', 'href' => '/one-reporting/daily'],
            ['menu_key' => 'laporan-compliance', 'label' => 'Laporan Compliance', 'icon' => 'FileText', 'href' => '/one-reporting/laporan'],
            ['menu_key' => 'setting-gl', 'label' => 'Setting GL', 'icon' => 'UserCheck', 'href' => '/one-reporting/setting-gl'],
            ['menu_key' => 'master-compliance', 'label' => 'Master Data Compliance', 'icon' => 'Database', 'href' => '/one-reporting/master'],
        ],
    ],
    ['menu_key' => 'perbaikan-pasca-insiden', 'label' => 'Perbaikan Pasca-Insiden', 'icon' => 'Wrench', 'href' => '/one-reporting/perbaikan-pasca-insiden'],
    ['menu_key' => 'ojk', 'label' => 'OJK (Observasi Jam Kritis)', 'icon' => 'Camera', 'href' => '/one-reporting/ojk'],
];
