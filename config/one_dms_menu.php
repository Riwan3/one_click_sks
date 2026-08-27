<?php

/**
 * Menu sidebar One-DMS
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard DMS', 'icon' => 'LayoutGrid', 'href' => '/one-dms/dashboard'],
    ['menu_key' => 'formulir', 'label' => 'Formulir DMS', 'icon' => 'FolderOpen', 'href' => '/one-dms/formulir'],
    [
        'menu_key' => 'data-icc', 'label' => 'Data ICC', 'icon' => 'Database', 'children' => [
            ['menu_key' => 'deviasi-fatigue', 'label' => 'Deviasi Fatigue', 'icon' => 'Activity', 'href' => '/one-dms/data-icc/deviasi-fatigue'],
            ['menu_key' => 'smoking-deviasi', 'label' => 'Smoking Deviasi', 'icon' => 'CircleSlash', 'href' => '/one-dms/data-icc/smoking-deviasi'],
            ['menu_key' => 'driver-distraction', 'label' => 'Driver Distraction', 'icon' => 'OctagonAlert', 'href' => '/one-dms/data-icc/driver-distraction'],
            ['menu_key' => 'phone-detection', 'label' => 'Phone Detection', 'icon' => 'Phone', 'href' => '/one-dms/data-icc/phone-detection'],
            ['menu_key' => 'yawning-detection', 'label' => 'Yawning Detection', 'icon' => 'Frown', 'href' => '/one-dms/data-icc/yawning-detection'],
            ['menu_key' => 'eye-blink', 'label' => 'Eye Blink', 'icon' => 'Eye', 'href' => '/one-dms/data-icc/eye-blink'],
            ['menu_key' => 'camera-covering', 'label' => 'Camera Covering', 'icon' => 'VideoOff', 'href' => '/one-dms/data-icc/camera-covering'],
            ['menu_key' => 'forward-collision', 'label' => 'Forward Collision', 'icon' => 'OctagonX', 'href' => '/one-dms/data-icc/forward-collision'],
            ['menu_key' => 'video-loss-alarm', 'label' => 'Video Loss Alarm', 'icon' => 'Video', 'href' => '/one-dms/data-icc/video-loss-alarm'],
            ['menu_key' => 'no-driver', 'label' => 'No Driver', 'icon' => 'UserX', 'href' => '/one-dms/data-icc/no-driver'],
            ['menu_key' => 'abnormal-speed', 'label' => 'Abnormal Speed', 'icon' => 'Gauge', 'href' => '/one-dms/data-icc/abnormal-speed'],
        ],
    ],
];
