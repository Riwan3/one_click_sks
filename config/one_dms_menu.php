<?php

/**
 * Menu sidebar One-DMS — dummy, href null berarti halamannya belum dibuat.
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'LayoutGrid', 'href' => null],
    ['menu_key' => 'dms', 'label' => 'DMS', 'icon' => 'FolderOpen', 'href' => '/one-dms'],
    ['menu_key' => 'ba', 'label' => 'BA', 'icon' => 'FileText', 'href' => null],
    ['menu_key' => 'eviden-intercom', 'label' => 'Eviden Intercom', 'icon' => 'Mic', 'href' => null],
    [
        'menu_key' => 'data-icc', 'label' => 'Data ICC', 'icon' => 'Database', 'children' => [
            ['menu_key' => 'deviasi-fatigue', 'label' => 'Deviasi Fatigue', 'icon' => 'Activity', 'href' => null],
            ['menu_key' => 'smoking-deviasi', 'label' => 'Smoking Deviasi', 'icon' => 'CircleSlash', 'href' => null],
            ['menu_key' => 'izin-fatigue', 'label' => 'Izin Fatigue', 'icon' => 'UserCheck', 'href' => null],
            ['menu_key' => 'correct-false-alarm', 'label' => 'Correct & False Alarm', 'icon' => 'BellRing', 'href' => null],
            ['menu_key' => 'driver-distraction', 'label' => 'Driver Distraction', 'icon' => 'OctagonAlert', 'href' => null],
            ['menu_key' => 'phone-detection', 'label' => 'Phone Detection', 'icon' => 'Phone', 'href' => null],
            ['menu_key' => 'yawning-detection', 'label' => 'Yawning Detection', 'icon' => 'Frown', 'href' => null],
            ['menu_key' => 'eye-blink', 'label' => 'Eye Blink', 'icon' => 'Eye', 'href' => null],
            ['menu_key' => 'camera-covering', 'label' => 'Camera Covering', 'icon' => 'VideoOff', 'href' => null],
            ['menu_key' => 'forward-collision', 'label' => 'Forward Collision', 'icon' => 'OctagonX', 'href' => null],
            ['menu_key' => 'video-loss-alarm', 'label' => 'Video Loss Alarm', 'icon' => 'Video', 'href' => null],
            ['menu_key' => 'no-driver', 'label' => 'No Driver', 'icon' => 'UserX', 'href' => null],
            ['menu_key' => 'abnormal-speed', 'label' => 'Abnormal Speed', 'icon' => 'Gauge', 'href' => null],
        ],
    ],
];
