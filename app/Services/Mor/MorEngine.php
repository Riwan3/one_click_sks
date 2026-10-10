<?php

namespace App\Services\Mor;

use DateTime;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Mesin penilaian & ranking MOR, port dari V1
 * database/quartal_operator_report/mor_ranking_engine.php (fungsi morXl,
 * morLookupPoint, morScoreSheet, morBuildRankList, morTypeFromText,
 * morResolveTypes, morMasaKerjaMap, morMonthlyRanking, snapshot).
 *
 * Aturan (tabel poin, kunci ranking, prasyarat, kuota IS/BS) dibaca dari
 * config/mor_rules.php; kelas ini hanya mengeksekusinya. Input per operator
 * memakai nama kolom MOR (ATR, HM, PDTY (%), TIMESHEET, FUEL, TOTAL PEMBINAAN,
 * MASA KERJA (BULAN)) dalam bentuk rasio seperti di Excel (ATR 0.98, bukan 98).
 *
 * Logika sengaja dibiarkan sama persis dengan V1 (termasuk perbandingan array
 * PHP untuk tie-break) supaya hasilnya identik; ubah hanya bersama V1/Excel.
 */
class MorEngine
{
    public const SHEET_ORDER = ['DT', 'WT773', 'VIBRO', 'WA&WTDT', 'GD', 'DZ85', 'PC200', 'DZ155', 'PC300', 'PC395', 'PC1250 6015', 'PC2000', 'HD'];

    public function rules(): array
    {
        return config('mor_rules');
    }

    // ── Penilaian persis sheet MOR ──────────────────────────────────────────

    /**
     * Excel menyimpan & membandingkan angka pada presisi 15 digit; tanpa ini
     * 71.599999999999994 dan 71.6 dianggap beda dan ranking seri bisa bergeser.
     */
    public static function xl($x): ?float
    {
        return $x === null ? null : (float) sprintf('%.15g', (float) $x);
    }

    /** MATCH(nilai, kolom_kunci, 1) + INDEX: kunci terbesar yang <= nilai; di bawah kunci terkecil -> 0. */
    public static function lookupPoint(array $table, $value): float
    {
        if ($value === null) {
            return 0.0;
        }
        $value = self::xl($value);
        $point = 0.0;
        foreach ($table as [$key, $pt]) {
            if ($value >= self::xl($key)) {
                $point = (float) $pt;
            } else {
                break;
            }
        }

        return $point;
    }

    /**
     * Hitung satu sheet kategori.
     *
     * @param  array  $ops  [['nrp','nama','jabatan','inputs'=>[...],'has_sp'=>bool], ...]
     * @return array $ops yang sama + 'values', 'total', 'rank', 'prasyarat', 'prasyarat_gagal'
     */
    public function scoreSheet(string $sheetName, array $ops): array
    {
        $rules = $this->rules();
        $sheet = $rules['sheets'][$sheetName];

        foreach ($ops as &$op) {
            $vals = $op['inputs'];
            $vals['N/A (20)'] = 20;
            foreach ($sheet['points'] as $name => $def) {
                if ($def['type'] === 'lookup') {
                    $vals[$name] = self::lookupPoint($def['table'], $vals[$def['input']] ?? null);
                } else { // pembinaan: IF((L+M)>cap, cap, L+M)
                    $sum = (float) ($vals[$def['a']] ?? 0) + (float) ($vals[$def['b']] ?? 0);
                    $vals[$name] = $sum > $def['cap'] ? $def['cap'] : $sum;
                }
            }
            $total = 0.0;
            foreach ($sheet['total_of'] as $name) {
                $total += (float) $vals[$name];
            }
            $vals['TOTAL POIN'] = $total;

            $gagal = [];
            if ($sheet['prasyarat_no_sp'] && ! empty($op['has_sp'])) {
                $gagal[] = ['input' => 'SP', 'nilai' => null, 'minimal' => null];
            }
            foreach ($sheet['prasyarat'] as $cond) {
                $v = $vals[$cond['input']] ?? null;
                $min = $rules['lain2'][$cond['min']];
                if ($v === null || self::xl($v) < self::xl($min)) {
                    $gagal[] = ['input' => $cond['input'], 'nilai' => $v, 'minimal' => $min];
                }
            }
            $op['prasyarat_gagal'] = $gagal;

            $op['values'] = $vals;
            $op['total'] = $total;
            $op['prasyarat'] = $gagal ? 'FALSE' : 'OK';
            $op['rank_key'] = array_map(fn ($k) => self::xl($vals[$k] ?? 0), $sheet['rank_keys']);
        }
        unset($op);

        // RANKING = 1 + jumlah operator yang unggul pada urutan kunci COUNTIFS.
        $n = count($ops);
        for ($i = 0; $i < $n; $i++) {
            $better = 0;
            for ($j = 0; $j < $n; $j++) {
                if ($j !== $i && $ops[$j]['rank_key'] > $ops[$i]['rank_key']) {
                    $better++;
                }
            }
            $ops[$i]['rank'] = 1 + $better;
        }

        return $ops;
    }

