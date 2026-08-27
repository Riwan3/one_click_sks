<?php

/**
 * Menu sidebar One-Power — href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'LayoutGrid', 'href' => '/one-power'],
    ['menu_key' => 'data-karyawan', 'label' => 'Data Karyawan', 'icon' => 'Users', 'href' => '/one-power/employees'],
    ['menu_key' => 'karyawan-nonaktif', 'label' => 'Data Karyawan Non-Aktif', 'icon' => 'UserX', 'href' => '/one-power/employees/inactive'],
    ['menu_key' => 'mpp-departemen', 'label' => 'MPP Departement', 'icon' => 'FileBarChart2', 'href' => '/one-power/mpp-departemen'],
    [
        'menu_key' => 'roster', 'label' => 'Roster', 'icon' => 'CalendarRange', 'children' => [
            ['menu_key' => 'roster-departemen', 'label' => 'Roster Departement', 'icon' => 'Users', 'href' => '/one-power/roster-departemen'],
            ['menu_key' => 'roster-personal', 'label' => 'Roster Personal', 'icon' => 'CalendarRange', 'href' => '/one-power/roster-personal'],
        ],
    ],
    [
        'menu_key' => 'personal-data-operator', 'label' => 'Personal Data Operator', 'icon' => 'Contact', 'children' => [
            ['menu_key' => 'personal-data-hm', 'label' => 'HM', 'icon' => 'Gauge', 'href' => '/one-power/personal-data/hm'],
            ['menu_key' => 'personal-data-attendance', 'label' => 'Attendance', 'icon' => 'CalendarCheck', 'href' => '/one-power/personal-data/attendance'],
            ['menu_key' => 'personal-data-mpr', 'label' => 'Manpower Performance Report', 'icon' => 'BarChart2', 'href' => '/one-power/personal-data/mpr'],
        ],
    ],
    ['menu_key' => 'mor', 'label' => 'MOR', 'icon' => 'BarChart', 'href' => '/one-power/mor'],
    ['menu_key' => 'quartal-operator-report', 'label' => 'Quartal Operator Report', 'icon' => 'CalendarDays', 'href' => '/one-power/quartal-operator-report'],
    [
        'menu_key' => 'deviasi-operator', 'label' => 'Deviasi Operator', 'icon' => 'UserRoundX', 'children' => [
            ['menu_key' => 'izin-karyawan', 'label' => 'Izin Karyawan', 'icon' => 'UserRoundX', 'href' => '/one-power/deviasi-operator/izin-karyawan'],
            ['menu_key' => 'operator-unfit', 'label' => 'Data Unfit Karyawan', 'icon' => 'HeartPulse', 'href' => '/one-power/deviasi-operator/unfit'],
        ],
    ],
    [
        'menu_key' => 'pembinaan', 'label' => 'Pembinaan', 'icon' => 'ShieldCheck', 'children' => [
            ['menu_key' => 'berita-acara', 'label' => 'Berita Acara', 'href' => '/one-power/pembinaan/berita-acara'],
            ['menu_key' => 'coaching-counseling', 'label' => 'Coaching & Counseling', 'href' => '/one-power/pembinaan/coaching-counseling'],
            ['menu_key' => 'pakta-integritas', 'label' => 'Pakta Integritas', 'href' => '/one-power/pembinaan/pakta-integritas'],
            ['menu_key' => 'sk-pembinaan', 'label' => 'SK Pembinaan & Sanksi Pelubangan', 'href' => '/one-power/pembinaan/sk-pembinaan'],
            ['menu_key' => 'surat-peringatan', 'label' => 'Surat Peringatan', 'href' => '/one-power/pembinaan/surat-peringatan'],
            ['menu_key' => 'surat-teguran', 'label' => 'Surat Teguran', 'href' => '/one-power/pembinaan/surat-teguran'],
            ['menu_key' => 'pemanggilan-karyawan', 'label' => 'Pemanggilan Karyawan', 'href' => '/one-power/pembinaan/pemanggilan-karyawan'],
        ],
    ],
    [
        'menu_key' => 'coaching-clinic', 'label' => 'Coaching Clinic', 'icon' => 'Lightbulb', 'children' => [
            ['menu_key' => 'coaching-clinic-data', 'label' => 'Data Coaching Karyawan (Coaching Clinic V1)', 'href' => '/one-power/coaching-clinic'],
        ],
    ],
];
