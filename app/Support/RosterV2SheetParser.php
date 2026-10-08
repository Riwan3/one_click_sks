<?php

namespace App\Support;

use InvalidArgumentException;

//
// Ambil & baca Google Sheets (tab roster dan tab MONCU) untuk impor Roster V2 / MONCU. TANPA akses database:
// fungsi di sini murni (fetch CSV + parse). Penulisan ke database ada di RosterV2SheetImportService.
//
// Keamanan fetch: hanya URL https://docs.google.com/spreadsheets/d/<id> yang diterima; URL export DIBANGUN SENDIRI dari id & gid
// (URL dari user tidak pernah di-fetch langsung), hanya protokol https, redirect dibatasi, timeout & ukuran dibatasi.
// Sheet harus dibagikan "Anyone with the link - Viewer".
//
// Layout yang dikenali (dicari lewat teks header, bukan posisi kolom tetap):
//   Tab roster : kolom NO | NRP | NAMA | UNIT | A/B(kode off) ... lalu blok bulan (JANUARY/JANUARI ... DESEMBER), baris nomor hari, baris nama hari.
//                Baris nama section = teks di kolom NO tanpa NRP. Nilai sel: D N OFF CT ID H TR MCU dst.
//   Tab MONCU  : header NRP & HIRING DATE, lalu blok berulang AWAL | AKHIR | TAHUNAN | IZIN RESMI | INDUKSI | PLAN.
//                Hanya AWAL, TAHUNAN, IZIN RESMI yang dipakai (AKHIR/INDUKSI/PLAN dihitung aplikasi).

class RosterV2SheetParser {
    const MAX_BYTES = 15728640;   // 15 MB
    const MONTHS = [
        'JANUARY' => 1, 'JANUARI' => 1, 'FEBRUARY' => 2, 'FEBRUARI' => 2, 'MARCH' => 3, 'MARET' => 3, 'APRIL' => 4,
        'MAY' => 5, 'MEI' => 5, 'JUNE' => 6, 'JUNI' => 6, 'JULY' => 7, 'JULI' => 7, 'AUGUST' => 8, 'AGUSTUS' => 8,
        'SEPTEMBER' => 9, 'OCTOBER' => 10, 'OKTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12, 'DESEMBER' => 12,
        // singkatan umum (mis. header "Sep-26", "Agt 2026", "Des-25")
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 'AGU' => 8, 'AGT' => 8, 'AGS' => 8,
        'SEP' => 9, 'SEPT' => 9, 'OCT' => 10, 'OKT' => 10, 'NOV' => 11, 'NOP' => 11, 'DEC' => 12, 'DES' => 12,
    ];
    const DOW = ['MIN' => 0, 'MGG' => 0, 'SEN' => 1, 'SEL' => 2, 'RAB' => 3, 'KAM' => 4, 'JUM' => 5, 'SAB' => 6];

    // ------------------------------------------------------------------ Fetch

    /** ['id' => ..., 'gid' => ...|null] dari URL Google Sheets; melempar InvalidArgumentException bila bukan. */
    public static function parseUrl($url) {
        $url = trim((string)$url);
        if (!preg_match('~^https://docs\.google\.com/spreadsheets/d/([A-Za-z0-9_-]{20,})~i', $url, $m)) {
            throw new InvalidArgumentException('URL harus berupa link Google Sheets (https://docs.google.com/spreadsheets/d/...).');
        }
        $gid = preg_match('~[#&?]gid=(\d{1,12})~', $url, $g) ? $g[1] : null;
        return ['id' => $m[1], 'gid' => $gid];
    }

