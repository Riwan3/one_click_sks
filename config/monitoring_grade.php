<?php

// Definisi kolom modul Monitoring Grade & Jabatan (One Academy) - satu sumber
// kebenaran dipakai controller (search/filter) & Vue (header tabel + tombol filter).

return [

    'grade' => [
        ['key' => 'nrp', 'label' => 'NRP'],
        ['key' => 'nama', 'label' => 'NAMA'],
        ['key' => 'posisi', 'label' => 'POSISI'],
        ['key' => 'eg', 'label' => 'EG'],
        ['key' => 'sub_area', 'label' => 'SUB AREA'],
        ['key' => 'atr_6_bulan', 'label' => 'ATR 6 BLN'],
        ['key' => 'join_date', 'label' => 'JOIN DATE'],
        ['key' => 'efektif_grade', 'label' => 'EFEKTIF GRADE'],
        ['key' => 'efektif_position', 'label' => 'EFEKTIF POSISI'],
        ['key' => 'grade_now', 'label' => 'GRADE NOW'],
        ['key' => 'min_level', 'label' => 'MIN LEVEL'],
        ['key' => 'max_level', 'label' => 'MAX LEVEL'],
        ['key' => 'status_level', 'label' => 'STATUS LEVEL'],
        ['key' => 'suite', 'label' => 'SUITE'],
        ['key' => 'level_posisi', 'label' => 'LEVEL'],
        ['key' => 'jumlah_versatility', 'label' => 'JUMLAH VERSATILITY'],
        ['key' => 'requirement_versatility', 'label' => 'REQ VERSATILITY'],
        ['key' => 'gap_versatility', 'label' => 'GAP VERSATILITY'],
        ['key' => 'kesesuaian_versatility', 'label' => 'KESESUAIAN VERSATILITY'],
        ['key' => 'masa_kerja', 'label' => 'MASA KERJA TOTAL'],
        ['key' => 'masa_kerja_grade_sekarang', 'label' => 'MASA KERJA GRADE SKRG'],
        ['key' => 'masa_kerja_posisi_sekarang', 'label' => 'MASA KERJA POSISI SKRG'],
        ['key' => 'masa_grade_3_th_up', 'label' => 'MASA GRADE 3TH UP'],
        ['key' => 'masa_position_3th_up', 'label' => 'MASA POSISI 3TH UP'],
        ['key' => 'sertifikasi', 'label' => 'SERTIFIKASI'],
        ['key' => 'status_sertifikasi', 'label' => 'STATUS SERTIFIKASI'],
        ['key' => 'expired_date_sertifikasi', 'label' => 'EXPIRED SERTIFIKASI'],
        ['key' => 'remaining_time', 'label' => 'REMAINING SERTIFIKASI'],
        ['key' => 'remarks_grade', 'label' => 'REMARKS GRADE'],
    ],

    'grade_search_columns' => ['nama', 'nrp', 'posisi'],

    'versality' => [
        ['key' => 'nrp', 'label' => 'NRP', 'group' => null],
        ['key' => 'nama', 'label' => 'NAMA', 'group' => null],
        ['key' => 'jabatan', 'label' => 'JABATAN', 'group' => null],
        ['key' => 'versality', 'label' => 'VERSALITY', 'group' => null],

        ['key' => 'grp1_wt_ft_lt', 'label' => 'WT/FT/LT', 'group' => 'grp1'],
        ['key' => 'grp1_forklift', 'label' => 'Forklift 3-7T', 'group' => 'grp1'],
        ['key' => 'grp1_slurry_pump', 'label' => 'Slurry Pump', 'group' => 'grp1'],

        ['key' => 'grp2_pc200', 'label' => 'PC200/210/200LC LongArm/Dragflow', 'group' => 'grp2'],
        ['key' => 'grp2_crane_3t', 'label' => 'Crane Truck 3T', 'group' => 'grp2'],
        ['key' => 'grp2_tyre_handler', 'label' => 'Tyre Handler 7-10T', 'group' => 'grp2'],
        ['key' => 'grp2_bw211d', 'label' => 'BW211D/212 SV528', 'group' => 'grp2'],
        ['key' => 'grp2_wa200', 'label' => 'WA 200', 'group' => 'grp2'],
        ['key' => 'grp2_sv700', 'label' => 'SV700', 'group' => 'grp2'],

        ['key' => 'grp3_pc200_amphibi', 'label' => 'PC200 Amphibi PC300/365/400', 'group' => 'grp3'],
        ['key' => 'grp3_pc500', 'label' => 'PC 500', 'group' => 'grp3'],
        ['key' => 'grp3_d85', 'label' => 'D85/D65', 'group' => 'grp3'],
        ['key' => 'grp3_gd511', 'label' => 'GD511/GD535', 'group' => 'grp3'],
        ['key' => 'grp3_dt_20_30t', 'label' => 'DT 20-30T', 'group' => 'grp3'],

        ['key' => 'grp4_crane_10_20t', 'label' => 'Crane Truck 10-20T', 'group' => 'grp4'],
        ['key' => 'grp4_telecopic', 'label' => 'Telecopic Handler 20T', 'group' => 'grp4'],
        ['key' => 'grp4_wa400', 'label' => 'WA400/480 WA470', 'group' => 'grp4'],
        ['key' => 'grp4_pc750', 'label' => 'PC750/800 PC850', 'group' => 'grp4'],
        ['key' => 'grp4_adt_hm400', 'label' => 'ADT / HM 400', 'group' => 'grp4'],
        ['key' => 'grp4_d155', 'label' => 'D155', 'group' => 'grp4'],
        ['key' => 'grp4_wa500', 'label' => 'WA500', 'group' => 'grp4'],
        ['key' => 'grp4_gd705', 'label' => 'GD705/GD755', 'group' => 'grp4'],
        ['key' => 'grp4_dt_35_50t', 'label' => 'DT 35-50T', 'group' => 'grp4'],
        ['key' => 'grp4_lowboy', 'label' => 'Lowboy', 'group' => 'grp4'],
        ['key' => 'grp4_dm245', 'label' => 'DM245', 'group' => 'grp4'],

        ['key' => 'grp5_pc1250', 'label' => 'PC1250/CAT6015 CAT395', 'group' => 'grp5'],
        ['key' => 'grp5_hd465', 'label' => 'HD465/OHT773 WT HD465/WT OHT773', 'group' => 'grp5'],
        ['key' => 'grp5_adt_volvo', 'label' => 'ADT VOLVO A60H', 'group' => 'grp5'],
        ['key' => 'grp5_crane_50_70t', 'label' => 'Crane Truck 50-70T', 'group' => 'grp5'],
        ['key' => 'grp5_d375', 'label' => 'D375', 'group' => 'grp5'],
        ['key' => 'grp5_gd825', 'label' => 'GD 825', 'group' => 'grp5'],
        ['key' => 'grp5_wa600', 'label' => 'WA 600', 'group' => 'grp5'],
        ['key' => 'grp5_single_trailer', 'label' => 'Single Trailer 60-90T', 'group' => 'grp5'],

        ['key' => 'grp6_pc2000', 'label' => 'PC2000/CAT6020', 'group' => 'grp6'],
        ['key' => 'grp6_hd785', 'label' => 'HD785/OHT777 WT HD785/WT OHT777', 'group' => 'grp6'],
        ['key' => 'grp6_wa800', 'label' => 'WA800', 'group' => 'grp6'],
        ['key' => 'grp6_double_trailer', 'label' => 'Double Trailer 100-200T', 'group' => 'grp6'],

        ['key' => 'grp7_pc3400', 'label' => 'PC3400', 'group' => 'grp7'],
        ['key' => 'grp7_hd1500', 'label' => 'HD1500/OHT785', 'group' => 'grp7'],
        ['key' => 'grp7_d475', 'label' => 'D475', 'group' => 'grp7'],
    ],

    'versality_search_columns' => ['nama', 'nrp', 'jabatan', 'versality'],

    // Warna per grup TIDAK disimpan di sini - Tailwind (JIT) tidak memindai file PHP,
    // jadi kelas warna didefinisikan sebagai literal di DataTable.vue (GROUP_COLORS).
    'versality_groups' => [
        'grp1' => ['label' => 'EQUIPMENT GROUP 1'],
        'grp2' => ['label' => 'EQUIPMENT GROUP 2'],
        'grp3' => ['label' => 'EQUIPMENT GROUP 3'],
        'grp4' => ['label' => 'EQUIPMENT GROUP 4'],
        'grp5' => ['label' => 'EQUIPMENT GROUP 5'],
        'grp6' => ['label' => 'EQUIPMENT GROUP 6'],
        'grp7' => ['label' => 'EQUIPMENT GROUP 7'],
    ],

    'monitoring' => [
        ['key' => 'type_unit', 'label' => 'TYPE UNIT'],
        ['key' => 'actual_operator', 'label' => 'ACTUAL OPERATOR'],
        ['key' => 'grade_sesuai', 'label' => 'GRADE SESUAI'],
        ['key' => 'grade_belum_sesuai', 'label' => 'GRADE BELUM SESUAI'],
        ['key' => 'ket', 'label' => 'KETERANGAN'],
    ],

];
