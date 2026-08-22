<?php

/**
 * Menu sidebar One-Power — dummy, href null berarti halamannya belum dibuat.
 */
return [
    ['label' => 'Dashboard', 'icon' => 'LayoutGrid', 'href' => '/one-power'],
    ['label' => 'Data Karyawan', 'icon' => 'Users', 'href' => '/one-power/employees'],
    ['label' => 'Karyawan Nonaktif', 'icon' => 'UserX', 'href' => '/one-power/employees/inactive'],
    [
        'label' => 'Mpp', 'icon' => 'FileBarChart2', 'children' => [
            ['label' => 'Actual MPP Master', 'icon' => 'FileSpreadsheet', 'href' => null],
            ['label' => 'MPP (Manpower Planning)', 'icon' => 'FileBarChart', 'href' => null],
        ],
    ],
    [
        'label' => 'Roster', 'icon' => 'CalendarRange', 'children' => [
            ['label' => 'Roster Bulanan / Harian', 'icon' => 'CalendarRange', 'href' => null],
            ['label' => 'Roster Dept Produksi', 'icon' => 'Users', 'href' => null],
        ],
    ],
    [
        'label' => 'Personal Data Operator', 'icon' => 'Contact', 'children' => [
            ['label' => 'HM', 'icon' => 'Gauge', 'href' => '/one-power'],
            ['label' => 'Attendance', 'icon' => 'CalendarCheck', 'href' => null],
            ['label' => 'MPR', 'icon' => 'BarChart2', 'href' => null],
            ['label' => 'Kimper', 'icon' => 'Eye', 'href' => null],
            ['label' => 'Attendance V2', 'icon' => 'CalendarClock', 'href' => null],
        ],
    ],
    ['label' => 'Mor', 'icon' => 'BarChart', 'href' => null],
    ['label' => 'Quartal Operator Report', 'icon' => 'CalendarDays', 'href' => null],
    [
        'label' => 'Deviasi Operator', 'icon' => 'UserRoundX', 'children' => [
            ['label' => 'Izin Karyawan', 'icon' => 'UserRoundX', 'href' => null],
            ['label' => 'Operator Unfit', 'icon' => 'HeartPulse', 'href' => null],
        ],
    ],
    [
        'label' => 'Kpi', 'icon' => 'TrendingUp', 'children' => [
            ['label' => 'MP', 'href' => null],
            ['label' => 'OPD', 'href' => null],
            ['label' => 'FRONT', 'href' => null],
            ['label' => 'DISPOSAL', 'href' => null],
            ['label' => 'DEWATERING', 'href' => null],
            ['label' => 'COAL', 'href' => null],
            ['label' => 'HRM', 'href' => null],
            ['label' => 'Penilaian GL', 'href' => null],
        ],
    ],
    [
        'label' => 'Pembinaan', 'icon' => 'ShieldCheck', 'children' => [
            ['label' => 'Berita Acara', 'href' => null],
            ['label' => 'Coaching & Counseling', 'href' => null],
            ['label' => 'Interview Fatigue', 'href' => null],
            ['label' => 'Pakta Integritas', 'href' => null],
            ['label' => 'Pemulangan Karyawan', 'href' => null],
            ['label' => 'SK Pembinaan', 'href' => null],
            ['label' => 'Surat Peringatan', 'href' => null],
            ['label' => 'Surat Teguran', 'href' => null],
            ['label' => 'Validasi Istirahat', 'href' => null],
            ['label' => 'Pemanggilan Karyawan', 'href' => null],
        ],
    ],
    [
        'label' => 'Coaching Clinic', 'icon' => 'Lightbulb', 'children' => [
            ['label' => 'Dashboard V1', 'href' => null],
            ['label' => 'Dashboard V2', 'href' => null],
            ['label' => 'Progress Monitoring', 'href' => null],
            ['label' => 'Laporan', 'href' => null],
            [
                'label' => 'Master Coach', 'children' => [
                    ['label' => 'Tahapan', 'href' => null],
                    ['label' => 'Materi', 'href' => null],
                ],
            ],
        ],
    ],
];