    /** Ambil satu tab sebagai baris CSV (array of array). */
    public static function fetchRows($url) {
        $u = self::parseUrl($url);
        $export = 'https://docs.google.com/spreadsheets/d/' . $u['id'] . '/export?format=csv' . ($u['gid'] !== null ? '&gid=' . $u['gid'] : '');
        if (!function_exists('curl_init')) {
            throw new InvalidArgumentException("Ekstensi cURL tidak tersedia di server.");
        }
        $ch = curl_init($export);
        $size = 0;
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 30, CURLOPT_USERAGENT => 'OneClick-RosterV2-Import',
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => function ($r, $dlTotal, $dlNow) { return $dlNow > RosterV2SheetParser::MAX_BYTES ? 1 : 0; },
        ]);
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            throw new InvalidArgumentException('Tidak dapat mengambil Google Sheet' . ($err !== '' ? ' (' . $err . ')' : '') . '. Periksa koneksi server.');
        }
        if ($code === 404) throw new InvalidArgumentException('Sheet tidak ditemukan. Periksa link-nya.');
        if ($code !== 200 || stripos($type, 'text/csv') === false) {
            throw new InvalidArgumentException('Sheet tidak dapat dibaca. Pastikan dibagikan sebagai "Anyone with the link - Viewer" dan link menyertakan tab (gid) yang benar.');
        }
        if (strlen($body) > self::MAX_BYTES) throw new InvalidArgumentException('Ukuran sheet terlalu besar.');
        $h = fopen('php://temp', 'r+');
        fwrite($h, $body); rewind($h);
        $rows = [];
        while (($r = fgetcsv($h, 0, ',', '"', '')) !== false) $rows[] = $r;
        fclose($h);
        if (count($rows) < 3) throw new InvalidArgumentException('Sheet kosong atau bukan tab yang dimaksud.');
        return $rows;
    }

    // ------------------------------------------------------------------ Tanggal

    /**
     * Tanggal dari berbagai bentuk -> YYYY-MM-DD; nol/tidak valid -> null.
     * Dikenal: 3-Nov-25, 11 Juli 2023, "Senin, 26 Januari 2026" (awalan nama hari dibuang), 2026-09-07, 7/9/2026, 13/09/22.
     * Bentuk a/b/y: bila a > 12 pasti hari/bulan; bila b > 12 pasti bulan/hari; bila keduanya <= 12 dipakai $order ('dm' hari dulu, 'md' bulan dulu).
     */
    public static function parseDate($s, $order = 'dm') {
        $s = trim((string)$s);
        if ($s === '') return null;
        $s = preg_replace("/^(senin|selasa|rabu|kamis|jumat|jum'at|sabtu|minggu|ahad)\\s*,?\\s*/i", '', $s);
        $mon = ['jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'mei' => 5, 'may' => 5, 'jun' => 6, 'jul' => 7, 'agu' => 8, 'ags' => 8, 'aug' => 8,
                'sep' => 9, 'okt' => 10, 'oct' => 10, 'nov' => 11, 'des' => 12, 'dec' => 12];
        $full = ['januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
                 'september' => 9, 'sept' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12];
        $d = $mo = $y = null;
        if (preg_match('/^(\d{1,2})-([A-Za-z]{3})-(\d{2,4})$/', $s, $m)) { $d = (int)$m[1]; $mo = $mon[strtolower($m[2])] ?? null; $y = (int)$m[3]; if ($y < 100) { if ($y === 0) return null; $y += 2000; } }
        elseif (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', $s, $m)) { $d = (int)$m[1]; $mo = $full[strtolower($m[2])] ?? ($mon[strtolower(substr($m[2], 0, 3))] ?? null); $y = (int)$m[3]; }
        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) { $y = (int)$m[1]; $mo = (int)$m[2]; $d = (int)$m[3]; }
        elseif (preg_match('~^(\d{1,2})/(\d{1,2})/(\d{2}|\d{4})$~', $s, $m)) {
            $p = (int)$m[1]; $q = (int)$m[2]; $y = (int)$m[3];
            if ($y < 100) { if ($y === 0) return null; $y += 2000; }
            if ($p > 12) { $d = $p; $mo = $q; } elseif ($q > 12) { $mo = $p; $d = $q; } elseif ($order === 'md') { $mo = $p; $d = $q; } else { $d = $p; $mo = $q; }
        }
        if (!$d || !$mo || !$y || $y < 2000 || $y > 2100 || !checkdate($mo, $d, $y)) return null;
        return sprintf('%04d-%02d-%02d', $y, $mo, $d);
    }

    /** Urutan bentuk a/b/y pada satu kolom: 'md' bila ada nilai dengan bagian ke-2 > 12 (dan tidak ada bagian ke-1 > 12 lebih banyak), selain itu 'dm'. */
    private static function detectSlashOrder(array $values) {
        $dm = 0; $md = 0;
        foreach ($values as $v) {
            if (!preg_match('~^(\d{1,2})/(\d{1,2})/(\d{2}|\d{4})$~', trim((string)$v), $m)) continue;
            if ((int)$m[1] > 12) $dm++; elseif ((int)$m[2] > 12) $md++;
        }
        return $md > $dm ? 'md' : 'dm';
    }

    /** 'JANUARY' / 'JANUARY 2026' / 'Mei' -> [bulan, tahun|null]; bukan nama bulan -> null. */
    private static function monthOf($v) {
        $u = strtoupper(trim((string)$v));
        // "Januari 2026", "Sep-26", "Agt/2026", "MEI" -> [bulan, tahun|null]; tahun 2 digit = 20xx
        if (!preg_match('/^([A-Z]+)[\s\-\/\'.]*(\d{4}|\d{2})?$/', $u, $m)) return null;
        if (!isset(self::MONTHS[$m[1]])) return null;
        $y = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : null;
        if ($y !== null && $y < 100) $y += 2000;
        return [self::MONTHS[$m[1]], $y];
    }

    private static function cell(array $row, $c) { return isset($row[$c]) ? trim((string)$row[$c]) : ''; }
    private static function isNrp($v) { return (bool)preg_match('/^\d{4,10}$/', $v); }

    // ------------------------------------------------------------------ Tab roster

    /**
     * @return array ['year','sections'=>[nama...],'members'=>[nrp=>[sec,name,unit,kode,kode_raw,cells[date=>KODE],blank,error,unknown[kode=>n]]],
     *                'warnings'=>[...]]
     * @throws InvalidArgumentException bila layout/tahun tidak cocok.
     */
    public static function parseRoster(array $rows, $year) {
        $year = (int)$year;
        // baris header: kolom NRP + nama bulan
        $h = null;
        foreach ($rows as $i => $r) {
            $isNrp = false; $months = 0;
            foreach ($r as $c => $v) { $u = strtoupper(trim((string)$v)); if ($u === 'NRP') $isNrp = true; if (self::monthOf($v) !== null) $months++; }
            if ($isNrp && $months >= 6) { $h = $i; break; }
        }
        if ($h === null) throw new InvalidArgumentException('Header roster tidak ditemukan (butuh kolom NRP dan nama bulan JANUARY..DESEMBER). Apakah ini tab roster?');
        $nrpCol = 2;
        foreach ($rows[$h] as $c => $v) if (strtoupper(trim((string)$v)) === 'NRP') { $nrpCol = $c; break; }
        $col = ['no' => max(0, $nrpCol - 1), 'nrp' => $nrpCol, 'name' => $nrpCol + 1, 'unit' => $nrpCol + 2, 'kode' => $nrpCol + 3];
        $kodeRank = ['KODE OFF' => 1, 'A/B' => 2, 'BULAN INI' => 3]; $bestRank = 99;
        foreach ($rows[$h] as $c => $v) {
            $u = strtoupper(trim((string)$v));
            if ($u === 'UNIT') $col['unit'] = $c;
            if ($u === 'NAMA') $col['name'] = $c;
            if (isset($kodeRank[$u]) && $kodeRank[$u] < $bestRank) { $bestRank = $kodeRank[$u]; $col['kode'] = $c; }   // 'BULAN INI' dipakai bila tidak ada A/B
        }

        $start = [];
        $yearCount = [];   // tahun pada header -> jumlah kolom bulan; bulan tanpa tahun dianggap tahun roster tujuan
        foreach ($rows[$h] as $c => $v) {
            $mo = self::monthOf($v);
            if ($mo === null) continue;
            $yr = $mo[1] !== null ? $mo[1] : $year;
            $yearCount[$yr] = ($yearCount[$yr] ?? 0) + 1;
            if ($yr !== $year) continue;   // kolom bulan tahun lain (mis. "Des-25" di sheet 2026) bukan bagian roster ini
            if (isset($start[$mo[0]])) continue;
            $start[$mo[0]] = $c;
        }
        if ($yearCount) {
            $max = max($yearCount);   // seri: tahun tujuan menang
            if (($yearCount[$year] ?? 0) < $max) {
                $years = array_keys($yearCount); sort($years);
                throw new InvalidArgumentException('Header sheet bertuliskan tahun ' . implode('/', $years) . ', sedangkan roster tujuan tahun ' . $year . '. Pilih roster tahun yang sama.');
            }
        }
        $firstMonthCol = min($start);
        // Header boleh baru sampai bulan tertentu (mis. sheet baru dibuat sampai Oktober): bulan sesudahnya dikembalikan di 'missing_months'
        // dan diisi otomatis oleh impor dari Kode Off. Syaratnya bulan yang ada berurutan dari Januari dan minimal 6 bulan.
        $missingMonths = [];
        if (count($start) < 12) {
            $ks = array_keys($start); sort($ks); $k = count($ks);
            if ($k < 6 || $ks !== range(1, $k)) throw new InvalidArgumentException('Header bulan tidak lengkap (ditemukan ' . count($start) . ' dari 12 bulan).');
            $missingMonths = range($k + 1, 12);
        }

        $dayRow = $rows[$h + 1] ?? []; $dowRow = $rows[$h + 2] ?? [];
        $colDate = []; $dowBad = 0; $dowChecked = 0; $dowBadDates = [];
        foreach ($start as $mo => $c0) {
            $n = cal_days_in_month(CAL_GREGORIAN, $mo, $year);
            for ($d = 1; $d <= $n; $d++) {
                $c = $c0 + $d - 1;
                if ((int)self::cell($dayRow, $c) !== $d) {
                    throw new InvalidArgumentException(sprintf('Kolom tanggal bulan %02d tidak cocok pada kolom %d (harap hari ke-%d, sheet berisi "%s"). Periksa apakah sheet ini untuk tahun %d.', $mo, $c + 1, $d, self::cell($dayRow, $c), $year));
                }
                $colDate[$c] = sprintf('%04d-%02d-%02d', $year, $mo, $d);
                $lab = strtoupper(self::cell($dowRow, $c));
                if ($lab !== '' && isset(self::DOW[$lab])) {
                    $dowChecked++;
                    if (self::DOW[$lab] !== (int)date('w', strtotime($colDate[$c]))) { $dowBad++; $dowBadDates[] = $colDate[$c]; }
                }
            }
        }
        if ($dowChecked > 30 && $dowBad > $dowChecked * 0.10) {
            throw new InvalidArgumentException("Nama hari pada sheet tidak cocok dengan tahun $year ($dowBad dari $dowChecked hari berbeda). Sepertinya sheet ini untuk tahun lain.");
        }

        $sections = []; $members = []; $warn = []; $sec = null;
        if ($dowBad > 0) {
            $warn[] = "$dowBad nama hari pada sheet tidak cocok dengan kalender $year (" . implode(', ', array_slice($dowBadDates, 0, 4)) . ($dowBad > 4 ? ', ...' : '') . '). Tanggal tetap mengikuti angka tanggal di sheet; periksa label hari-nya.';
        }
        for ($i = 0; $i < count($rows); $i++) {
            if ($i >= $h && $i <= $h + 2) continue;   // baris header, nomor hari, nama hari
            $r = $rows[$i];
            $no = self::cell($r, $col['no']); $nrp = self::cell($r, $col['nrp']);
            if ($no !== '' && $nrp === '' && !ctype_digit($no) && strtoupper($no) !== 'NO' && strtoupper($no) !== 'BULAN') {
                $leftBusy = false;
                for ($k = $col['nrp']; $k < $firstMonthCol; $k++) if (self::cell($r, $k) !== '') { $leftBusy = true; break; }
                if (!$leftBusy) $sec = mb_strtoupper($no);      // baris ringkasan (SIANG/MALAM/TOTAL ...) punya teks di kolom kiri -> bukan section
                continue;
            }
            if (!self::isNrp($nrp) || !ctype_digit($no)) continue;
            if ($sec === null) $sec = 'TANPA SECTION';
            if (!in_array($sec, $sections, true)) $sections[] = $sec;
            $kodeRaw = strtoupper(self::cell($r, $col['kode']));
            $kode = RosterV2Rotation::normalize($kodeRaw);
            if ($kode === null) { $warn[] = "NRP $nrp: Kode Off \"$kodeRaw\" tidak valid, diabaikan."; $kode = ''; }
            $cells = []; $blank = 0; $err = 0; $unknown = [];
            foreach ($colDate as $c => $date) {
                $v = self::cell($r, $c);
                if ($v === '') { $blank++; continue; }
                if ($v[0] === '#') { $err++; continue; }
                $cells[$date] = strtoupper($v);
            }
            if (isset($members[$nrp])) $warn[] = "NRP $nrp muncul lebih dari sekali; baris terakhir dipakai.";
            $members[$nrp] = ['sec' => $sec, 'name' => self::cell($r, $col['name']), 'unit' => self::cell($r, $col['unit']), 'kode' => $kode,
                              'cells' => $cells, 'blank' => $blank, 'error' => $err];
        }
        if (!$members) throw new InvalidArgumentException('Tidak ada baris karyawan (NRP) yang ditemukan pada tab roster.');
        return ['year' => $year, 'sections' => $sections, 'members' => $members, 'warnings' => $warn, 'missing_months' => $missingMonths];
    }

    // ------------------------------------------------------------------ Tab MONCU

    /**
     * @return array ['employees' => [nrp => [name, hiring, hiring_raw, periods[[year,no,awal,tahunan,izin,shifted]], unmapped[[awal,tahunan,izin]]]], 'warnings']
     */
    public static function parseMoncu(array $rows) {
        $h = null; $nrpCol = null; $hireCol = null; $nameCol = null;
        foreach ($rows as $i => $r) {
            $n = $hc = $nm = null;
            foreach ($r as $c => $v) { $u = strtoupper(trim((string)$v)); if ($u === 'NRP') $n = $c; if ($u === 'HIRING DATE') $hc = $c; if ($u === 'NAMA') $nm = $c; }
            if ($n !== null && $hc !== null) { $h = $i; $nrpCol = $n; $hireCol = $hc; $nameCol = $nm ?? ($n + 1); break; }
        }
        if ($h === null) throw new InvalidArgumentException('Header MONCU tidak ditemukan (butuh kolom NRP dan HIRING DATE). Apakah ini tab MONCU?');
        // baris sub-header blok: cari baris dengan >=2 sel "AWAL"
        $sub = null;
        for ($i = $h; $i <= $h + 3 && $i < count($rows); $i++) {
            $cnt = 0; foreach ($rows[$i] as $v) if (strtoupper(trim((string)$v)) === 'AWAL') $cnt++;
            if ($cnt >= 2) { $sub = $i; break; }
        }
        if ($sub === null) throw new InvalidArgumentException('Blok periode (AWAL / AKHIR / TAHUNAN / IZIN RESMI) tidak ditemukan pada header MONCU.');
        $blocks = [];
        foreach ($rows[$sub] as $c => $v) {
            if (strtoupper(trim((string)$v)) !== 'AWAL') continue;
            $t = $iz = null;
            for ($k = $c + 1; $k <= $c + 6; $k++) {
                $u = strtoupper(self::cell($rows[$sub], $k));
                if ($u === 'AWAL') break;
                if ($u === 'TAHUNAN' && $t === null) $t = $k;
                if (($u === 'IZIN RESMI' || $u === 'IZIN') && $iz === null) $iz = $k;
            }
            if ($t === null) continue;
            $fixed = null;
            for ($k = $c; $k <= $c + 5; $k++) {   // label blok ada di baris header, di atas kolom AWAL
                if (preg_match('/PERIODE\s*(\d)\s*(\d{4})/i', self::cell($rows[$h], $k), $lm) && (int)$lm[1] >= 1 && (int)$lm[1] <= 5) { $fixed = [(int)$lm[2], (int)$lm[1]]; break; }
            }
            $blocks[] = ['awal' => $c, 'tahunan' => $t, 'izin' => $iz, 'fixed' => $fixed];
        }
        if (!$blocks) throw new InvalidArgumentException('Kolom AWAL/TAHUNAN tidak ditemukan pada header MONCU.');

        $perMonths = [1 => 1, 2 => 1, 3 => 1, 4 => 2, 5 => 2, 6 => 2, 7 => 3, 8 => 3, 9 => 3, 10 => 4, 11 => 4, 12 => 4];
        $emps = []; $warn = [];
        // urutan a/b/y ditentukan per kolom (sheet bisa berisi 13/09/22 dan 04/20/2026 sekaligus)
        $hireVals = []; $awalVals = [];
        for ($i = $sub + 1; $i < count($rows); $i++) {
            if (!self::isNrp(self::cell($rows[$i], $nrpCol))) continue;
            $hireVals[] = self::cell($rows[$i], $hireCol);
            foreach ($blocks as $bi => $b) $awalVals[$bi][] = self::cell($rows[$i], $b['awal']);
        }
        $hireOrder = self::detectSlashOrder($hireVals);
        $awalOrder = []; foreach ($blocks as $bi => $b) $awalOrder[$bi] = self::detectSlashOrder($awalVals[$bi] ?? []);

        for ($i = $sub + 1; $i < count($rows); $i++) {
            $r = $rows[$i]; $nrp = self::cell($r, $nrpCol);
            if (!self::isNrp($nrp)) continue;
            $raw = []; $labeled = [];
            foreach ($blocks as $bi => $b) {
                $aw = self::parseDate(self::cell($r, $b['awal']), $awalOrder[$bi]);
                if (!$aw) continue;
                $t = self::cell($r, $b['tahunan']); $iz = $b['izin'] === null ? '' : self::cell($r, $b['izin']);
                $p = ['awal' => $aw, 'tahunan' => preg_match('/^-?\d+$/', $t) ? (int)$t : 0, 'izin' => preg_match('/^\d+$/', $iz) ? (int)$iz : 0];
                if ($b['fixed'] !== null) { $p['year'] = $b['fixed'][0]; $p['no'] = $b['fixed'][1]; $p['shifted'] = false; $labeled[] = $p; }   // label eksplisit di sheet dipakai apa adanya
                else $raw[] = $p;
            }
            usort($raw, function ($a, $b) { return strcmp($a['awal'], $b['awal']); });
            $taken = []; foreach ($labeled as $p) $taken[$p['year']][$p['no']] = true;
            // tanpa label: nomor = kuartal AWAL bila bebas; bentrok digeser supaya urut & muat 1..5 (5 = Periode Tambahan). >5 per tahun tidak terpetakan.
            $periods = $labeled; $unmapped = []; $byY = [];
            foreach ($raw as $p) $byY[(int)substr($p['awal'], 0, 4)][] = $p;
            foreach ($byY as $y => $list) {
                if (!empty($taken[$y])) {   // sebagian nomor tahun ini sudah dipakai label eksplisit: isi nomor bebas terdekat
                    foreach ($list as $p) {
                        $q = $perMonths[(int)substr($p['awal'], 5, 2)]; $no = null;
                        for ($n = $q; $n <= 5; $n++) if (empty($taken[$y][$n])) { $no = $n; break; }
                        if ($no === null) for ($n = $q - 1; $n >= 1; $n--) if (empty($taken[$y][$n])) { $no = $n; break; }
                        if ($no === null) { $unmapped[] = $p; continue; }
                        $taken[$y][$no] = true; $p['year'] = $y; $p['no'] = $no; $p['shifted'] = ($no !== $q); $periods[] = $p;
                    }
                    continue;
                }
                $keep = array_slice($list, 0, 5);
                foreach (array_slice($list, 5) as $x) $unmapped[] = $x;
                $a = [];
                foreach ($keep as $k => $p) { $q = $perMonths[(int)substr($p['awal'], 5, 2)]; $a[$k] = max($q, $k ? $a[$k - 1] + 1 : 1); }
                for ($k = count($keep) - 1; $k >= 0; $k--) { $lim = $k === count($keep) - 1 ? 5 : $a[$k + 1] - 1; $a[$k] = min($a[$k], $lim); }
                foreach ($keep as $k => $p) { $q = $perMonths[(int)substr($p['awal'], 5, 2)]; $p['year'] = $y; $p['no'] = $a[$k]; $p['shifted'] = ($a[$k] !== $q); $periods[] = $p; }
            }
            usort($periods, function ($a, $b) { return [$a['year'], $a['no']] <=> [$b['year'], $b['no']]; });
            if (isset($emps[$nrp])) $warn[] = "NRP $nrp muncul lebih dari sekali pada tab MONCU; baris terakhir dipakai.";
            $emps[$nrp] = ['name' => self::cell($r, $nameCol), 'hiring' => self::parseDate(self::cell($r, $hireCol), $hireOrder), 'hiring_raw' => self::cell($r, $hireCol),
                           'periods' => $periods, 'unmapped' => $unmapped];
        }
        if (!$emps) throw new InvalidArgumentException('Tidak ada baris karyawan (NRP) yang ditemukan pada tab MONCU.');
        return ['employees' => $emps, 'warnings' => $warn];
    }
}
