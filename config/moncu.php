<?php
// config/moncu.php - konfigurasi aturan MONCU (Monitoring Cuti). Bukan hardcode di service.
//
// Sumber kebenaran: workbook ROSTER.xlsx, sheet MONCU (formula di-dump, lihat laporan Step 5):
//   AKHIR   = AWAL + 13 + TAHUNAN + IZIN RESMI          (+1 bila hari AWAL = Rabu)
//   INDUKSI = AKHIR + 1
//   PLAN    = AKHIR + 71  (= INDUKSI + 70)
// Rumus yang sama dipakai di ketiga blok Excel (ADMIN PRODUKSI, HELPER PIT SERVICE, DRIVER LV).
return [
    // Hari tetap pada rumus AKHIR (Excel: "+13").
    'base_days' => 13,

    // Tambahan hari bila AWAL jatuh pada hari tertentu (Excel: TEXT(AWAL,"dddd")="Rabu" -> +1). ISO: 1=Senin .. 7=Minggu.
    'extra_day_when_weekday' => 3,   // Rabu
    'extra_days' => 1,

    // Interval PLAN dari INDUKSI (hari). Excel: PLAN = INDUKSI + 70 untuk SEMUA blok pada workbook.
    'default_interval_days' => 70,

    // Override per jabatan (dicocokkan dengan employees.jabatan_ss6 / jabatan_roster, huruf besar, "mengandung").
    // Override per karyawan (v2_moncu_employees.plan_interval_days) mengalahkan aturan ini.
    // Business rule OneClick (disepakati, Step 5.1): GROUP LEADER = 56 hari. Workbook Excel memakai 70 untuk semua blok,
    // jadi PLAN Group Leader memang SENGAJA berbeda dari Excel. Hanya PLAN yang terpengaruh (AKHIR/INDUKSI tidak).
    'interval_rules' => [
        ['jabatan_contains' => 'GROUP LEADER', 'days' => 56],
    ],

    // Hak cuti tahunan (Excel kolom G: IF(TODAY()-HIRING>365,"12","-")).
    'annual_entitlement_days' => 12,
    'entitlement_min_tenure_days' => 365,   // hak berlaku bila masa kerja > nilai ini (hari)
    // 4 periode reguler + 1 "Periode Tambahan" (nomor 5) untuk karyawan yang cuti lebih dari 4 kali dalam setahun.
    'periods_per_year' => 5,
    'regular_periods' => 4,   // periode reguler yang menentukan status SELESAI di Monitoring; periode 5 hanya tambahan

    // Nama periode & bulan (label Excel: PERIODE 1 (JAN,FEB,MAR) dst.). Hanya untuk peringatan, tidak memaksa.
    'period_months' => [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12], 5 => []],   // 5 = Periode Tambahan (tanpa bulan tertentu)

    // Batas masukan manual (penjaga salah ketik, bukan aturan bisnis).
    'max_tahunan' => 12,
    'max_izin_resmi' => 30,

    // Kode shift Roster V2 yang dihasilkan MONCU.
    // Induksi (kode ID) hanya diadakan pada hari tertentu (ISO: 1=Senin ... 7=Minggu): Selasa, Kamis, Minggu.
    // INDUKSI hasil hitung yang jatuh di hari lain ditandai MERAH di halaman MONCU agar PIC merevisi AWAL/TAHUNAN/IZIN RESMI secara manual.
    'induction_weekdays' => [2, 4, 7],

    'leave_shift_code' => 'CT',
    'induction_shift_code' => 'ID',
];