    /**
     * Sheet RANK: gabungkan semua sheet (urutan VSTACK), urutkan PRA SYARAT desc ->
     * JABATAN asc -> RANKING asc (SORT bertingkat Excel bersifat stabil), lalu beri
     * NO per jabatan dan KATEGORI IS/BS memakai kuota sheet kategorinya.
     */
    public function buildRankList(array $scoredBySheet): array
    {
        $rules = $this->rules();

        $quota = [];
        foreach ($scoredBySheet as $sheet => $ops) {
            $lolos = count(array_filter($ops, fn ($o) => $o['prasyarat'] === 'OK'));
            $pct = $rules['sheets'][$sheet]['quota_pct'];
            $quota[$sheet] = [
                'IS' => (int) round(self::xl(($pct['IS'] ?? 0) * $lolos)),
                'BS' => (int) round(self::xl(($pct['BS'] ?? 0) * $lolos)),
            ];
        }

        $list = [];
        $seq = 0;
        foreach (self::SHEET_ORDER as $sheet) {
            foreach ($scoredBySheet[$sheet] ?? [] as $op) {
                $op['sheet'] = $sheet;
                $op['seq'] = $seq++;
                $list[] = $op;
            }
        }
        usort($list, function ($a, $b) {
            return [$b['prasyarat'], strtoupper($a['jabatan']), $a['rank'], $a['seq']]
               <=> [$a['prasyarat'], strtoupper($b['jabatan']), $b['rank'], $b['seq']];
        });

        $posByJabatan = [];
        foreach ($list as &$op) {
            $pos = $posByJabatan[$op['jabatan']] = ($posByJabatan[$op['jabatan']] ?? 0) + 1;
            $op['no'] = $pos;
            $quotaSheet = $rules['rank_kategori'][$op['jabatan']] ?? null;
            $q = $quotaSheet ? ($quota[$quotaSheet] ?? ['IS' => 0, 'BS' => 0]) : ['IS' => 0, 'BS' => 0];
            $op['kategori_rank'] = $pos <= $q['IS'] ? 'IS' : ($pos <= $q['IS'] + $q['BS'] ? 'BS' : '');
        }
        unset($op);

        return $list;
    }

    // ── Jabatan (TYPE) & masa kerja ─────────────────────────────────────────

