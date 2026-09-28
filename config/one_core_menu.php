<?php

/**
 * Menu sidebar One-Core & Value
 * menu_key wajib unik dalam file ini (dipakai untuk role menu permission).
 */
return [
    ['menu_key' => 'user', 'label' => 'User Oneclick', 'icon' => 'ShieldLock', 'href' => '/one-core'],
    ['menu_key' => 'role-akses', 'label' => 'Role & Akses', 'icon' => 'KeySquare', 'href' => '/one-core/roles'],
    ['menu_key' => 'unit', 'label' => 'Unit Produksi', 'icon' => 'Truck', 'href' => '/one-core/units'],
    ['menu_key' => 'master-spdk', 'label' => 'Master SPDK', 'icon' => 'BookOpen', 'href' => '/one-core/spdk'],
    ['menu_key' => 'nomor-penting', 'label' => 'Nomor Penting', 'icon' => 'Phone', 'href' => '/one-core/nomor-penting'],
    ['menu_key' => 'request-reset-password', 'label' => 'Request Reset Password', 'icon' => 'KeyRound', 'href' => '/one-core/reset-password-requests'],
];
