<?php

/**
 * Menu sidebar One-Setting
 * menu_key wajib unik dalam file ini.
 */
return [
    ['menu_key' => 'dashboard', 'label' => 'Dashboard Setting', 'icon' => 'Gauge', 'href' => '/one-setting/dashboard'],
    ['menu_key' => 'setting-board', 'label' => 'Setting Board', 'icon' => 'Image', 'href' => '/one-setting/setting-board'],
    [
        'menu_key' => 'settingan-v2', 'label' => 'Settingan Manpower', 'icon' => 'Sliders', 'children' => [
            ['menu_key' => 'settingan-v2-manpower', 'label' => 'Setting Manpower', 'icon' => 'UserCheck', 'href' => '/one-setting/manpower'],
            ['menu_key' => 'settingan-v2-monitoring-mp', 'label' => 'Monitoring Settingan Manpower', 'icon' => 'ClipboardCheck', 'href' => '/one-setting/manpower/monitoring-mp'],
        ],
    ],
    [
        'menu_key' => 'setting-fleet', 'label' => 'Setting Fleet', 'icon' => 'Truck', 'children' => [
            ['menu_key' => 'setting-fleet-master', 'label' => 'Master Unit', 'icon' => 'Database', 'href' => '/one-setting/fleet/master'],
            ['menu_key' => 'setting-fleet-harian', 'label' => 'Setting Fleet', 'icon' => 'Settings', 'href' => '/one-setting/fleet'],
        ],
    ],
    ['menu_key' => 'settingan-v2-monitoring', 'label' => 'Monitoring Settingan', 'icon' => 'BarChart2', 'href' => '/one-setting/manpower/monitoring'],
    ['menu_key' => 'setting-gl', 'label' => 'Settingan GL', 'icon' => 'Settings', 'href' => '/one-setting/gl'],
    ['menu_key' => 'setting-unit-support', 'label' => 'Lokasi Unit Support', 'icon' => 'MapPin', 'href' => '/one-setting/unit-support'],
    ['menu_key' => 'setting-dozer-pc', 'label' => 'Posisi Dozer & PC', 'icon' => 'Target', 'href' => '/one-setting/dozer-pc'],
    ['menu_key' => 'setting-pw-cp', 'label' => 'Password CP', 'icon' => 'KeyRound', 'href' => '/one-setting/pw-cp'],
    ['menu_key' => 'monitoring-manpower', 'label' => 'Monitoring Settingan Manpower (Lama)', 'icon' => 'Users', 'href' => '/one-setting/monitoring-manpower'],
    ['menu_key' => 'monitoring-fleet', 'label' => 'Monitoring Settingan Fleet (Lama)', 'icon' => 'Truck', 'href' => '/one-setting/monitoring-fleet'],
];