    /**
     * JABATAN/TYPE di MOR mengikuti unit yang dipegang operator di roster, bukan
     * posisi absensi. Unit Roster V2 -> kategori unit Manpower -> TYPE; posisi
     * absensi hanya cadangan.
     */
    public static function typeFromText($text): ?string
    {
        $t = strtoupper(preg_replace('/\s+/', ' ', (string) $text));
        $n = preg_replace('/[^A-Z0-9]/', '', $t);
        if ($n === '') {
            return null;
        }
        if (str_contains($n, '1250') || str_contains($n, '6015')) {
            return 'OPERATOR PC 1250/6015';
        }
        if (str_contains($n, '2000')) {
            return 'OPERATOR PC 2000';
        }
        if (str_contains($n, '395')) {
            return 'OPERATOR CAT 395';
        }
        if (str_contains($n, '300')) {
            return 'OPERATOR PC 300';
        }
        if (preg_match('/(PC|EXCAVATOR)2(00|10)/', $n)) {
            return 'OPERATOR PC 200';
        }
        if (str_contains($n, '155')) {
            return 'OPERATOR D155';
        }
        if (str_contains($n, 'D85') || str_contains($n, 'BULLDOZER85')) {
            return 'OPERATOR D85';
        }
        if (str_contains($n, 'GD') || str_contains($n, 'GRADER')) {
            return 'OPERATOR GD 825';
        }
        if (str_contains($n, 'VIBRO') || preg_match('/(^|OPERATOR|MASTER)(VB|BW)/', $n)) {
            return 'OPERATOR VB 20T';
        }
        if (str_contains($n, '480') || str_contains($n, 'WHEELLOADER') || str_contains($n, 'MASTERWL')) {
            return 'OPERATOR WA 480';
        }
        if (str_contains($n, '773') || str_contains($n, 'SKT')) {
            return 'OPERATOR CAT 773';
        }
        if (str_contains($n, 'WATERTRUCK') || str_contains($n, 'WT20KL') || preg_match('/^(MASTER)?WT$/', $n)) {
            return 'OPERATOR WATER TRUCK';
        }
        if (str_contains($n, 'HD') || str_contains($n, '785') || str_contains($n, '777') || str_contains($n, 'OHT')
            || str_contains($n, 'DUMPTRUCKKOMATSU') || str_contains($n, 'HEAVYDUMP') || str_contains($n, 'OFFHIGHWAY')) {
            return 'OPERATOR HAULER';
        }
        if (str_contains($n, 'DT') || str_contains($n, 'DUMPTRUCK')) {
            return 'OPERATOR DT 31 - 50T';
        }

        return null;
    }

    /** @return array<string, array{type: ?string, unit: ?string, sumber: string}> */
    public function resolveTypes(string $start, string $end, array $posisiByNrp): array
    {
        $unitCat = [];
        try {
            $rows = DB::select('SELECT UPPER(TRIM(u.unit)) unit, c.name FROM v2_mp_units u JOIN v2_mp_categories c ON c.id = u.category_id');
            foreach ($rows as $r) {
                $unitCat[$r->unit] = $r->name;
            }
        } catch (Throwable) {
        }

        $rosterUnit = [];
        try {
            $rows = DB::select("SELECT m.nrp, m.unit FROM v2_roster_members m JOIN v2_rosters r ON r.id = m.roster_id
                WHERE r.period_start <= ? AND r.period_end >= ? AND m.unit IS NOT NULL AND m.unit <> ''
                ORDER BY r.status = 'published' DESC, r.period_start DESC", [$end, $start]);
            foreach ($rows as $r) {
                $rosterUnit[$r->nrp] ??= strtoupper(trim($r->unit));
            }
        } catch (Throwable) {
        }

        $out = [];
        foreach ($posisiByNrp as $nrp => $posisi) {
            $unit = $rosterUnit[$nrp] ?? null;
            $type = null;
            $sumber = 'posisi';
            if ($unit !== null) {
                $type = self::typeFromText($unitCat[$unit] ?? $unit);
                if ($type) {
                    $sumber = isset($unitCat[$unit]) ? 'unit' : 'unit (teks)';
                }
            }
            if (! $type) {
                $type = self::typeFromText($posisi);
            }
            $out[$nrp] = ['type' => $type, 'unit' => $unit, 'sumber' => $type ? $sumber : 'tidak dikenali'];
        }

        return $out;
    }

    /** join_date ODP berformat "1-Okt-18" (singkatan bulan Indonesia) yang tidak dikenali strtotime. */
    public static function parseTanggal($raw): ?string
    {
        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }
        $bulan = ['jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'mei' => 5, 'may' => 5, 'jun' => 6, 'jul' => 7,
            'agu' => 8, 'agt' => 8, 'ags' => 8, 'aug' => 8, 'sep' => 9, 'okt' => 10, 'oct' => 10, 'nov' => 11, 'des' => 12, 'dec' => 12];
        if (preg_match('/^(\d{1,2})[-\s]([A-Za-z]{3})[A-Za-z]*[-\s](\d{2}|\d{4})$/', $s, $m)) {
            $mon = $bulan[strtolower($m[2])] ?? null;
            if (! $mon) {
                return null;
            }
            $y = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

            return checkdate($mon, (int) $m[1], $y) ? sprintf('%04d-%02d-%02d', $y, $mon, (int) $m[1]) : null;
        }
        $ts = strtotime($s);

        return $ts ? date('Y-m-d', $ts) : null;
    }

