<?php

/**
 * Menu sidebar One-Reporting — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'LayoutGrid', 'href' => '/one-reporting'],
    ['menu_key' => 'daily-assignment', 'label' => 'Daily Assignment', 'icon' => 'CalendarCheck', 'href' => null],
    ['menu_key' => 'daily-compliance', 'label' => 'Daily Compliance', 'icon' => 'ClipboardCheck', 'href' => null],
    ['menu_key' => 'rekapitulasi-compliance', 'label' => 'Rekapitulasi Compliance', 'icon' => 'FileBarChart2', 'href' => null],
    ['menu_key' => 'checklist-hp', 'label' => 'Checklist HP', 'icon' => 'ClipboardCheck', 'href' => null],
    ['menu_key' => 'reporting-p5m', 'label' => 'Reporting P5M', 'icon' => 'FileText', 'href' => null],
];
