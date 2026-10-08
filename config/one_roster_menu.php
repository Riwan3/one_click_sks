<?php

/**
 * Menu sidebar One-Roster (urutan & label sama dengan One-Click V1).
 * menu_key wajib unik dalam file ini.
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard Monitoring', 'icon' => 'LayoutDashboard', 'href' => '/one-roster/dashboard'],
    ['menu_key' => 'monitoring-departemen', 'label' => 'Monitoring Roster Departement', 'icon' => 'UsersRound', 'href' => '/one-roster/monitoring-departemen'],
    ['menu_key' => 'roster', 'label' => 'Roster', 'icon' => 'CalendarRange', 'href' => '/one-roster/roster'],
    ['menu_key' => 'analisis-harian', 'label' => 'Analisis Harian', 'icon' => 'ChartLine', 'href' => '/one-roster/analisis-harian'],
    ['menu_key' => 'moncu', 'label' => 'MONCU', 'icon' => 'Activity', 'href' => '/one-roster/moncu'],
    ['menu_key' => 'moncu-monitoring', 'label' => 'MONCU Monitoring', 'icon' => 'Eye', 'href' => '/one-roster/moncu-monitoring'],
    ['menu_key' => 'moncu-calendar', 'label' => 'MONCU Calendar', 'icon' => 'CalendarClock', 'href' => '/one-roster/moncu-calendar'],
    ['menu_key' => 'share', 'label' => 'Share Roster', 'icon' => 'Share2', 'href' => '/one-roster/share'],
];