    /**
     * Masa kerja dalam bulan penuh, seperti DATEDIF(tgl_masuk, TODAY(), "m") di MOR.
     * join_date ODP grade menimpa employees.efektif_bekerja (cadangan).
     */
    public function masaKerjaMap(array $nrps): array
    {
        if (! $nrps) {
            return [];
        }
        $ph = implode(',', array_fill(0, count($nrps), '?'));
        $joinDate = [];
        $sources = [
            "SELECT nrp, efektif_bekerja AS tgl FROM employees WHERE nrp IN ($ph)",
            "SELECT nrp, join_date AS tgl FROM odp_mg_grade_v4 WHERE nrp IN ($ph)",
        ];
        foreach ($sources as $sql) {
            try {
                foreach (DB::select($sql, array_values($nrps)) as $r) {
                    $tgl = self::parseTanggal($r->tgl);
                    if ($tgl) {
                        $joinDate[trim($r->nrp)] = $tgl; // sumber belakangan menimpa
                    }
                }
            } catch (Throwable) {
            }
        }
        $today = new DateTime('today');
        $out = [];
        foreach ($joinDate as $nrp => $tgl) {
            $d = (new DateTime($tgl))->diff($today);
            $out[$nrp] = $d->invert ? 0 : $d->y * 12 + $d->m;
        }

        return $out;
    }

    // ── Ranking satu periode ────────────────────────────────────────────────

