<?php

/**
 * Menu sidebar One-Reporting — dipandu oleh kebutuhan V1.
 * menu_key wajib unik dalam file ini.
 */
return [
    ['menu_key' => 'dashboard-compliance', 'label' => 'Dashboard Compliance', 'icon' => 'LayoutGrid', 'href' => '/one-reporting/dashboard'],
    ['menu_key' => 'daily-compliance', 'label' => 'Daily Compliance', 'icon' => 'ClipboardCheck', 'href' => '/one-reporting/daily'],
    ['menu_key' => 'laporan-compliance', 'label' => 'Laporan Compliance', 'icon' => 'FileText', 'href' => '/one-reporting/laporan'],
    ['menu_key' => 'master-compliance', 'label' => 'Master Data Compliance', 'icon' => 'Database', 'href' => '/one-reporting/master'],
    ['menu_key' => 'ojk', 'label' => 'OJK (Observasi Jaminan Safety)', 'icon' => 'Camera', 'href' => '/one-reporting/ojk'],
];
