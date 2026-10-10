<?php
// Aturan poin MOR, disalin apa adanya dari V1 database/quartal_operator_report/mor_rules.php
// (dihasilkan generator V1 dari "MOR Agustus 2026.xlsx"). Jangan diedit manual; generate ulang bila Excel acuan berubah.
return array (
  'source' => 'MOR Agustus 2026.xlsx',
  'lain2' => 
  array (
    'masa_kerja' => 3.0,
    'atr' => 0.98,
    'hm' => 0.75,
    'pdty' => 1.0,
  ),
  'sheets' => 
  array (
    'DT' => 
    array (
      'name' => 'DT',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'DT',
      ),
      'member_search' => 'operator dt',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 4.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 5.6,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 7.2,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 8.8,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 10.4,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 13.6,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 15.2,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 16.8,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 18.4,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 20.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'HD' => 
    array (
      'name' => 'HD',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'ATR',
        'O' => 'HM',
        'P' => 'PDTY (%)',
        'Q' => 'POIN FUEL',
        'R' => 'POIN TS',
        'S' => 'POIN PEMBINAAN',
        'T' => 'POIN ATR',
        'U' => 'POIN HM',
        'V' => 'POIN PDTY',
        'W' => 'TOTAL POIN',
        'X' => 'RANKING',
        'Y' => 'PRA SYARAT',
      ),
      'member_search' => 'OPERATOR HAULER',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 0.5,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 1.0,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 1.5,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 2.0,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 2.5,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 3.0,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 3.5,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 4.0,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 4.5,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 5.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 3.75,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 4.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 5.25,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 6.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 6.75,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 7.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 8.25,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 9.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 9.75,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 10.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 11.25,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 12.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 12.75,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 13.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 14.25,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 15.0,
            ),
          ),
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 1.0,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 2.0,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 3.0,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 4.0,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 5.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 6.0,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 7.0,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 8.0,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 9.0,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PDTY' => 
        array (
          'type' => 'lookup',
          'input' => 'PDTY (%)',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.2,
              1 => 20.0,
            ),
            1 => 
            array (
              0 => 0.25,
              1 => 20.83,
            ),
            2 => 
            array (
              0 => 0.3,
              1 => 21.67,
            ),
            3 => 
            array (
              0 => 0.35,
              1 => 22.5,
            ),
            4 => 
            array (
              0 => 0.4,
              1 => 23.33,
            ),
            5 => 
            array (
              0 => 0.45,
              1 => 24.17,
            ),
            6 => 
            array (
              0 => 0.5,
              1 => 25.0,
            ),
            7 => 
            array (
              0 => 0.55,
              1 => 25.83,
            ),
            8 => 
            array (
              0 => 0.6,
              1 => 26.67,
            ),
            9 => 
            array (
              0 => 0.65,
              1 => 27.5,
            ),
            10 => 
            array (
              0 => 0.7,
              1 => 28.33,
            ),
            11 => 
            array (
              0 => 0.75,
              1 => 29.17,
            ),
            12 => 
            array (
              0 => 0.8,
              1 => 30.0,
            ),
            13 => 
            array (
              0 => 0.85,
              1 => 30.83,
            ),
            14 => 
            array (
              0 => 0.9,
              1 => 31.67,
            ),
            15 => 
            array (
              0 => 0.95,
              1 => 32.5,
            ),
            16 => 
            array (
              0 => 1.0,
              1 => 33.33,
            ),
            17 => 
            array (
              0 => 1.05,
              1 => 34.17,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN PDTY',
        1 => 'POIN HM',
        2 => 'POIN ATR',
        3 => 'POIN PEMBINAAN',
        4 => 'POIN TS',
        5 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'PDTY (%)',
        2 => 'HM',
        3 => 'ATR',
        4 => 'POIN PEMBINAAN',
        5 => 'TIMESHEET',
        6 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'PDTY (%)',
          'min' => 'pdty',
        ),
        2 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        3 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'WT773' => 
    array (
      'name' => 'WT773',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'WT OHT',
      ),
      'member_search' => 'operator cat 773',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 4.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 5.6,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 7.2,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 8.8,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 10.4,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 13.6,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 15.2,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 16.8,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 18.4,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 20.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'WA&WTDT' => 
    array (
      'name' => 'WA&WTDT',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'WT OHT',
      ),
      'member_search' => 'operator WA',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 4.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 5.6,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 7.2,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 8.8,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 10.4,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 13.6,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 15.2,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 16.8,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 18.4,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 20.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'VIBRO' => 
    array (
      'name' => 'VIBRO',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'WT OHT',
      ),
      'member_search' => 'operator vb',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 4.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 5.6,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 7.2,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 8.8,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 10.4,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 13.6,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 15.2,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 16.8,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 18.4,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 20.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'GD' => 
    array (
      'name' => 'GD',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'GD',
      ),
      'member_search' => 'operator gd',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.1,
              1 => 2.0,
            ),
            1 => 
            array (
              0 => 0.2,
              1 => 4.0,
            ),
            2 => 
            array (
              0 => 0.3,
              1 => 6.0,
            ),
            3 => 
            array (
              0 => 0.4,
              1 => 8.0,
            ),
            4 => 
            array (
              0 => 0.5,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.6,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.7,
              1 => 14.0,
            ),
            7 => 
            array (
              0 => 0.8,
              1 => 16.0,
            ),
            8 => 
            array (
              0 => 0.9,
              1 => 18.0,
            ),
            9 => 
            array (
              0 => 1.01,
              1 => 20.0,
            ),
            10 => 
            array (
              0 => 1.11,
              1 => 18.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'PC200' => 
    array (
      'name' => 'PC200',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'PC200',
      ),
      'member_search' => 'PC 200',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 1.38777878078145E-16,
              1 => 2.0,
            ),
            1 => 
            array (
              0 => 0.1,
              1 => 4.0,
            ),
            2 => 
            array (
              0 => 0.2,
              1 => 6.0,
            ),
            3 => 
            array (
              0 => 0.3,
              1 => 8.0,
            ),
            4 => 
            array (
              0 => 0.4,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.5,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.6,
              1 => 14.0,
            ),
            7 => 
            array (
              0 => 0.7,
              1 => 16.0,
            ),
            8 => 
            array (
              0 => 0.8,
              1 => 18.0,
            ),
            9 => 
            array (
              0 => 0.9,
              1 => 20.0,
            ),
            10 => 
            array (
              0 => 1.01,
              1 => 16.0,
            ),
            11 => 
            array (
              0 => 1.11,
              1 => 14.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'DZ85' => 
    array (
      'name' => 'DZ85',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'DZ 85',
      ),
      'member_search' => 'operator d85',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 1.38777878078145E-16,
              1 => 2.0,
            ),
            1 => 
            array (
              0 => 0.1,
              1 => 4.0,
            ),
            2 => 
            array (
              0 => 0.2,
              1 => 6.0,
            ),
            3 => 
            array (
              0 => 0.3,
              1 => 8.0,
            ),
            4 => 
            array (
              0 => 0.4,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.5,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.6,
              1 => 14.0,
            ),
            7 => 
            array (
              0 => 0.7,
              1 => 16.0,
            ),
            8 => 
            array (
              0 => 0.8,
              1 => 18.0,
            ),
            9 => 
            array (
              0 => 0.9,
              1 => 20.0,
            ),
            10 => 
            array (
              0 => 1.01,
              1 => 16.0,
            ),
            11 => 
            array (
              0 => 1.11,
              1 => 14.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'DZ155' => 
    array (
      'name' => 'DZ155',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'DZ 155',
      ),
      'member_search' => 'operator d155',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 1.38777878078145E-16,
              1 => 2.0,
            ),
            1 => 
            array (
              0 => 0.1,
              1 => 4.0,
            ),
            2 => 
            array (
              0 => 0.2,
              1 => 6.0,
            ),
            3 => 
            array (
              0 => 0.3,
              1 => 8.0,
            ),
            4 => 
            array (
              0 => 0.4,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.5,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.6,
              1 => 14.0,
            ),
            7 => 
            array (
              0 => 0.7,
              1 => 16.0,
            ),
            8 => 
            array (
              0 => 0.8,
              1 => 18.0,
            ),
            9 => 
            array (
              0 => 0.9,
              1 => 20.0,
            ),
            10 => 
            array (
              0 => 1.01,
              1 => 16.0,
            ),
            11 => 
            array (
              0 => 1.11,
              1 => 14.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'PC300' => 
    array (
      'name' => 'PC300',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'ATR',
        'P' => 'POIN FUEL',
        'Q' => 'POIN TS',
        'R' => 'POIN PEMBINAAN',
        'S' => 'POIN HM',
        'T' => 'POIN ATR',
        'U' => 'TOTAL POIN',
        'V' => 'RANKING',
        'W' => 'PRA SYARAT',
        'Y' => 'PC300',
      ),
      'member_search' => 'operator PC 300',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 4.2,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 5.4,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 6.6,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 7.8,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 9.0,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 10.2,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 11.4,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 12.6,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 13.8,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 15.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 4.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 5.6,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 7.2,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 8.8,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 10.4,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 12.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 13.6,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 15.2,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 16.8,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 18.4,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 20.0,
            ),
          ),
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 5.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 6.25,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 7.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 8.75,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 10.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 11.25,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 12.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 13.75,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 15.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 16.25,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 17.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 18.75,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 20.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 21.25,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 22.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 23.75,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 25.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN ATR',
        1 => 'POIN HM',
        2 => 'POIN PEMBINAAN',
        3 => 'POIN TS',
        4 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'TOTAL POIN',
        1 => 'ATR',
        2 => 'HM',
        3 => 'POIN PEMBINAAN',
        4 => 'TIMESHEET',
        5 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        2 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'PC395' => 
    array (
      'name' => 'PC395',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'PDTY (%)',
        'P' => 'ATR',
        'Q' => 'POIN FUEL',
        'R' => 'POIN TS',
        'S' => 'POIN PEMBINAAN',
        'T' => 'POIN ATR',
        'U' => 'POIN HM',
        'V' => 'POIN PDTY',
        'W' => 'TOTAL POIN',
        'X' => 'RANKING',
        'Y' => 'PRA SYARAT',
      ),
      'member_search' => 'operator cat 395',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 0.5,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 1.0,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 1.5,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 2.0,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 2.5,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 3.0,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 3.5,
            ),
            8 => 
            array (
              0 => 0.95,
              1 => 4.5,
            ),
            9 => 
            array (
              0 => 0.98,
              1 => 5.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 3.75,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 4.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 5.25,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 6.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 6.75,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 7.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 8.25,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 9.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 9.75,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 10.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 11.25,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 12.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 12.75,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 13.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 14.25,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 15.0,
            ),
          ),
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 1.0,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 2.0,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 3.0,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 4.0,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 5.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 6.0,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 7.0,
            ),
            8 => 
            array (
              0 => 1.06,
              1 => 9.0,
            ),
            9 => 
            array (
              0 => 1.15,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PDTY' => 
        array (
          'type' => 'lookup',
          'input' => 'PDTY (%)',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 20.0,
            ),
            1 => 
            array (
              0 => 0.2,
              1 => 21.25,
            ),
            2 => 
            array (
              0 => 0.25,
              1 => 22.5,
            ),
            3 => 
            array (
              0 => 0.3,
              1 => 23.75,
            ),
            4 => 
            array (
              0 => 0.35,
              1 => 25.0,
            ),
            5 => 
            array (
              0 => 0.4,
              1 => 26.25,
            ),
            6 => 
            array (
              0 => 0.45,
              1 => 27.5,
            ),
            7 => 
            array (
              0 => 0.5,
              1 => 28.75,
            ),
            8 => 
            array (
              0 => 0.55,
              1 => 30.0,
            ),
            9 => 
            array (
              0 => 0.6,
              1 => 31.25,
            ),
            10 => 
            array (
              0 => 0.65,
              1 => 32.5,
            ),
            11 => 
            array (
              0 => 0.7,
              1 => 33.75,
            ),
            12 => 
            array (
              0 => 0.75,
              1 => 35.0,
            ),
            13 => 
            array (
              0 => 0.8,
              1 => 36.25,
            ),
            14 => 
            array (
              0 => 0.85,
              1 => 37.5,
            ),
            15 => 
            array (
              0 => 0.9,
              1 => 38.75,
            ),
            16 => 
            array (
              0 => 0.95,
              1 => 40.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN PDTY',
        1 => 'POIN HM',
        2 => 'POIN ATR',
        3 => 'POIN PEMBINAAN',
        4 => 'POIN TS',
        5 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'POIN PDTY',
        1 => 'ATR',
        2 => 'PDTY (%)',
        3 => 'HM',
        4 => 'POIN TS',
        5 => 'TIMESHEET',
        6 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'PDTY (%)',
          'min' => 'pdty',
        ),
        2 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        3 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'PC1250 6015' => 
    array (
      'name' => 'PC1250 6015',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'PDTY (%)',
        'P' => 'ATR',
        'Q' => 'POIN FUEL',
        'R' => 'POIN TS',
        'S' => 'POIN PEMBINAAN',
        'T' => 'POIN ATR',
        'U' => 'POIN HM',
        'V' => 'POIN PDTY',
        'W' => 'TOTAL POIN',
        'X' => 'RANKING',
        'Y' => 'PRA SYARAT',
      ),
      'member_search' => 'operator pc 1250',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 0.5,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 1.0,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 1.5,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 2.0,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 2.5,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 3.0,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 3.5,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 4.0,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 4.5,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 5.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 3.75,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 4.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 5.25,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 6.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 6.75,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 7.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 8.25,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 9.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 9.75,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 10.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 11.25,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 12.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 12.75,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 13.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 14.25,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 15.0,
            ),
          ),
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 1.0,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 2.0,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 3.0,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 4.0,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 5.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 6.0,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 7.0,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 8.0,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 9.0,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PDTY' => 
        array (
          'type' => 'lookup',
          'input' => 'PDTY (%)',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 20.0,
            ),
            1 => 
            array (
              0 => 0.2,
              1 => 21.25,
            ),
            2 => 
            array (
              0 => 0.25,
              1 => 22.5,
            ),
            3 => 
            array (
              0 => 0.3,
              1 => 23.75,
            ),
            4 => 
            array (
              0 => 0.35,
              1 => 25.0,
            ),
            5 => 
            array (
              0 => 0.4,
              1 => 26.25,
            ),
            6 => 
            array (
              0 => 0.45,
              1 => 27.5,
            ),
            7 => 
            array (
              0 => 0.5,
              1 => 28.75,
            ),
            8 => 
            array (
              0 => 0.55,
              1 => 30.0,
            ),
            9 => 
            array (
              0 => 0.6,
              1 => 31.25,
            ),
            10 => 
            array (
              0 => 0.65,
              1 => 32.5,
            ),
            11 => 
            array (
              0 => 0.7,
              1 => 33.75,
            ),
            12 => 
            array (
              0 => 0.75,
              1 => 35.0,
            ),
            13 => 
            array (
              0 => 0.8,
              1 => 36.25,
            ),
            14 => 
            array (
              0 => 0.85,
              1 => 37.5,
            ),
            15 => 
            array (
              0 => 0.9,
              1 => 38.75,
            ),
            16 => 
            array (
              0 => 0.95,
              1 => 40.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN PDTY',
        1 => 'POIN HM',
        2 => 'POIN ATR',
        3 => 'POIN PEMBINAAN',
        4 => 'POIN TS',
        5 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'POIN PDTY',
        1 => 'ATR',
        2 => 'PDTY (%)',
        3 => 'HM',
        4 => 'POIN TS',
        5 => 'TIMESHEET',
        6 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'PDTY (%)',
          'min' => 'pdty',
        ),
        2 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        3 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
    'PC2000' => 
    array (
      'name' => 'PC2000',
      'columns' => 
      array (
        'B' => 'NO',
        'C' => 'NIK',
        'D' => 'NAMA KARYAWAN',
        'E' => 'MASA KERJA',
        'G' => 'PERUSAHAAN',
        'H' => 'DEPARTEMEN',
        'I' => 'JABATAN',
        'J' => 'FUEL',
        'K' => 'TIMESHEET',
        'L' => 'N/A (20)',
        'M' => 'TOTAL PEMBINAAN',
        'N' => 'HM',
        'O' => 'PDTY (%)',
        'P' => 'ATR',
        'Q' => 'POIN FUEL',
        'R' => 'POIN TS',
        'S' => 'POIN PEMBINAAN',
        'T' => 'POIN ATR',
        'U' => 'POIN HM',
        'V' => 'POIN PDTY',
        'W' => 'TOTAL POIN',
        'X' => 'RANKING',
        'Y' => 'PRA SYARAT',
      ),
      'member_search' => 'operator pc 2000',
      'points' => 
      array (
        'POIN FUEL' => 
        array (
          'type' => 'lookup',
          'input' => 'FUEL',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.71,
              1 => 0.5,
            ),
            2 => 
            array (
              0 => 0.74,
              1 => 1.0,
            ),
            3 => 
            array (
              0 => 0.77,
              1 => 1.5,
            ),
            4 => 
            array (
              0 => 0.8,
              1 => 2.0,
            ),
            5 => 
            array (
              0 => 0.83,
              1 => 2.5,
            ),
            6 => 
            array (
              0 => 0.86,
              1 => 3.0,
            ),
            7 => 
            array (
              0 => 0.89,
              1 => 3.5,
            ),
            8 => 
            array (
              0 => 0.92,
              1 => 4.0,
            ),
            9 => 
            array (
              0 => 0.95,
              1 => 4.5,
            ),
            10 => 
            array (
              0 => 0.98,
              1 => 5.0,
            ),
          ),
        ),
        'POIN TS' => 
        array (
          'type' => 'lookup',
          'input' => 'TIMESHEET',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 7.0,
            ),
            1 => 
            array (
              0 => 0.9,
              1 => 8.0,
            ),
            2 => 
            array (
              0 => 0.95,
              1 => 9.0,
            ),
            3 => 
            array (
              0 => 1.0,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PEMBINAAN' => 
        array (
          'type' => 'pembinaan',
          'a' => 'N/A (20)',
          'b' => 'TOTAL PEMBINAAN',
          'cap' => 20.0,
        ),
        'POIN ATR' => 
        array (
          'type' => 'lookup',
          'input' => 'ATR',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.8284,
              1 => 3.0,
            ),
            1 => 
            array (
              0 => 0.8385,
              1 => 3.75,
            ),
            2 => 
            array (
              0 => 0.8486,
              1 => 4.5,
            ),
            3 => 
            array (
              0 => 0.8587,
              1 => 5.25,
            ),
            4 => 
            array (
              0 => 0.8688,
              1 => 6.0,
            ),
            5 => 
            array (
              0 => 0.8789,
              1 => 6.75,
            ),
            6 => 
            array (
              0 => 0.889,
              1 => 7.5,
            ),
            7 => 
            array (
              0 => 0.8991,
              1 => 8.25,
            ),
            8 => 
            array (
              0 => 0.9092,
              1 => 9.0,
            ),
            9 => 
            array (
              0 => 0.9193,
              1 => 9.75,
            ),
            10 => 
            array (
              0 => 0.9294,
              1 => 10.5,
            ),
            11 => 
            array (
              0 => 0.9395,
              1 => 11.25,
            ),
            12 => 
            array (
              0 => 0.9496,
              1 => 12.0,
            ),
            13 => 
            array (
              0 => 0.9597,
              1 => 12.75,
            ),
            14 => 
            array (
              0 => 0.9698,
              1 => 13.5,
            ),
            15 => 
            array (
              0 => 0.9799,
              1 => 14.25,
            ),
            16 => 
            array (
              0 => 0.99,
              1 => 15.0,
            ),
          ),
        ),
        'POIN HM' => 
        array (
          'type' => 'lookup',
          'input' => 'HM',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 0.0,
            ),
            1 => 
            array (
              0 => 0.34,
              1 => 1.0,
            ),
            2 => 
            array (
              0 => 0.43,
              1 => 2.0,
            ),
            3 => 
            array (
              0 => 0.52,
              1 => 3.0,
            ),
            4 => 
            array (
              0 => 0.61,
              1 => 4.0,
            ),
            5 => 
            array (
              0 => 0.7,
              1 => 5.0,
            ),
            6 => 
            array (
              0 => 0.79,
              1 => 6.0,
            ),
            7 => 
            array (
              0 => 0.88,
              1 => 7.0,
            ),
            8 => 
            array (
              0 => 0.97,
              1 => 8.0,
            ),
            9 => 
            array (
              0 => 1.06,
              1 => 9.0,
            ),
            10 => 
            array (
              0 => 1.15,
              1 => 10.0,
            ),
          ),
        ),
        'POIN PDTY' => 
        array (
          'type' => 'lookup',
          'input' => 'PDTY (%)',
          'table' => 
          array (
            0 => 
            array (
              0 => 0.0,
              1 => 20.0,
            ),
            1 => 
            array (
              0 => 0.2,
              1 => 21.25,
            ),
            2 => 
            array (
              0 => 0.25,
              1 => 22.5,
            ),
            3 => 
            array (
              0 => 0.3,
              1 => 23.75,
            ),
            4 => 
            array (
              0 => 0.35,
              1 => 25.0,
            ),
            5 => 
            array (
              0 => 0.4,
              1 => 26.25,
            ),
            6 => 
            array (
              0 => 0.45,
              1 => 27.5,
            ),
            7 => 
            array (
              0 => 0.5,
              1 => 28.75,
            ),
            8 => 
            array (
              0 => 0.55,
              1 => 30.0,
            ),
            9 => 
            array (
              0 => 0.6,
              1 => 31.25,
            ),
            10 => 
            array (
              0 => 0.65,
              1 => 32.5,
            ),
            11 => 
            array (
              0 => 0.7,
              1 => 33.75,
            ),
            12 => 
            array (
              0 => 0.75,
              1 => 35.0,
            ),
            13 => 
            array (
              0 => 0.8,
              1 => 36.25,
            ),
            14 => 
            array (
              0 => 0.85,
              1 => 37.5,
            ),
            15 => 
            array (
              0 => 0.9,
              1 => 38.75,
            ),
            16 => 
            array (
              0 => 0.95,
              1 => 40.0,
            ),
          ),
        ),
      ),
      'total_of' => 
      array (
        0 => 'POIN PDTY',
        1 => 'POIN HM',
        2 => 'POIN ATR',
        3 => 'POIN PEMBINAAN',
        4 => 'POIN TS',
        5 => 'POIN FUEL',
      ),
      'rank_keys' => 
      array (
        0 => 'POIN PDTY',
        1 => 'ATR',
        2 => 'PDTY (%)',
        3 => 'HM',
        4 => 'POIN TS',
        5 => 'TIMESHEET',
        6 => 'FUEL',
      ),
      'prasyarat' => 
      array (
        0 => 
        array (
          'input' => 'MASA KERJA (BULAN)',
          'min' => 'masa_kerja',
        ),
        1 => 
        array (
          'input' => 'PDTY (%)',
          'min' => 'pdty',
        ),
        2 => 
        array (
          'input' => 'ATR',
          'min' => 'atr',
        ),
        3 => 
        array (
          'input' => 'HM',
          'min' => 'hm',
        ),
      ),
      'prasyarat_no_sp' => true,
      'quota_pct' => 
      array (
        'IS' => 0.05,
        'BS' => 0.15,
      ),
    ),
  ),
  'rank_kategori' => 
  array (
    'OPERATOR D155' => 'DZ155',
    'OPERATOR D85' => 'DZ85',
    'OPERATOR HAULER' => 'HD',
    'OPERATOR GD 825' => 'GD',
    'OPERATOR PC 1250/6015' => 'PC1250 6015',
    'OPERATOR PC 200' => 'PC200',
    'OPERATOR PC 2000' => 'PC2000',
    'OPERATOR CAT 395' => 'PC395',
    'OPERATOR PC 300' => 'PC300',
  ),
);