    /**
     * Ranking MOR dari baris Operator Report per NRP (atr_persen, hm, hm_target,
     * pdty_combined, posisi, nama, ...).
     *
     * @return array{sheets: array, rank: array, types: array}
     */
    public function monthlyRanking(array $rows, string $start, string $end, array $tsMap, array $pembMap): array
    {
        $posisi = [];
        foreach ($rows as $r) {
            $posisi[$r['nrp']] = $r['posisi'] ?? '';
        }
        $types = $this->resolveTypes($start, $end, $posisi);
        $masaKerja = $this->masaKerjaMap(array_keys($posisi));

        $ops = [];
        foreach ($rows as $r) {
            $nrp = $r['nrp'];
            $type = $types[$nrp]['type'] ?? null;
            if ($type === null) {
                continue;
            }
            $hmTarget = (float) ($r['hm_target'] ?? 0);
            $ts = $tsMap[$nrp] ?? null;
            $ops[$nrp] = [
                'nrp' => $nrp, 'nama' => $r['nama'] ?? '', 'jabatan' => $type,
                // Angka Operator Report apa adanya, untuk "Detail Produksi" di Raport MOR.
                'produksi' => array_intersect_key($r, array_flip([
                    'posisi', 'atr_persen', 'timesheet_persen', 'hm_target', 'hm', 'hm_ach',
                    'pdty_coal', 'pdty_ob', 'pdty_combined', 'loader', 'jarak', 'kode_unit',
                    'rit_ob', 'total_bcm', 'rit_coal', 'total_ton',
                ])),
                'unit' => $types[$nrp]['unit'], 'type_sumber' => $types[$nrp]['sumber'],
                'has_sp' => ! empty($pembMap[$nrp]['has_sp']),
                'inputs' => [
                    'ATR' => (float) $r['atr_persen'] / 100,
                    // K di PERFORMA: IFERROR(HM / (hari kerja x 7), 0)
                    'HM' => ($hmTarget > 0 && $r['hm'] !== null) ? (float) $r['hm'] / $hmTarget : 0.0,
                    'PDTY (%)' => isset($r['pdty_combined']) && $r['pdty_combined'] !== null ? (float) $r['pdty_combined'] / 100 : null,
                    'TIMESHEET' => $ts ? (float) $ts['achievement'] / 100 : 0.0,
                    // Sheet FUEL di MOR kosong -> FUEL ACH = 0 untuk semua operator.
                    'FUEL' => 0.0,
                    'TOTAL PEMBINAAN' => (float) ($pembMap[$nrp]['total'] ?? 0),
                    'MASA KERJA (BULAN)' => $masaKerja[$nrp] ?? null,
                ],
            ];
        }

        $scored = [];
        foreach ($this->rules()['sheets'] as $sheet => $def) {
            $members = array_values(array_filter($ops, fn ($o) => stripos($o['jabatan'], $def['member_search']) !== false));
            $scored[$sheet] = $members ? $this->scoreSheet($sheet, $members) : [];
        }

        return ['sheets' => $scored, 'rank' => $this->buildRankList($scored), 'types' => $types];
    }

    // ── Snapshot Raport MOR (dibaca "MOR Saya") ─────────────────────────────
    // M = bulan (bulan = 1..12), Q = quartal (bulan = 1..4), Y = tahun (bulan = 0).

