<?php

// Rubrik penilaian GMP per tipe, disalin dari V1 (views/database/gmp/*-form.php).
// Dipakai oleh OneGmpController::penilaian() untuk menampilkan tabel rubrik di form input.
return array (
  'MHR' => 
  array (
    0 => 
    array (
      'number' => 1,
      'title' => 'Kondisi Permukaan Jalan Undulasi & Rutting (Lendutan permukaan jalan sejajar lintasan ban) & Kondisi Jalan Berlubang',
      'rubricTitle' => 'Kondisi Permukaan Jalan Undulasi & Rutting (Lendutan permukaan jalan sejajar lintasan ban) & Kondisi Jalan Berlubang',
      'notes' => 
      array (
        0 => '- Tidak ada undulasi permukaan jalan >5 cm',
        1 => '- Tidak ada rutting pada permukaan jalan >5 cm dan panjang <4 meter',
        2 => '- Tidak ada jalan berlubang c diameter 30 cm dan kedalaman 10 cm',
      ),
      'columns' => 
      array (
        0 => 'Undulasi',
        1 => 'Rutting',
        2 => 'Lubang',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada',
            1 => 'Tidak ada',
            2 => 'Tidak ada',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '3% dari total panjang jalan',
            1 => '3% dari total panjang jalan',
            2 => '',
          ),
          'black' => 
          array (
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '4 - 5% dari total panjang jalan',
            1 => '4 - 5% dari total panjang jalan',
            2 => '',
          ),
          'black' => 
          array (
            2 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '> 5% dari total panjang jalan',
            1 => '> 5% dari total panjang jalan',
            2 => 'Ada jalan berlubang',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 5,
      ),
      'format' => 'table',
    ),
    1 => 
    array (
      'number' => 2,
      'title' => 'Kemiringan Jalan atau Grade pada Tanjakan / Turunan',
      'rubricTitle' => 'Kemiringan Jalan atau Grade pada Tanjakan / Turunan',
      'notes' => 
      array (
        0 => 'Sesuai standar 8% (minimal segmen jalan 10 Meter)',
        1 => 'Jika overgrade, wajib dilengkapi kelengkapan teknikal standard grade tinggi: Rambu jalan overgrade, rambu dilarang penyiraman dan median sepanjang jalan tersebut.',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'On Grade ≤8%',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Overgrade (0.5-1%) dengan rambu tidak lengkap',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Overgrade >1% dengan rambu tidak lengkap',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Overgrade tanpa terdapat rambu',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 5,
      ),
      'format' => 'table',
    ),
    2 => 
    array (
      'number' => 3,
      'title' => 'Lebar Jalan Angkut yang dilakukan penilaian',
      'rubricTitle' => 'Lebar Jalan Angkut (Road Width)',
      'notes' => 
      array (
        0 => '- Lebar jalan angkut >= 3.5x lebar unit terbesar persegmen jalan',
        1 => '- Kontrol rambu (wajib terpenuhi semua saat ada penyempitan jalan): rambu penyempitan, rambu prioritas, rambu maintenance, dan rambu batas kecepatan',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada penyempitan jalan (lebar jalan >= 3.5 x lebar unit terbesar)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Terdapat penyempitan jalan dengan kontrol rambu-rambu yang lengkap',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Terdapat penyempitan (<3.5x lebar unit terbesar) / rambu tidak lengkap and atau tanpa kontrol rambu-rambu',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    3 => 
    array (
      'number' => 4,
      'title' => 'Kemiringan Jalan pada Tikungan (Superelevasi %) dan Profil Melintang Jalan / Crossfall (%)',
      'rubricTitle' => 'Kemiringan Jalan pada Tikungan (Superelevasi %) dan Profil Melintang Jalan / Crossfall (%)',
      'notes' => 
      array (
        0 => '- Tersedia superelevasi jalan menikung yang standar (2% - 4%)',
        1 => '- Crossfall Jalan terbentuk 2% untuk mengarahkan air pada badan jalan ke bahu jalan sesuai rambu crossfall jalan (tidak flat)',
      ),
      'columns' => 
      array (
        0 => 'Superelevasi',
        1 => 'Arah',
        2 => 'Crossfall',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => '4%',
            1 => 'Mengarah kedalam tikungan',
            2 => 'Crossfall sesuai standar dan terbentuk sepanjang segmen',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '2.5 - 2.9 %',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '2 - 2.4 %',
            1 => '',
            2 => 'Crossfall terbentuk < 40 meter per segmen jalan',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '< 2% atau > 4%',
            1 => 'Superelevasi terbalik',
            2 => 'Crossfall tidak terbentuk > 40 meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 5,
      ),
      'format' => 'table',
    ),
    4 => 
    array (
      'number' => 5,
      'title' => 'Bundwall atau Tanggul Pengaman',
      'rubricTitle' => 'Bundwall / Tanggul Pengaman',
      'notes' => 
      array (
        0 => 'Tersedia tanggul pengaman standar sepanjang segmen jalan dengan parameter :',
        1 => '- >= 3/4 tinggi ban Truk terbesar,',
        2 => '- Lebar atas minimal 1.5 m dan terbentuk kemiringan bundwall 60°.',
        3 => '- Material bundwall tidak menggunakan material pasir dan material spoil.',
        4 => '- Tidak ada material spoil yang ditempatkan di bundwall .',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sepanjang segmen jalan terdapat tanggul standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '4 - 5% per segmen jalan, tanggul tidak standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '>5% per segmen jalan, tanggul tidak standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    5 => 
    array (
      'number' => 6,
      'title' => 'Drainase, Air Permukaan, dan Sedimentasi',
      'rubricTitle' => 'Drainase, Air Permukaan, dan Sedimentasi',
      'notes' => 
      array (
        0 => '- Terdapat paritan pada sisi luar jalan untuk menangkap catchment aliran air',
        1 => '- Terdapat sodetan pada bundwall agar air dapat mengalir lancar masuk ke paritan',
        2 => '- Tidak terdapat sedimentasi',
        3 => '- Tidak terdapat titik genangan',
      ),
      'columns' => 
      array (
        0 => 'Drainase Paritan',
        1 => 'Sodetan',
        2 => 'Posisi Sedimentasi pada Paritan',
        3 => 'Titik Genangan',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Drainase paritan ada pada sisi luar jalan',
            1 => 'Terdapat sodetan dan tidak tertutup material',
            2 => '≥ 0,5 meter dari badan jalan',
            3 => 'Tidak ada',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
            1 => 'Ada, tetapi tertutup material',
            2 => '',
            3 => '2 titik genangan/ potensi',
          ),
          'black' => 
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak terdapat drainase paritan',
            1 => 'Tidak ada sodetan',
            2 => '≤ 0,5 meter dari badan jalan',
            3 => '>2 titik genangan/ potensi',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 5,
      ),
      'format' => 'table',
    ),
    6 => 
    array (
      'number' => 7,
      'title' => 'Culvert / Gorong - Gorong',
      'rubricTitle' => 'Culvert / Gorong - Gorong',
      'notes' => 
      array (
        0 => '- Tersedia culvert dan berfungsi dengan baik.',
        1 => '- Tidak ada sedimentasi yang menutup gorong-gorong.',
      ),
      'columns' => 
      array (
        0 => 'Tersedia Culvert / Gorong-gorong',
        1 => 'Sedimentasi pada Culvert / Gorong - gorong',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sesuai plan dan dalam kondisi baik',
            1 => 'Tidak ada sedimentasi',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Sesuai plan dan dalam kondisi baik',
            1 => '1 - 15 % sedimentasi',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Line culvert kurang dari Plan',
            1 => '> 15 % sedimentasi',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 5,
      ),
      'format' => 'table',
    ),
    7 => 
    array (
      'number' => 8,
      'title' => 'Pembatas Lajur Jalan / Median / Divider & Bundaran pada Persimpangan',
      'rubricTitle' => 'Pembatas Lajur Jalan / Median / Divider & Bundaran pada Persimpangan',
      'notes' => 
      array (
        0 => 'Terdapat divider pada area persimpangan dan divider pada tikungan (sudut <=90°), Dimensi divider standard :',
        1 => '- Lebar atas >= lebar ban truck terbesar',
        2 => '- Tinggi >= 1/2 diameter ban truck terbesar',
        3 => '- Panjang minimal 5 meter di persimpangan',
        4 => '- Divider sepanjang tikungan (sampai segmen jalan lurus)',
      ),
      'columns' => 
      array (
        0 => 'Divider',
        1 => 'Bundaran',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sesuai standar dan terdapat di persimpangan dan tikungan',
            1 => 'Sesuai standar pada persimpangan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Terdapat Divider dan Bundaran tetapi salah satu atau keduanya dari Divider atau Bundaran tidak sesuai standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => 2,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak terdapat Divider maupun Bundaran',
          ),
          'black' => 
          array (
          ),
          'colspan' => 2,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    8 => 
    array (
      'number' => 9,
      'title' => 'Sudut Persimpangan',
      'rubricTitle' => 'Sudut Persimpangan',
      'notes' => 
      array (
        0 => 'Sudut persimpangan >=70°',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sudut persimpangan >= 70°',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Sudut persimpangan < 70° dengan control sebagian',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Sudut persimpangan < 70° tanpa control',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    9 => 
    array (
      'number' => 10,
      'title' => 'Tumpukan Material Sisa (Spoil)',
      'rubricTitle' => 'Tumpukan Material Sisa (Spoil)',
      'notes' => 
      array (
        0 => 'Akumulasi tumpukan material spoil di bahu jalan dengan lebar (Penilaian tidak termasuk spoil box )',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada spoil sepanjang segmen jalan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Total spoil 50 - 70 meter dalam satu segmen jalan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Total spoil 70 - 80 meter dalam satu segmen jalan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Total panjang spoil >80 meter (>2 guide post) dalam satu segmen jalan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 5,
      ),
      'format' => 'table',
    ),
    10 => 
    array (
      'number' => 11,
      'title' => 'Rambu Keselamatan Jalan Angkut & Patok Acuan Tepi Jalan',
      'rubricTitle' => 'Rambu Keselamatan Jalan Angkut & Patok Acuan Tepi Jalan',
      'notes' => 
      array (
        0 => '- Tersedia dan sesuai standar Traffic Rules dan terawat (berdiri tegak, bersih dan terlihat jelas reflektor).',
        1 => '- Tersedia Guide Post standar Traffic Rules dalam suatu segmen jalan dengan tinggi 2.5m-3.5m dari bahu badan jalan Atau minimal 1.5 meter diatas bundwall .',
      ),
      'columns' => 
      array (
        0 => 'Rambu Traffic Rules',
        1 => 'Guide post',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sesuai Traffic rules dan terawat',
            1 => 'Tiap 40 meter, standar dan terawat',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Sesuai Traffic rules dan tidak terawat',
            1 => 'Guide post tidak terawat',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Rambu tidak sesuai fungsi, tidak terpenuhi dan tidak terawat',
            1 => 'Jarak antar Guide post >40 meter, tidak standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    11 => 
    array (
      'number' => 12,
      'title' => 'Debu & Blindspot (Visibility)',
      'rubricTitle' => 'Debu & Blindspot (Visibility)',
      'notes' => 
      array (
        0 => '- Kondisi jalan bebas debu',
        1 => '- Tidak terdapat blind spot pada persimpangan, tanjakan dan turunan jarak 30 meter',
      ),
      'columns' => 
      array (
        0 => 'Debu',
        1 => 'Blindspot',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Debu terkontrol, jarak pandang > 80 meter (3 guide post)',
            1 => 'Tidak terdapat blindspot',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Debu tidak terkontrol, jarak pandang 40 - 80 meter (2-3 guide post )',
            1 => 'Terdapat blindspot dengan control lengkap : Rambu giveway , cermin cembung dan rambu batas kecepatan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Debu tebal jarak pandang <40 meter (2 guide post )',
            1 => 'Terdapat blindspot tanpa control',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
  ),
  'MFOB' =>
  array (
    0 =>
    array (
      'number' => 1,
      'title' => 'Pilih nilai yang sesuai dengan Class Excavator',
      'rubricTitle' => 'Tinggi Jenjang Penggalian',
      'columns' =>
      array (
        0 => 'PC 1250 Class / CAT 395 / R9100 / EX 1200',
        1 => 'PC 2000 Class / EX 2600 / R9200',
        2 => 'PC 4000 Class / CAT 6060 / EX 5600',
        3 => '4000 Class',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => '4 m',
            1 => '4 m',
            2 => '≥4 m',
            3 => '8 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '3 - <4 m',
            1 => '3 - <4 m',
            2 => '3 - <4 m',
            3 => '7 - <8 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '2 - <3 m',
            1 => '2 - <3 m',
            2 => '2 - <3 m',
            3 => '6 - <7 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => '<2 m',
            1 => '<2 m',
            2 => '<2 m',
            3 => '<6 m atau >8 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    1 =>
    array (
      'number' => 2,
      'title' => 'Pilih salah satu yang menggambarkan Luas Area Penggalian Front Loading',
      'rubricTitle' => 'Luasan Area Penggalian (Working Geometry)',
      'columns' =>
      array (
        0 => 'Kondisi',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => '≥ 4x Lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '3 - <4x Lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '2 - <3x Lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => '<2x Lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    2 =>
    array (
      'number' => 3,
      'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah',
      'rubricTitle' => 'Grade Box',
      'columns' =>
      array (
        0 => 'Gradebox dan Form Validasi',
        1 => 'Jarak terhadap Area Front Loading',
        2 => 'Validasi PIC',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Tersedia dalam kondisi standard',
            1 => '30 - 50 m dan dibarikade',
            2 => 'Tervalidasi oleh kedua PIC (pengawas dan survey) dan tersubmit secara online',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '30 - 50 m tidak dibarikade',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => 'Tervalidasi oleh kedua PIC (pengawas dan survey) dan belum tersubmit secara online',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '<30 m atau >50 m dan dibarikade',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Tersedia dalam kondisi standard tetapi kurang baik',
            1 => '<30 m atau >50 m dan tidak dibarikade',
            2 => 'Tervalidasi oleh survey saja',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak tersedia grade box atau tersedia namun tidak tervalidasi oleh survey',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
          ),
          'colspan' => 3,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    3 =>
    array (
      'number' => 4,
      'title' => 'Pilih Kondisi Lantai Kerja',
      'rubricTitle' => 'Lantai Kerja Sesuai Rencana Elevasi (On Grade)',
      'columns' =>
      array (
        0 => 'Kondisi',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'On Grade, sesuai dengan benang ukur',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Under Cut (benang ukur berada di bawah spotlight HD)',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Over Cut (benang ukur berada di atas spotlight HD) dan/atau grade box tidak tersedia atau tidak update',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    4 =>
    array (
      'number' => 5,
      'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah',
      'rubricTitle' => 'Batas Penggalian (Digging Limit) & Patok Acuan Kemiringan Lereng Final Design/Batter Peg/Bowplank dan Patok Crestline',
      'columns' =>
      array (
        0 => 'Patok Batas Acuan Penggalian',
        1 => 'Better Peg/Bow Plank',
        2 => 'Patok Crestline',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Ada setiap 10 m dan sesuai standard patok',
            1 => 'Ada setiap 30 m dan sesuai standard',
            2 => 'Ada setiap 15 m dan sesuai standard',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => 'Ada dengan jarak > 10 m tetapi tidak ada temuan deviasi',
            1 => 'Ada dengan jarak > 30 m tetapi tidak ada temuan deviasi',
            2 => 'Ada dengan jarak > 15 m tetapi tidak ada temuan deviasi',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Ada dengan jarak > 10 m dan ada temuan deviasi',
            1 => 'Ada dengan jarak > 30 m dan ada temuan deviasi',
            2 => 'Ada dengan jarak > 15 m dan ada temuan deviasi',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak ada',
            1 => 'Tidak ada',
            2 => 'Tidak ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    5 =>
    array (
      'number' => 6,
      'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah',
      'rubricTitle' => 'Kondisi Lantai dan Akses menuju Area Kerja Penggalian',
      'columns' =>
      array (
        0 => 'Luasan Undulasi',
        1 => 'Kedalaman Undulasi',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Tidak ada undulasi',
            1 => '0 cm',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '< 10 cm',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => 'Luasan undulasi <5% area kerja',
            1 => '',
          ),
          'black' =>
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '10 - 15 cm',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Luasan undulasi 5-10% area kerja',
            1 => '15 - 20 cm',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Luasan undulasi >10% area kerja',
            1 => '≥ 20 cm',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    6 =>
    array (
      'number' => 7,
      'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 2 item penilaian dibawah',
      'rubricTitle' => 'Drainase & Air Permukaan',
      'columns' =>
      array (
        0 => 'Arah Aliran Air',
        1 => 'Potensi Genangan',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Jelas (contour drainage min. 1%)',
            1 => 'Tidak terbentuk potensi',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '<5% area (2.5 m)',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '5-10% area (2.5 - 5 m)',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak ada',
            1 => '>10% area (5 m)',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    7 =>
    array (
      'number' => 8,
      'title' => 'Pilih Nilai terendah paling menggambarkan kondisi dari 3 item penilaian dibawah',
      'rubricTitle' => 'Tanggul Pengaman (Bundwall) dan Rambu Keselamatan',
      'columns' =>
      array (
        0 => 'Bundwall',
        1 => 'Lebar Sodetan',
        2 => 'Rambu Keselamatan',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Tersedia, berukuran ≥ 3/4 tinggi ban truk terbesar di area front dengan beda ketinggian',
            1 => 'Ada, lebar sesuai standard (1 m)',
            2 => 'Ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '<5% (2.5 m) area front tidak standard',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '5-10% (2.5-5 m) area front tidak standard',
            1 => 'Ada, lebar tidak sesuai standard (1 m)',
            2 => 'Ada, tidak sesuai standard',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => '>10% (5 m) area front tidak standard',
            1 => 'Tidak ada',
            2 => 'Tidak ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    8 =>
    array (
      'number' => 9,
      'title' => 'A. Perapihan Dinding Lereng Final Design',
      'rubricTitle' => 'A. Perapihan Dinding Lereng Final Design',
      'columns' =>
      array (
        0 => 'Lereng Final',
        1 => 'Serah Terima Patok',
        2 => 'Material Lepas',
        3 => 'Lereng Gantung',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Kemiringan sesuai Bowplank',
            1 => 'Sudah serah terima',
            2 => 'Tidak ada',
            3 => 'Tidak ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Kemiringan tidak sesuai design dan patok acuan',
            1 => 'Tidak melakukan serah terima',
            2 => 'Ada',
            3 => 'Ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 5,
      ),
      'format' => 'table',
    ),
    9 =>
    array (
      'number' => 10,
      'title' => 'Pengawasan Harian Area Kerja pada Awal Shift',
      'rubricTitle' => 'Pengawasan Harian Area Kerja pada Awal Shift',
      'columns' =>
      array (
        0 => 'Pengisian Form Inspeksi Area Kerja di Awal Shift',
        1 => 'Risk Map / Peta Bahaya Geoteknik',
        2 => 'Bahaya Geoteknik',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Sudah dilakukan dan tidak ditemukan deviasi',
            1 => 'Tersedia dan sudah disosialisasikan',
            2 => 'Tidak ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => 'Sudah dilakukan dan temuan deviasi sudah di follow up',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Sudah dilakukan dan temuan deviasi belum di follow up',
            1 => 'Tersedia dan belum disosialisasikan',
            2 => '',
          ),
          'black' =>
          array (
            2 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Belum dilakukan',
            1 => 'Belum tersedia',
            2 => 'Terdapat instability',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    10 =>
    array (
      'number' => 11,
      'title' => 'Kondisi Tanggap Darurat',
      'rubricTitle' => 'Kondisi Tanggap Darurat',
      'columns' =>
      array (
        0 => 'Jika Terdapat Area Bahaya',
        1 => 'Emergency Procedure',
        2 => 'Rambu, Jalur Evakuasi, dan Muster Point',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Sudah diisolasi / barikade',
            1 => 'Tersedia sudah disosialisasikan',
            2 => 'Tersedia & terlihat jelas',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => 'Tersedia namun belum disosialisasikan',
            2 => 'Tersedia namun tidak terlihat jelas (kotor / rebah / miring)',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Belum diisolasi / barikade',
            1 => 'Tidak tersedia',
            2 => 'Tidak tersedia',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
  ),
  'MFCO' =>
  array (
    0 =>
    array (
      'number' => 1,
      'title' => 'Tinggi Jenjang Penggalian',
      'rubricTitle' => 'Tinggi Jenjang Penggalian',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Backhoe PC 2000 Class / EX 2600 / R9200',
        1 => 'Backhoe PC 1250 Class / CAT 395 / R9100 / EX 1200',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => '4 m',
            1 => '3 - 4 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '3 - <4 m',
            1 => '2 - <3 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => '<3 m',
            1 => '<2 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    1 => 
    array (
      'number' => 2,
      'title' => 'Metode Penggalian',
      'rubricTitle' => 'Metode Penggalian',
      'notes' => 
      array (
      ),
      'columns' => 
      array (
        0 => 'Metode Penggalian',
        1 => 'Expose Batu Bara',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sisa penggalian batu bara ideal yaitu terdapat beda tinggi > 1 m dari OB',
            1 => 'Telah ter-expose',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => 'Sisa penggalian batubara terdapat beda tinggi 0.5 - 1 m dari OB',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Sisa penggalian batubara terdapat beda tinggi < 0.5 m dari OB',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Sisa penggalian batu bara flat dengan OB (tidak ada beda tinggi)',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Dropcut dengan izin Adaro',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Dropcut tanpa izin Adaro',
            1 => 'Tidak ter-expose',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    2 =>
    array (
      'number' => 3,
      'title' => 'Kebersihan Front Loading Batu Bara',
      'rubricTitle' => 'Kondisi Roof Batu Bara (Kebersihan)',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Kondisi Roof Batu Bara',
        1 => 'Kontaminasi',
        2 => 'Batubara Terisolasi dari Air Luar dan Pengotor',
        3 => 'Batu Bara Berhamburan',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Bersih',
            1 => 'Tidak terdapat',
            2 => 'Sudah terisolasi',
            3 => 'Tidak terdapat',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => 'Berhamburan 1 - <25% dari panjang front',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => 'Berhamburan 25 - 50% dari panjang front',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Kotor',
            1 => 'Terdapat',
            2 => 'Belum terisolasi',
            3 => 'Berhamburan >50% - sepanjang front',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    3 =>
    array (
      'number' => 4,
      'title' => 'Luasan Area Penggalian (Working Geometry)',
      'rubricTitle' => 'Working Geometry',
      'notes' =>
      array (
        0 => 'Standard: Luas area penggalian >= 4x lebar DT terbesar',
      ),
      'columns' =>
      array (
        0 => 'Kriteria',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => '>= 4x lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '3 - <4x lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '2 - <3x lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => '< 2x lebar Dump Truck terbesar',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    4 => 
    array (
      'number' => 5,
      'title' => 'Grade Box',
      'rubricTitle' => 'Grade Box dan Form Validasi',
      'notes' => 
      array (
      ),
      'columns' => 
      array (
        0 => 'Gradebox dan Form Validasi',
        1 => 'Jarak terhadap Area Front Loading',
        2 => 'Validasi PIC',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia dalam kondisi standard',
            1 => '30 - 50 m dan dibarikade',
            2 => 'Tervalidasi oleh kedua PIC (pengawas dan survey) dan tersubmit secara online',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '30 - 50 m tidak dibarikade',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '<30 m atau >50 m dan dibarikade',
            2 => 'Tervalidasi oleh kedua PIC (pengawas dan survey) dan belum tersubmit secara online',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '<30 m atau >50 m dan tidak dibarikade',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tersedia dalam kondisi standard tetapi kurang baik',
            1 => '< 30 m atau > 50 m dan tidak dibarikade',
            2 => 'Tervalidasi oleh survey saja',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak tersedia atau tidak tervalidasi survey',
          ),
          'black' => 
          array (
          ),
          'colspan' => 3,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    5 =>
    array (
      'number' => 6,
      'title' => 'Lantai Kerja Sesuai Rencana Elevasi / On Grade',
      'rubricTitle' => 'Deviasi Lantai Kerja terhadap Acuan Benang Ukur',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Kondisi',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'On Grade : sesuai dengan benang ukur',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Under Cut : (benang ukur berada di bawah scotlight HD)',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Over Cut : (benang ukur berada di atas scotlight HD) dan/atau grade box tidak tersedia atau tidak update',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    6 =>
    array (
      'number' => 7,
      'title' => 'Batas Penggalian (Digging Limit) Patok Acuan Kemiringan Lereng Final Design / Batter Peg / Bowplank dan Patok Crestline (Pita Orange)',
      'rubricTitle' => 'Batas Penggalian (Digging Limit)',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Patok Batas Acuan Penggalian',
        1 => 'Batter Peg/Bow Plank (Berlaku jika final design)',
        2 => 'Patok Crestline',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Ada setiap 10 m dan sesuai standard patok',
            1 => 'Ada setiap 30 m dan berfungsi dengan baik',
            2 => 'Ada setiap 15 m dan berfungsi dengan baik',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => 'Ada setiap 10 m tetapi 1 standard tidak dipenuhi',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => 'Ada setiap 10 m tetapi 2 standard tidak dipenuhi',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Ada dengan jarak > 10 m dan tidak sesuai standard',
            1 => 'Ada dengan jarak > 30 m dan ada yang rusak serta tidak berfungsi',
            2 => 'Ada dengan jarak > 15 m dan ada yang rusak serta tidak berfungsi',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => 3,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 5,
      ),
      'format' => 'table',
    ),
    7 =>
    array (
      'number' => 8,
      'title' => 'Alat Gali dan Alat Angkut menggunakan label Coal Series Pada Unit',
      'rubricTitle' => 'Coal Series Label',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Alat Gali dan Alat Angkut Menggunakan Label Series',
        1 => 'Kesesuaian Label Series yang Digunakan',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Menggunakan',
            1 => 'Sesuai',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak menggunakan / salah',
            1 => 'Tidak sesuai',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 5,
      ),
      'format' => 'table',
    ),
    8 =>
    array (
      'number' => 9,
      'title' => 'Kebersihan Alat Gali dan Alat Angkut',
      'rubricTitle' => 'Kebersihan Alat Gali dan Alat Angkut',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Kondisi Kebersihan Alat Gali dan Alat Angkut',
        1 => 'Keterdapatan Potensi Kontaminasi',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Bersih',
            1 => 'Tidak terdapat potensi',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Kotor',
            1 => 'Terdapat potensi',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 5,
      ),
      'format' => 'table',
    ),
    9 => 
    array (
      'number' => 10,
      'title' => 'Kondisi Lantai Kerja Penggalian',
      'rubricTitle' => 'Kondisi Lantai Kerja Penggalian',
      'notes' => 
      array (
      ),
      'columns' => 
      array (
        0 => 'Luasan Undulasi',
        1 => 'Kedalaman Undulasi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada undulasi',
            1 => '0 cm',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '< 10 cm',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Luasan undulasi <5% area kerja',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '10 - 15 cm',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Luasan undulasi 5-10% area kerja',
            1 => '15 - 20 cm',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Luasan undulasi >10% area kerja',
            1 => '> 20 cm',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    10 =>
    array (
      'number' => 11,
      'title' => 'Drainase & Air Permukaan',
      'rubricTitle' => 'Drainase & Air Permukaan',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Arah Aliran Air',
        1 => 'Potensi Genangan',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Jelas (contour drainage min. 1%)',
            1 => 'Tidak terbentuk potensi genangan',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '≤5% area (2.5 M)',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '5-10% area (2.5-5M)',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak Ada',
            1 => '>10% area (5M)',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    11 =>
    array (
      'number' => 12,
      'title' => 'Tanggul Pengaman / Bundwall',
      'rubricTitle' => 'Tanggul Pengaman / Bundwall',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Bundwall',
        1 => 'Lebar Sodetan',
        2 => 'Rambu Keselamatan',
        3 => 'Material Bundwall',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Tersedia, berukuran ≥ 3/4 tinggi ban truk terbesar di area front dengan beda ketinggian',
            1 => 'Ada, lebar sesuai standard (1m)',
            2 => 'Ada',
            3 => 'Material Insitu',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '<5% (2.5 M) area front tidak standard',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '5-10% (2.5-5 M) area front tidak standard',
            1 => 'Ada, lebar tidak sesuai standard (1m)',
            2 => 'Ada, tidak sesuai standard',
            3 => '',
          ),
          'black' =>
          array (
            3 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => '>10% (5 M) area front tidak standard',
            1 => 'Tidak ada',
            2 => 'Tidak ada',
            3 => 'Material Non Insitu',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    12 =>
    array (
      'number' => 13,
      'title' => 'Pengawasan Harian Area Kerja pada Awal Shift',
      'rubricTitle' => 'Pengawasan Harian Area Kerja pada Awal Shift',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Pengisian Form Inspeksi Area Kerja di Awal Shift',
        1 => 'Risk Map / Peta Bahaya Geoteknik',
        2 => 'Bahaya Geoteknik',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Sudah dilakukan dan tidak ditemukan deviasi',
            1 => 'Tersedia dan sudah disosialisasikan',
            2 => 'Tidak ada',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => 'Sudah dilakukan dan temuan deviasi sudah di Follow-up',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Sudah dilakukan dan temuan deviasi belum di Follow-up',
            1 => 'Tersedia dan belum disosialisasikan',
            2 => '',
          ),
          'black' =>
          array (
            2 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Belum dilakukan',
            1 => 'Belum tersedia',
            2 => 'Terdapat Instability',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    13 =>
    array (
      'number' => 14,
      'title' => 'Kondisi Tanggap Darurat',
      'rubricTitle' => 'Kondisi Tanggap Darurat',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Jika Terdapat Area Bahaya',
        1 => 'Emergency Procedure',
        2 => 'Rambu, Jalur Evakuasi, dan Master Point',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Sudah diisolasi / barikade',
            1 => 'Tersedia sudah disosialisasikan',
            2 => 'Tersedia & terlihat jelas',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => 'Tersedia namun belum disosialisasikan',
            2 => 'Tersedia namun tidak terlihat jelas (kotor / rebah / miring)',
          ),
          'black' =>
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Belum diisolasi / barikade',
            1 => 'Tidak tersedia',
            2 => 'Tidak tersedia',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    14 =>
    array (
      'number' => 15,
      'title' => 'Optimalisasi Floor Batu Bara',
      'rubricTitle' => 'Optimalisasi Floor Batu Bara',
      'notes' =>
      array (
      ),
      'columns' =>
      array (
        0 => 'Optimalisasi Floor',
        1 => 'Tumpukan Batubara Hasil Optimalisasi Floor',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Optimalisasi material floor sudah dilakukan dan tidak terjadi loss coal',
            1 => 'Tidak ada tumpukan batu bara hasil optimalisasi floor baik di dalam maupun di luar range front loading (range 100 m)',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak dilakukan optimalisasi floor batubara dan tinggi jenjang sudah tidak memungkinkan untuk dilakukan optimalisasi (> 4 meter) baik di dalam maupun di luar range front loading (range 100 m)',
            1 => 'Terdapat tumpukan batu bara hasil optimalisasi floor baik di dalam maupun di luar range front loading (range 100 m)',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 5,
      ),
      'format' => 'table',
    ),
  ),
  'DISPOSAL' => 
  array (
    0 => 
    array (
      'number' => 1,
      'title' => 'Dozer Support',
      'rubricTitle' => 'Dozer Support',
      'notes' => 
      array (
        0 => 'Tersedia unit dozer dengan kapasitas yang sesuai (Standard: 1 DZ / 3 fleet loader, dll)',
      ),
      'columns' => 
      array (
        0 => 'D85SS',
        1 => 'D155',
        2 => 'D375',
        3 => 'D9R',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => '1 DZ untuk 3 <i>Fleet loader</i> kelas PC400',
            1 => '1 DZ untuk 3 <i>Fleet loader</i> kelas PC1250',
            2 => '1 DZ untuk 3 <i>Fleet loader</i> kelas PC 2000-3000',
            3 => '1 DZ untuk 3 <i>Fleet loader</i> kelas PC 4000',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 4,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '1 DZ untuk 4 <i>Fleet loader</i> kelas PC400',
            1 => '1 DZ untuk 4 <i>Fleet loader</i> kelas PC1250',
            2 => '1 DZ untuk 4 <i>Fleet loader</i> kelas PC 2000-3000',
            3 => '1 DZ untuk 4 <i>Fleet loader</i> kelas PC 4000',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 4,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '1 unit dozer melayani > <i>fleet</i> OB atau tidak sesuai <i>class loader</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => 4,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak terdapat unit dozer',
          ),
          'black' => 
          array (
          ),
          'colspan' => 4,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    1 => 
    array (
      'number' => 2,
      'title' => 'Tinggi Jenjang Penimbunan',
      'rubricTitle' => 'Tinggi Jenjang',
      'notes' => 
      array (
        0 => 'Standard: Tinggi jenjang penimbunan 6 meter dan 12 meter',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tinggi jenjang timbunan 6 meter dan 12 meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Tinggi jenjang timbunan >4 meter dan <6 meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tinggi jenjang timbunan ≤4 Meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tinggi jenjang timbunan ≤3 Meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    2 => 
    array (
      'number' => 3,
      'title' => 'Kondisi Lantai Kerja Penimbunan',
      'rubricTitle' => 'Lantai Kerja',
      'notes' => 
      array (
        0 => 'Tidak ada undulasi (radius 200m dari titik penimbunan)',
      ),
      'columns' => 
      array (
        0 => 'Luasan Undulasi',
        1 => 'Kedalaman Undulasi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada undulasi',
            1 => '0 cm',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '< 10 cm',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '<5% area kerja',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '10 - 15 cm',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '5-10% area kerja',
            1 => '15 - 20 cm',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '>10% area kerja',
            1 => '>= 20 cm',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    3 =>
    array (
      'number' => 4,
      'title' => 'Batas Penimbunan (Dumping Limit)',
      'rubricTitle' => 'Dumping Limit',
      'notes' =>
      array (
        0 => 'Tersedia tanda atau patok batas yang memadai',
      ),
      'columns' =>
      array (
        0 => 'Patok Batas',
        1 => 'Sesuai Design',
      ),
      'rows' =>
      array (
        0 =>
        array (
          'score' => '5',
          'cells' =>
          array (
            0 => 'Jarak antar patok = 20 m',
            1 => 'Jarak patok dari toe = 25 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        1 =>
        array (
          'score' => '4',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 =>
        array (
          'score' => '3',
          'cells' =>
          array (
            0 => 'Jarak antar patok ≤ 25 m',
            1 => 'Jarak patok dari toe ≤ 30 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        3 =>
        array (
          'score' => '2',
          'cells' =>
          array (
            0 => '',
            1 => '',
          ),
          'black' =>
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 =>
        array (
          'score' => '1',
          'cells' =>
          array (
            0 => 'Jarak antar patok > 20 m',
            1 => 'Jarak patok dari toe > 25 m',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
        5 =>
        array (
          'score' => '0',
          'cells' =>
          array (
            0 => 'Tidak terdapat patok',
            1 => 'Tidak terdapat patok',
          ),
          'black' =>
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' =>
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    4 => 
    array (
      'number' => 5,
      'title' => 'Grade Box',
      'rubricTitle' => 'Grade Box',
      'notes' => 
      array (
        0 => 'Tersedia Grade Box standard (2x1x0.5m) and tervalidasi online',
      ),
      'columns' => 
      array (
        0 => 'Grade box dan Form Validasi',
        1 => 'Jarak terhadap Area Dumping <i>Point</i>',
        2 => 'Validasi PIC',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia dalam kondisi standard',
            1 => '50-100 m dan dibarikade',
            2 => 'Tervalidasi oleh kedua PIC (pengawas dan survey) dan tersubmit secara online',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '50-100 m tidak dibarikade',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '<50 m atau >100 m dan dibarikade',
            2 => 'Tervalidasi oleh kedua PIC (pengawas dan survey) dan belum tersubmit secara online',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tersedia dalam kondisi standard tetapi kurang baik',
            1 => '< 50 m atau > 100 m dan tidak dibarikade',
            2 => 'Tervalidasi oleh survey saja',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak tersedia <i>grade box</i> atau tersedia namun tidak tervalidasi oleh <i>survey</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => 3,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 5,
      ),
      'format' => 'table',
    ),
    5 => 
    array (
      'number' => 6,
      'title' => 'Lantai Kerja Sesuai Rencana Elevasi / On Grade',
      'rubricTitle' => 'On Grade',
      'notes' => 
      array (
        0 => 'Sesuai rencana elevasi target, tidak overdump',
      ),
      'columns' => 
      array (
        0 => 'Deviasi Lantai Kerja terhadap Acuan Benang Ukur',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => '<i>On Grade</i> , sesuai dengan benang ukur',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '<i>Under Fill</i> (benang ukur berada di atas <i>scotlight</i> HD)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '<i>Over Fill</i> (benang ukur berada di bawah <i>scotlight</i> HD) dan/atau <i>grade box</i> tidak tersedia atau tidak <i>update</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    6 => 
    array (
      'number' => 7,
      'title' => 'Komposisi Dumping',
      'rubricTitle' => 'Komposisi',
      'notes' => 
      array (
        0 => 'Distribusi fleet merata di base, middle, dan top disposal',
      ),
      'columns' => 
      array (
        0 => '<i>Base</i>',
        1 => '<i>Middle</i>',
        2 => '<i>Top</i>',
        3 => '<i>Fleet Running</i>',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Terbentuk <i>Progress</i> ≥ 70%',
            1 => 'Terbentuk <i>Progress</i> ≤ 70%',
            2 => 'Terbentuk <i>Progress</i> ≤ 70%',
            3 => '<i>Fleet</i> terbagi merata di <i>base</i>, <i>middle</i>, dan <i>top</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 4,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 4,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 4,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '<i>Progress</i> ≤ 70 %',
            1 => '<i>Progress</i> ≤ 50 %',
            2 => '<i>Progress</i> ≤ 50 %',
            3 => 'Hanya <i>dumping</i> di <i>middle</i>/<i>fleet</i> lebih banyak di <i>middle</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak sesuai desain/Tidak ada <i>progress dumping</i>',
            1 => 'Hanya <i>dumping</i> di <i>top</i>/<i>fleet</i> lebih banyak di <i>top</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => 3,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    7 => 
    array (
      'number' => 8,
      'title' => 'Material Penimbunan',
      'rubricTitle' => 'Material',
      'notes' => 
      array (
        0 => 'Tidak ada free dump liar, pasir di frame, atau waste coal di final frame',
      ),
      'columns' => 
      array (
        0 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Dapat memenuhi 4 item tersebut',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Dapat memenuhi 3 item tersebut',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Terdapat deviasi namun diizinkan oleh pihak Adaro (kecuali <i>point</i> no. 3 dan 4)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak dapat memenuhi 4 item tersebut',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    8 => 
    array (
      'number' => 9,
      'title' => 'Pos Pengawas & Tempat Parkir Sarana',
      'rubricTitle' => 'Fasilitas',
      'notes' => 
      array (
        0 => 'Pos terawat (5R), dalam radius petir, dan ada tanggul/rambu parkir',
      ),
      'columns' => 
      array (
        0 => 'Ketersediaan Sarana dan Prasarana',
        1 => 'Jarak Maksimal Pos dari <i>Dumping Point</i>',
        2 => 'Kondisi Sarana dan Prasarana',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia',
            1 => 'Maks. 300 Meter',
            2 => 'Standard',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
            1 => '>300 Meter',
            2 => 'Tidak standard',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => 3,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    9 => 
    array (
      'number' => 10,
      'title' => 'Luasan Area Penimbunan (Dumping Working Geometry)',
      'rubricTitle' => 'Geometry',
      'notes' => 
      array (
        0 => 'Lebar area sesuai unit (HD777: 25m, HD150: 30m, HD200: 35m)',
      ),
      'columns' => 
      array (
        0 => 'HD 100 Ton',
        1 => 'HD 150 Ton',
        2 => 'HD 200 Ton',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => '≥ 25 Meter atau ≥ 27 Meter',
            1 => '≥ 30 Meter',
            2 => '≥ 35 Meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '< 25 Meter atau < 27 Meter',
            1 => '< 30 Meter',
            2 => '< 35 Meter',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 5,
      ),
      'format' => 'table',
    ),
    10 => 
    array (
      'number' => 11,
      'title' => 'Drainase, Air Permukaan',
      'rubricTitle' => 'Drainase',
      'notes' => 
      array (
        0 => 'Tidak ada genangan, aliran air jelas (radius 100m)',
      ),
      'columns' => 
      array (
        0 => 'Arah Aliran Air',
        1 => 'Potensi Genangan',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Jelas (contour <i>drainage</i> min. 1%)',
            1 => 'Tidak terbentuk potensi genangan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 2,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '<5% area (2.5 M)',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 2,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
            1 => '5-10% area (2.5 - 5 M)',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak Ada',
            1 => '>10% area (5 M)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    11 => 
    array (
      'number' => 12,
      'title' => 'Tanggul Pembatas Timbunan / Windrow',
      'rubricTitle' => 'Windrow',
      'notes' => 
      array (
        0 => 'Tinggi windrow > 1/3 ban unit terbesar',
      ),
      'columns' => 
      array (
        0 => 'Ketersediaan <i>Windrow</i>',
        1 => 'Standar',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia disepanjang <i>crest line</i> timbunan',
            1 => 'Standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '75 - <100% panjang <i>crest line</i> timbunan',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '50 - <75% panjang <i>crest line</i> timbunan',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '25 - <50% panjang <i>crest line</i> timbunan',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '<25% panjang <i>crest line</i> timbunan',
            1 => 'Tidak standar',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => 2,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    12 => 
    array (
      'number' => 13,
      'title' => 'Pengawasan Harian Area Kerja pada Awal Shift',
      'rubricTitle' => 'Pengawasan',
      'notes' => 
      array (
        0 => 'Inspeksi awal shift, Risk Map, dan nihil bahaya geoteknik',
      ),
      'columns' => 
      array (
        0 => 'Pengisian Form Inspeksi Area Kerja di Awal <i>Shift</i>',
        1 => '<i>Risk Map</i> / Peta Bahaya Geoteknik',
        2 => 'Bahaya Geoteknik',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sudah dilakukan dan tidak ditemukan deviasi',
            1 => 'Tersedia dan sudah disosialisasikan',
            2 => 'Tidak ada',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Sudah dilakukan dan temuan deviasi sudah di <i>follow up</i>',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Sudah dilakukan dan temuan deviasi belum di <i>follow up</i>',
            1 => 'Tersedia dan belum disosialisasikan',
            2 => '',
          ),
          'black' => 
          array (
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 3,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Belum dilakukan',
            1 => 'Belum tersedia',
            2 => 'Terdapat <i>instability</i>',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 2,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    13 => 
    array (
      'number' => 14,
      'title' => 'Rambu - Rambu Area Disposal',
      'rubricTitle' => 'Rambu',
      'notes' => 
      array (
        0 => 'Rambu material, limiter, putar, and parkir tersedia & terawat',
      ),
      'columns' => 
      array (
        0 => 'Kelengkapan',
        1 => 'Kondisi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Lengkap',
            1 => 'Terawat',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 2,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 2,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => 2,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tidak lengkap',
            1 => 'Tidak terawat',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak terdapat rambu',
          ),
          'black' => 
          array (
          ),
          'colspan' => 2,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
  ),
  'DEWATERING' => 
  array (
    0 => 
    array (
      'number' => 1,
      'title' => 'Kondisi Pipa Pompa Air Sump / HDPE',
      'rubricTitle' => 'Pipa HDPE',
      'notes' => 
      array (
        0 => 'Monitoring kebocoran jalur pipa dan memastikan tidak ada pipa yang saling bersilang atau berlindih yang dapat menyebabkan kerusakan.',
      ),
      'columns' => 
      array (
        0 => 'Kebocoran Jalur Pipa HDPE',
        1 => 'Kondisi Pipa HDPE Bersilang / Berlindih',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada temuan kebocoran',
            1 => 'Tidak ada temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => 'Ada temuan < 10% dari total jumlah jalur 1 sump',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => 'Ada temuan < 20% dari total jumlah jalur 1 sump',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => 'Ada temuan < 30% dari total jumlah jalur 1 sump',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
            1 => 'Ada temuan < 40% dari total jumlah jalur 1 sump',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Ada temuan kebocoran',
            1 => 'Ada temuan > 40% dari total jumlah jalur 1 sump',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    1 => 
    array (
      'number' => 2,
      'title' => 'Kolam Penampung Sedimentasi (Sediment Trap) di Area Sump',
      'rubricTitle' => 'Sediment Trap',
      'notes' => 
      array (
        0 => 'Memastikan sediment trap berfungsi optimal dengan beda tinggi overflow yang cukup, aliran air terkontrol, dan kelengkapan papan informasi.',
      ),
      'columns' => 
      array (
        0 => 'Beda Tinggi Overflow',
        1 => 'Aliran Air',
        2 => 'Kompartemen & Fungsi',
        3 => 'Papan Informasi & Jadwal',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => '> 2 m',
            1 => 'Semua aliran air ke sump melewati sediment trap',
            2 => 'Tersedia jumlah min. 3 buah dan berfungsi',
            3 => 'Papan info & bar level: ada, jelas, update. Jadwal: ada & update',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '1.5 - 2 m',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '1.25 - <1.5 m',
            1 => '',
            2 => '',
            3 => 'Papan info & bar level: ada, jelas, tidak update. Jadwal: ada, tidak update',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '1 - <1.25 m',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '0.5 - <1 m',
            1 => 'Terdapat 1 aliran tidak melewati sediment trap',
            2 => 'Tersedia jumlah min. 3 buah tetapi tidak berfungsi',
            3 => 'Papan info & bar level: ada, tidak update. Jadwal: ada, tidak update',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '< 0.5 m',
            1 => 'Terdapat > 1 aliran tidak melewati sediment trap',
            2 => 'Tidak tersedia atau jumlah kurang dari 3',
            3 => 'Tidak ada papan informasi, bar level, dan jadwal maintenance',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    2 => 
    array (
      'number' => 3,
      'title' => 'Kolam Control Box di Outlet Pompa Air Keluaran',
      'rubricTitle' => 'Control Box',
      'notes' => 
      array (
        0 => 'Monitoring kondisi control box di outlet untuk memastikan pemisahan lumpur tahap akhir berfungsi dengan baik.',
      ),
      'columns' => 
      array (
        0 => 'Beda Tinggi Overflow',
        1 => 'Aliran Air',
        2 => 'Kompartemen & Fungsi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => '> 2 m',
            1 => 'Ada dan lengkap',
            2 => 'Tersedia dan berfungsi jumlah min. 3 buah',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '1.5 - 2 m',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '1.25 - <1.5 m',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '1 - <1.25 m',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '0.5 - <1 m',
            1 => 'Ada dan tidak lengkap',
            2 => 'Tersedia jumlah min. 3 buah tetapi tidak berfungsi',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '< 0.5 m',
            1 => 'Tidak ada',
            2 => 'Tidak tersedia atau jumlah kurang dari 3 kompartemen',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    3 => 
    array (
      'number' => 4,
      'title' => 'Akses Menuju ke Pos Area Sump',
      'rubricTitle' => 'Akses Sump',
      'notes' => 
      array (
        0 => 'Akses jalan menuju pos harus memenuhi standar lebar, rambu, grade, dan kondisi permukaan yang aman.',
      ),
      'columns' => 
      array (
        0 => 'Lebar',
        1 => 'Rambu',
        2 => 'Grade',
        3 => 'Kondisi Surface',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Standar (>= 3.5m)',
            1 => 'Tersedia, tegak, bersih',
            2 => '< 8%',
            3 => 'Tidak ada undulasi / rolling / genangan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Tidak standar (3 - < 3.5m)',
            1 => 'Tersedia, tapi miring atau kotor',
            2 => '> 8%, tetapi dilengkapi rambu awas grade, dilarang pengereman, dan median',
            3 => 'Luasan undulasi / rolling / genangan < 5% area akses',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
            3 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
            3 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tidak standar (2.5 - < 3m)',
            1 => 'Tidak lengkap',
            2 => '> 8%, pengendalian tidak lengkap',
            3 => 'Luasan undulasi / rolling / genangan 5-10% area akses',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak standar (< 2.5m)',
            1 => 'Tidak tersedia',
            2 => '> 8% tanpa ada pengendalian',
            3 => 'Luasan undulasi / rolling / genangan > 10% area akses',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    4 => 
    array (
      'number' => 5,
      'title' => 'Akses Sarana dan Prasarana ke Pompa',
      'rubricTitle' => 'Akses Pompa',
      'notes' => 
      array (
        0 => 'Memastikan akses menuju pompa aman, baik menggunakan walkway standar maupun perahu layak pakai, serta dilengkapi sarana keselamatan.',
      ),
      'columns' => 
      array (
        0 => 'Akses Sarana dan Prasarana ke Pompa',
        1 => 'Informasi Kapasitas, P3K, KIP, Ringbuoy/Tali Lintasan',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Walkway sesuai standar',
            1 => 'Tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Walkway tetapi tidak sesuai standar',
            1 => '',
          ),
          'black' => 
          array (
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Menggunakan perahu dengan kondisi layak',
            1 => 'Tersedia, tapi tidak standar / tidak tervalidasi / tidak terbaca',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak tersedia akses atau menggunakan perahu tapi bocor / tidak layak',
            1 => 'Tidak tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    5 => 
    array (
      'number' => 6,
      'title' => 'Kondisi Elevasi Air Sump & Rambu Elevasi Air / Bar Meter di Sump',
      'rubricTitle' => 'Elevasi Air',
      'notes' => 
      array (
        0 => 'Monitoring level air terhadap batas kritis hujan dan memastikan alat ukur (bar meter) tersedia serta terlihat jelas.',
      ),
      'columns' => 
      array (
        0 => 'Level Elevasi Air',
        1 => 'Bar Meter / Rambu Elevasi Air',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Di bawah level kritis terhadap max hujan 4 harian (240mm)',
            1 => 'Tersedia pada posisi standar (terlihat jelas & berfungsi)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Di bawah level kritis terhadap max hujan 1 harian (110mm)',
            1 => 'Tersedia tetapi kotor sehingga tidak terlihat jelas',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Di atas level kritis terhadap max hujan 1 harian (110mm)',
            1 => 'Tidak tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    6 => 
    array (
      'number' => 7,
      'title' => 'Kondisi Pompa Sump & Lampu Rotary pada Unit Pompa',
      'rubricTitle' => 'Kondisi Pompa',
      'notes' => 
      array (
        0 => 'Memastikan unit pompa bebas kebocoran, APAR tersedia lengkap, dan lampu rotary berfungsi dengan baik.',
      ),
      'columns' => 
      array (
        0 => 'Kebocoran (Oli, Solar, Air)',
        1 => 'APAR',
        2 => 'Lampu Rotary / Lampu Penerangan',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada',
            1 => 'Tersedia sesuai porsi',
            2 => 'Semua rotary menyala dengan jelas',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '1',
            1 => 'Tersedia, tetapi jumlah kurang 1',
            2 => 'Terdapat 1 temuan (rotary tidak terlihat jelas atau mati)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => '> 1',
            1 => 'Tersedia, tetapi jumlah kurang > 1',
            2 => 'Terdapat > 1 temuan (rotary tidak terlihat jelas atau mati)',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    7 => 
    array (
      'number' => 8,
      'title' => '(Khusus Pompa Elektrik) Terdapat: Kabel Daya dari Trafo ke Pompa, Cable Tray/Ladder',
      'rubricTitle' => 'Pompa Elektrik',
      'notes' => 
      array (
        0 => 'Khusus untuk instalasi pompa elektrik, memastikan keamanan kabel daya, kondisi tray, dan isolasi kabel dari potensi bahaya.',
      ),
      'columns' => 
      array (
        0 => 'Kondisi Kabel & Tray',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Semua komponen tersedia, tidak ditemukan kerusakan, korosi signifikan, genangan, atau potensi bahaya',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Semua komponen tersedia, tetapi terdapat maks. 1 temuan minor (clamp longgar, korosi ringan, kabel kotor tetapi tidak rusak)',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Salah satu item penilaian terjadi kerusakan (kabel terendam, isolasi kabel rusak, tray patah/runtuh, kabel tidak melalui tray)',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    8 => 
    array (
      'number' => 9,
      'title' => 'Rambu-rambu di Area Sump',
      'rubricTitle' => 'Rambu Sump',
      'notes' => 
      array (
        0 => 'Memastikan semua rambu peringatan dan informasi di area sump tersedia lengkap, dalam kondisi tegak, dan bersih agar mudah terbaca.',
      ),
      'columns' => 
      array (
        0 => 'Kondisi Rambu',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia lengkap, tidak miring, tidak kotor',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => 'Tersedia lengkap, tetapi miring atau kotor',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Tidak lengkap / miring / kotor, 1 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Tidak lengkap / miring / kotor, 2 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tidak lengkap / miring / kotor, 3 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak lengkap / miring / kotor, > 3 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    9 => 
    array (
      'number' => 10,
      'title' => 'Spill Kit di Area Sump',
      'rubricTitle' => 'Spillkit',
      'notes' => 
      array (
        0 => 'Memastikan ketersediaan dan kesiapan spill kit untuk menangani ceceran oli atau bahan kimia lainnya di area sump dengan cepat dan efektif.',
      ),
      'columns' => 
      array (
        0 => 'Kondisi Spill Kit',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia lengkap, tidak kotor, berfungsi',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => 'Tersedia lengkap, kotor, tidak berfungsi',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, 1 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, 2 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, 3 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, > 3 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    10 => 
    array (
      'number' => 11,
      'title' => 'Papan Informasi di Area Sump',
      'rubricTitle' => 'Papan Info',
      'notes' => 
      array (
        0 => 'Memastikan papan informasi di area sump tersedia, data yang ditampilkan akurat (ter-update), lengkap, dan mudah dibaca oleh seluruh personil.',
      ),
      'columns' => 
      array (
        0 => 'Kondisi Papan Informasi',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Terdapat papan informasi, ter-update, lengkap',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => 'Terdapat papan informasi, ter-update, lengkap, namun angka / huruf tidak terlalu jelas terlihat',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Informasi di papan lengkap tetapi tidak update',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Informasi di papan tidak lengkap dan tidak update (kurang 1 informasi / 1 item tidak update)',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Informasi di papan tidak lengkap dan tidak update (kurang 2 informasi / 2 item tidak update)',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Informasi di papan tidak lengkap dan tidak update (kurang > 2 informasi / > 2 item tidak update)',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    11 => 
    array (
      'number' => 12,
      'title' => 'Area Pos Crew /Operator Dewatering  di Area Sump',
      'rubricTitle' => 'Pos Crew / OPR',
      'notes' => 
      array (
        0 => 'Memastikan Area Pos Crew / Operator Dewatering di Area Sump tersedia, bersih, dan layak digunakan.',
      ),
      'columns' => 
      array (
        0 => 'Kondisi Pos Crew',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tersedia, tidak kotor, berfungsi',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => 'Tersedia, kotor, tidak berfungsi',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, 1 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, 2 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, 3 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Tidak lengkap / kotor / tidak berfungsi, > 3 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => 1,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
    12 => 
    array (
      'number' => 13,
      'title' => 'Kondisi Tanggap Darurat',
      'rubricTitle' => 'Emergency Response',
      'notes' => 
      array (
        0 => 'Memastikan area bahaya telah diisolasi, prosedur darurat disosialisasikan, dan rambu/peralatan darurat terlihat jelas.',
      ),
      'columns' => 
      array (
        0 => 'Jika Terdapat Area Bahaya',
        1 => 'Emergency Procedure',
        2 => 'Rambu, Jalur Evakuasi, dan Muster Point',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Sudah diisolasi / barikade',
            1 => 'Tersedia sudah disosialisasikan',
            2 => 'Tersedia & terlihat jelas',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
            1 => 'Tersedia namun belum disosialisasikan',
            2 => 'Tersedia namun tidak terlihat jelas (kotor / rebah / miring)',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Belum diisolasi / barikade',
            1 => 'Tidak tersedia',
            2 => 'Tidak tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 5,
      ),
      'format' => 'table',
    ),
    13 => 
    array (
      'number' => 14,
      'title' => 'Kebersihan di Area Sump / Housekeeping',
      'rubricTitle' => 'Housekeeping',
      'notes' => 
      array (
        0 => 'Memastikan seluruh area operasional sump dalam kondisi bersih (5R), bebas dari sampah organik maupun anorganik.',
      ),
      'columns' => 
      array (
        0 => 'Sampah Berceceran',
        1 => 'Kesesuaian Penempatan Barang & Peralatan',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada temuan',
            1 => 'Sudah sesuai',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => 'Ada 1 temuan',
            1 => 'Terdapat ketidaksesuaian - 1 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => '',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => 'Ada 2 temuan',
            1 => 'Terdapat ketidaksesuaian - 2 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Ada > 2 temuan',
            1 => 'Terdapat ketidaksesuaian > 2 temuan',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 3,
        3 => 5,
      ),
      'format' => 'table',
    ),
    14 => 
    array (
      'number' => 15,
      'title' => 'Limbah / Sampah, Kebocoran / Tumpahan Hidrokarbon atau B3 & Peralatan Spill Kit',
      'rubricTitle' => 'B3 & Spill Kit',
      'notes' => 
      array (
        0 => 'Memastikan tidak ada tumpahan B3 dan kesiapan peralatan spill kit di area kerja.',
      ),
      'columns' => 
      array (
        0 => 'Limbah / Sampah, Kebocoran / Tumpahan Hidrokarbon atau B3',
        1 => 'Peralatan Penanganan Hidrokarbon (Spillkit / Absorber / Oil Boom / Water Dispersant)',
        2 => 'Checklist Kelengkapan',
      ),
      'rows' => 
      array (
        0 => 
        array (
          'score' => '5',
          'cells' => 
          array (
            0 => 'Tidak ada',
            1 => 'Tersedia dengan lengkap dan layak',
            2 => 'Tersedia dan update dan terbaca',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
        1 => 
        array (
          'score' => '4',
          'cells' => 
          array (
            0 => '',
            1 => 'Tersedia tetapi tidak lengkap / tidak layak - 1 temuan',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        2 => 
        array (
          'score' => '3',
          'cells' => 
          array (
            0 => '',
            1 => '',
            2 => 'Tersedia dan update tetapi tidak layak / tidak terbaca',
          ),
          'black' => 
          array (
            0 => true,
            1 => true,
          ),
          'colspan' => NULL,
        ),
        3 => 
        array (
          'score' => '2',
          'cells' => 
          array (
            0 => '',
            1 => 'Tersedia tetapi tidak lengkap / tidak layak - 2 temuan',
            2 => '',
          ),
          'black' => 
          array (
            0 => true,
            2 => true,
          ),
          'colspan' => NULL,
        ),
        4 => 
        array (
          'score' => '1',
          'cells' => 
          array (
            0 => '',
            1 => 'Tersedia tetapi tidak lengkap / tidak layak > 2 temuan',
            2 => 'Tersedia tetapi tidak update',
          ),
          'black' => 
          array (
            0 => true,
          ),
          'colspan' => NULL,
        ),
        5 => 
        array (
          'score' => '0',
          'cells' => 
          array (
            0 => 'Ada',
            1 => 'Tidak tersedia',
            2 => 'Tidak tersedia',
          ),
          'black' => 
          array (
          ),
          'colspan' => NULL,
        ),
      ),
      'options' => 
      array (
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ),
      'format' => 'table',
    ),
  ),
);