    /** @return array{0: string, 1: string} */
    public static function periodRange(string $periode, int $tahun, int $nomor): array
    {
        if ($periode === 'Y') {
            return [sprintf('%04d-01-01', $tahun), sprintf('%04d-12-31', $tahun)];
        }
        if ($periode === 'Q') {
            $start = sprintf('%04d-%02d-01', $tahun, ($nomor - 1) * 3 + 1);

            return [$start, date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $tahun, $nomor * 3)))];
        }
        $start = sprintf('%04d-%02d-01', $tahun, $nomor);

        return [$start, date('Y-m-t', strtotime($start))];
    }

    /**
     * Tanpa DPR/SAP, HM & PDTY semua operator 0 -> total poin seri dan semua "#1".
     * Raport seperti itu menyesatkan, jadi hanya periode berdata produksi yang disimpan.
     */
    public function rangeHasProductionData(string $start, string $end): bool
    {
        $row = DB::selectOne('SELECT EXISTS(SELECT 1 FROM qor_dpr WHERE tanggal BETWEEN ? AND ?)
            OR EXISTS(SELECT 1 FROM qor_sap_hm WHERE tanggal BETWEEN ? AND ?) AS ada', [$start, $end, $start, $end]);

        return (bool) $row->ada;
    }

    public function deleteSnapshot(int $tahun, int $nomor, string $periode = 'M'): void
    {
        DB::table('mor_raport_bulanan')->where(['tahun' => $tahun, 'periode' => $periode, 'bulan' => $nomor])->delete();
    }

    public function saveSnapshot(int $tahun, int $nomor, array $morResult, string $periode = 'M'): void
    {
        if (! $this->rangeHasProductionData(...self::periodRange($periode, $tahun, $nomor))) {
            $this->deleteSnapshot($tahun, $nomor, $periode);

            return;
        }
        $rules = $this->rules();
        $kategori = [];
        foreach ($morResult['rank'] as $op) {
            $kategori[$op['sheet'].'|'.$op['nrp']] = $op['kategori_rank'];
        }

        DB::transaction(function () use ($tahun, $nomor, $periode, $morResult, $rules, $kategori) {
            $this->deleteSnapshot($tahun, $nomor, $periode);
            $now = now()->format('Y-m-d H:i:s');
            foreach ($morResult['sheets'] as $sheet => $ops) {
                $def = $rules['sheets'][$sheet];
                $komponen = [];
                foreach ($def['points'] as $name => $p) {
                    $komponen[$name] = [
                        'input' => $p['type'] === 'lookup' ? $p['input'] : 'TOTAL PEMBINAAN',
                        'maks' => $p['type'] === 'lookup' ? max(array_column($p['table'], 1)) : $p['cap'],
                    ];
                }
                $rows = [];
                foreach ($ops as $op) {
                    $detail = [
                        'inputs' => $op['inputs'],
                        'poin' => array_intersect_key($op['values'], $komponen),
                        'komponen' => $komponen,
                        'prasyarat_gagal' => $op['prasyarat_gagal'],
                        'member_search' => $def['member_search'],
                        'produksi' => $op['produksi'] ?? [],
                    ];
                    $rows[] = [
                        'tahun' => $tahun, 'periode' => $periode, 'bulan' => $nomor, 'sheet' => $sheet,
                        'nrp' => $op['nrp'], 'nama' => $op['nama'], 'jabatan' => $op['jabatan'],
                        'ranking' => $op['rank'], 'jumlah' => count($ops), 'total_poin' => round($op['total'], 2),
                        'prasyarat' => $op['prasyarat'], 'kategori' => $kategori[$sheet.'|'.$op['nrp']] ?? '',
                        'detail' => json_encode($detail), 'computed_at' => $now,
                    ];
                }
                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table('mor_raport_bulanan')->insert($chunk);
                }
            }
        });
    }

    /** Bulan (YYYY-MM) yang datanya berubah ditandai, lalu dihitung ulang pada kesempatan berikutnya. */
    public function markSnapshotDirty(array $yms): void
    {
        if (! $yms) {
            return;
        }
        $cur = json_decode((string) DB::table('settings')->where('setting_key', 'mor_raport_dirty')->value('setting_value'), true) ?: [];
        $all = array_values(array_unique(array_merge($cur, $yms)));
        DB::table('settings')->updateOrInsert(
            ['setting_key' => 'mor_raport_dirty'],
            ['setting_value' => json_encode($all), 'updated_at' => now()]
        );
    }

    public static function quarterMonths(int $tahun, int $quartal): array
    {
        $out = [];
        for ($m = ($quartal - 1) * 3 + 1; $m <= $quartal * 3; $m++) {
            $out[] = sprintf('%04d-%02d', $tahun, $m);
        }

        return $out;
    }

    /**
     * QTD/YTD: rentang dipotong ke bulan pertama..terakhir yang punya data produksi.
     * Tanpa ini HM Target & ATR menghitung bulan yang DPR-nya belum diimport.
     *
     * @return array{0: string, 1: string}
     */
    public function effectiveRange(string $start, string $end): array
    {
        $row = DB::selectOne('SELECT MIN(t) mn, MAX(t) mx FROM (
                SELECT MIN(tanggal) t FROM qor_dpr WHERE tanggal BETWEEN ? AND ?
                UNION ALL SELECT MAX(tanggal) FROM qor_dpr WHERE tanggal BETWEEN ? AND ?
                UNION ALL SELECT MIN(tanggal) FROM qor_sap_hm WHERE tanggal BETWEEN ? AND ?
                UNION ALL SELECT MAX(tanggal) FROM qor_sap_hm WHERE tanggal BETWEEN ? AND ?
            ) x', [$start, $end, $start, $end, $start, $end, $start, $end]);
        if (! $row->mn) {
            return [$start, $end];
        }

        return [date('Y-m-01', strtotime($row->mn)), date('Y-m-t', strtotime($row->mx))];
    }
}
