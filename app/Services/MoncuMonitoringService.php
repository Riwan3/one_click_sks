<?php

namespace App\Services;

use App\Exceptions\RosterV2Forbidden;
use App\Exceptions\RosterV2NotFound;
use App\Repositories\MoncuMonitoringRepository;
use DateTimeImmutable;

//
// MONCU Monitoring - lapisan MONITORING READ-ONLY di atas data MONCU V1.
// TIDAK ada INSERT/UPDATE/DELETE, tidak memanggil sinkron roster, tidak mengubah AWAL/AKHIR/INDUKSI/PLAN.
// Satu-satunya pemakaian core MONCU adalah fungsi hitung murni MoncuService::calculateAnnualEntitlement().
//
// ------------------------------------------------------------ Status engine (getMoncuStatus) - FINAL (Step 6.1)
// Status dihitung dari TANGGAL AKTUAL terhadap "tanggal acuan" (as-of) dan URUTAN PERIODE MONCU
// (cycle_year, period_no). TIDAK ada logika kalender/triwulan: period MONCU adalah period bisnis, bukan quarter.
// Urutan prioritas (yang pertama cocok menang):
//   1. CUTI            : ada periode dengan AWAL <= as-of <= AKHIR
//   2. INDUKSI         : ada periode dengan INDUKSI = as-of      (INDUKSI = AKHIR + 1, dicek sebelum status lain)
//   3. AKAN CUTI       : ada periode ber-AWAL aktual dengan AWAL > as-of (dipilih yang terdekat)
//   4. BELUM ADA AWAL  : tidak ada cuti aktif / induksi / AWAL mendatang, TETAPI masih ada periode MONCU
//                        berikutnya (menurut urutan cycle_year, period_no) yang belum punya AWAL
//   5. SELESAI         : semua periode ber-AWAL sudah selesai dan tidak ada periode berikutnya yang tersedia
// "Periode berikutnya tersedia" = setelah periode ber-AWAL TERAKHIR (urut cycle_year, period_no) masih ada slot
// pada tahun yang dipantau: bila periode terakhir itu dari tahun sebelumnya -> slot 1..N tahun ini tersedia;
// bila dari tahun yang dipantau -> tersedia selama period_no < jumlah periode per tahun (4); tanpa periode sama
// sekali -> periode 1 tersedia.
// PLAN tidak pernah mempengaruhi status; PLAN hanya ditampilkan ("PLAN Berikutnya") dan dipakai "Perlu Perhatian".
//
// ------------------------------------------------------------ Konteks tahun (as-of)
//   Tahun berjalan  : as-of = hari ini              -> "Status Hari Ini"
//   Tahun lampau    : as-of = 31 Des tahun itu       -> "Status Periode" (Data Historis, BUKAN kondisi hari ini)
//   Tahun mendatang : as-of = 1 Jan tahun itu        -> "Status Periode" (Periode Mendatang)
// "Perlu Perhatian" (PLAN dekat/lewat, roster) hanya relevan untuk kondisi hari ini -> hanya tahun berjalan.

class MoncuMonitoringService {
    const S_CUTI = 'CUTI';
    const S_INDUKSI = 'INDUKSI';
    const S_AKAN = 'AKAN CUTI';
    const S_BELUM = 'BELUM ADA AWAL';
    const S_SELESAI = 'SELESAI';
    const STATUSES = [self::S_CUTI, self::S_INDUKSI, self::S_AKAN, self::S_BELUM, self::S_SELESAI];

    const MODE_CURRENT = 'current';
    const MODE_HISTORICAL = 'historical';
    const MODE_FUTURE = 'future';

    /** "PLAN sudah dekat" = PLAN jatuh dalam N hari ke depan (parameter tampilan, bukan aturan status). */
    const PLAN_NEAR_DAYS = 14;
    const PERIODS_PER_YEAR = 4;   // periode REGULER (config regular_periods): menentukan status SELESAI; periode 5 = tambahan opsional
    const SLOTS_PER_YEAR = 5;     // slot yang ditampilkan di detail (4 reguler + Periode Tambahan)
    const SORTS = ['nrp', 'nama', 'jabatan', 'group', 'status', 'awal', 'akhir', 'induksi', 'plan'];
    const MAX_PER_PAGE = 100;

    private $repo;
    private $core;

    public function __construct(MoncuMonitoringRepository $repo, MoncuService $core) {
        $this->repo = $repo;
        $this->core = $core; // hanya untuk fungsi hitung murni (hak tahunan)
    }

    public static function canView() { return MoncuService::canView(); }

    private function requireView() {
        if (!self::canView()) throw new RosterV2Forbidden('Tidak berwenang melihat MONCU Monitoring.');
    }

    // ------------------------------------------------------------- helper (murni)

    /** Periode ikut lingkup tahun Y: cycle_year = Y atau ada tanggal (AWAL/AKHIR/INDUKSI) di tahun Y. */
    private static function inYearScope(array $p, $year) {
        if ((int)$p['cycle_year'] === (int)$year) return true;
        foreach (['awal', 'akhir', 'induksi'] as $k) {
            if (!empty($p[$k]) && (int)substr($p[$k], 0, 4) === (int)$year) return true;
        }
        return false;
    }

    /** Konteks tahun: mode, tanggal acuan (as-of), label. Murni. */
    public static function buildContext($year, $today) {
        $cy = (int)substr($today, 0, 4);
        if ($year === $cy) {
            return ['mode' => self::MODE_CURRENT, 'year' => $year, 'as_of' => $today, 'today' => $today,
                    'label' => "Tahun $year — Posisi hari ini", 'status_label' => 'Status Hari Ini'];
        }
        if ($year < $cy) {
            return ['mode' => self::MODE_HISTORICAL, 'year' => $year, 'as_of' => "$year-12-31", 'today' => $today,
                    'label' => "Tahun $year — Data Historis", 'status_label' => 'Status Periode'];
        }
        return ['mode' => self::MODE_FUTURE, 'year' => $year, 'as_of' => "$year-01-01", 'today' => $today,
                'label' => "Tahun $year — Periode Mendatang", 'status_label' => 'Status Periode'];
    }

    // ---------------------------------------------------------------- STATUS ENGINE

    /**
     * @param array  $periods kandidat (sudah dibatasi ke lingkup tahun)
     * @param string $asOf    Y-m-d tanggal acuan
     * @param int    $year    tahun yang dipantau (untuk menentukan "periode berikutnya tersedia")
     * @return array ['status'=>string, 'period'=>?array]  period = periode yang relevan untuk status itu
     */
    public static function getMoncuStatus(array $periods, $asOf, $year) {
        // 1. CUTI
        foreach ($periods as $p) {
            if ($p['awal'] && $p['akhir'] && $p['awal'] <= $asOf && $asOf <= $p['akhir']) {
                return ['status' => self::S_CUTI, 'period' => $p];
            }
        }
        // 2. INDUKSI
        foreach ($periods as $p) {
            if ($p['induksi'] && $p['induksi'] === $asOf) return ['status' => self::S_INDUKSI, 'period' => $p];
        }
        // 3. AKAN CUTI: AWAL aktual terdekat di masa depan
        $up = null;
        foreach ($periods as $p) {
            if ($p['awal'] && $p['awal'] > $asOf && ($up === null || $p['awal'] < $up['awal'])) $up = $p;
        }
        if ($up) return ['status' => self::S_AKAN, 'period' => $up];

        // 4/5. Periode ber-AWAL terakhir menurut URUTAN MONCU (cycle_year, period_no) - bukan bulan kalender.
        $last = null;
        foreach ($periods as $p) {
            if (empty($p['awal'])) continue;
            if ($last === null
                || (int)$p['cycle_year'] > (int)$last['cycle_year']
                || ((int)$p['cycle_year'] === (int)$last['cycle_year'] && (int)$p['period_no'] > (int)$last['period_no'])) {
                $last = $p;
            }
        }
        if ($last === null) return ['status' => self::S_BELUM, 'period' => null];   // periode 1 belum punya AWAL
        $nextAvailable = (int)$last['cycle_year'] < (int)$year
            || ((int)$last['cycle_year'] === (int)$year && (int)$last['period_no'] < self::PERIODS_PER_YEAR);
        return $nextAvailable
            ? ['status' => self::S_BELUM, 'period' => null]
            : ['status' => self::S_SELESAI, 'period' => $last];
    }

    /** Alias sesuai kebutuhan spesifikasi ("getStatus()"). */
    public static function getStatus(array $periods, $asOf, $year) {
        return self::getMoncuStatus($periods, $asOf, $year);
    }

    /** PLAN Berikutnya: PLAN periode terakhir yang sudah dimulai (AWAL <= as-of); bila belum ada, PLAN periode terdekat. */
    private static function planNext(array $all, $asOf) {
        $started = null; $upcoming = null;
        foreach ($all as $p) {
            if (empty($p['awal']) || empty($p['plan'])) continue;
            if ($p['awal'] <= $asOf) { if ($started === null || $p['awal'] > $started['awal']) $started = $p; }
            else { if ($upcoming === null || $p['awal'] < $upcoming['awal']) $upcoming = $p; }
        }
        $p = $started ?? $upcoming;
        return $p ? $p['plan'] : null;
    }

    // --------------------------------------------------------------- filter & evaluasi

    private function normalizeFilters(array $in, $today) {
        $year = (int)($in['year'] ?? 0);
        if ($year < 2000 || $year > 2100) $year = (int)substr($today, 0, 4);
        $status = trim((string)($in['status'] ?? ''));
        $ctx = self::buildContext($year, $today);
        return [
            'year' => $year,
            'group' => mb_substr(trim((string)($in['group'] ?? '')), 0, 100),
            'jabatan' => mb_substr(trim((string)($in['jabatan'] ?? '')), 0, 150),
            'status' => in_array($status, self::STATUSES, true) ? $status : '',
            'q' => mb_substr(trim((string)($in['q'] ?? '')), 0, 50),
            'mode' => $ctx['mode'], 'as_of' => $ctx['as_of'], 'today' => $today,
        ];
    }

    /** Evaluasi satu karyawan. $all = semua periode; $members = keanggotaan roster. */
    private function evaluateEmployee(array $e, array $all, array $members, array $f) {
        $asOf = $f['as_of']; $year = $f['year']; $current = $f['mode'] === self::MODE_CURRENT;
        $cand = array_values(array_filter($all, fn($p) => self::inYearScope($p, $year)));
        $st = self::getMoncuStatus($cand, $asOf, $year);
        $p = $st['period'];
        // PLAN Berikutnya: kondisi hari ini memakai semua periode; data historis/mendatang hanya periode dalam lingkup tahun itu
        // (agar PLAN tahun lain tidak terbaca sebagai bagian dari tahun yang sedang dilihat).
        $planNext = self::planNext($current ? $all : $cand, $asOf);

        // Keanggotaan Roster V2 hanya bermakna untuk kondisi hari ini (tahun berjalan).
        $inRoster = null; $range = [$asOf, $asOf];
        if ($current) {
            if ($p && in_array($st['status'], [self::S_CUTI, self::S_INDUKSI, self::S_AKAN], true)) {
                $range = [$p['awal'], $p['induksi'] ?: $p['akhir']];
            }
            $inRoster = false;
            foreach ($members as $m) {
                if ($m['period_start'] <= $range[1] && $m['period_end'] >= $range[0]) { $inRoster = true; break; }
            }
        }

        $planShown = $p ? ($p['plan'] ?? null) : null;
        $planIsRef = false;
        if ($planShown === null && $planNext !== null && !$p) { $planShown = $planNext; $planIsRef = true; }

        // kondisi data untuk "Perlu Perhatian" (dipakai hanya pada tahun berjalan)
        $noNext = !array_filter($all, fn($x) => !empty($x['awal']) && $x['awal'] > $asOf);
        $lastEnd = null;
        foreach ($all as $x) { if (!empty($x['awal']) && $x['awal'] <= $asOf && ($lastEnd === null || $x['akhir'] > $lastEnd)) $lastEnd = $x['akhir']; }
        $idle = in_array($st['status'], [self::S_BELUM, self::S_SELESAI], true);
        $neg = array_values(array_filter($cand, fn($x) => !empty($x['awal']) && $x['tahunan'] !== null && (int)$x['tahunan'] < 0));
        $nearEnd = (new DateTimeImmutable($asOf))->modify('+' . self::PLAN_NEAR_DAYS . ' days')->format('Y-m-d');

        return [
            'nrp' => $e['nrp'], 'name' => (string)$e['employee_name'], 'jabatan' => (string)$e['jabatan'],
            'group' => (string)$e['group_name'], 'hiring_date' => $e['hiring_date'],
            'status' => $st['status'], 'period' => $p,
            'awal' => $p['awal'] ?? null, 'akhir' => $p['akhir'] ?? null, 'induksi' => $p['induksi'] ?? null,
            'plan' => $planShown, 'plan_is_ref' => $planIsRef, 'plan_next' => $planNext,
            'in_roster' => $inRoster, 'roster_range' => $range,
            'flags' => [
                'plan_dekat' => $current && $idle && $noNext && $planNext !== null && $planNext >= $asOf && $planNext <= $nearEnd,
                'plan_lewat' => $current && $idle && $noNext && $planNext !== null && $planNext < $asOf,
                'tahunan_negatif' => $current && (bool)$neg,
                'belum_di_roster' => $current && $inRoster === false,
                'selesai_tanpa_berikutnya' => $current && $idle && $noNext && $lastEnd !== null && $lastEnd < $asOf,
            ],
            'last_end' => $lastEnd, 'neg_periods' => $neg,
        ];
    }

    /** Muat karyawan terfilter (tanpa filter status) dan evaluasi semuanya: 3 query tetap. */
    private function evaluateAll(array $f) {
        $emps = $this->repo->listEmployees($f);
        if (!$emps) return [];
        $nrps = array_column($emps, 'nrp');
        $periods = $this->repo->periodsForEmployees($nrps);
        $y = $f['year'];
        $members = $f['mode'] === self::MODE_CURRENT
            ? $this->repo->rosterMemberships($nrps, ($y - 1) . '-01-01', ($y + 1) . '-12-31')
            : [];   // roster tidak relevan untuk data historis / mendatang -> tidak diquery
        $out = [];
        foreach ($emps as $e) {
            $out[] = $this->evaluateEmployee($e, $periods[$e['nrp']] ?? [], $members[$e['nrp']] ?? [], $f);
        }
        return $out;
    }

    // ------------------------------------------------------------------- API layanan

    public function getFilterOptions() {
        $this->requireView();
        return ['groups' => $this->repo->listGroups(), 'jabatan' => $this->repo->listJabatan(), 'statuses' => self::STATUSES];
    }

    public function getSummary(array $filters, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        return $this->summarize($this->evaluateAll($this->normalizeFilters($filters, $today)));
    }

    private function summarize(array $ev) {
        $s = ['total' => count($ev), 'cuti' => 0, 'induksi' => 0, 'akan_cuti' => 0, 'belum_ada_awal' => 0, 'memiliki_plan' => 0, 'selesai' => 0];
        $map = [self::S_CUTI => 'cuti', self::S_INDUKSI => 'induksi', self::S_AKAN => 'akan_cuti', self::S_BELUM => 'belum_ada_awal', self::S_SELESAI => 'selesai'];
        foreach ($ev as $r) {
            $s[$map[$r['status']]]++;
            if ($r['plan_next'] !== null) $s['memiliki_plan']++;
        }
        return $s;
    }

    public function getAttentionItems(array $filters, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        $f = $this->normalizeFilters($filters, $today);
        return $this->attention($this->evaluateAll($f));
    }

    private function attention(array $ev) {
        $defs = [
            'plan_dekat' => 'PLAN sudah dekat (≤ ' . self::PLAN_NEAR_DAYS . ' hari) tetapi AWAL belum diisi',
            'plan_lewat' => 'PLAN sudah lewat tetapi AWAL masih kosong',
            'tahunan_negatif' => 'TAHUNAN negatif (memperpendek cuti)',
            'belum_di_roster' => 'Belum ada di Roster V2 untuk periode yang relevan',
            'selesai_tanpa_berikutnya' => 'Cuti sudah selesai, belum ada periode berikutnya',
        ];
        $out = [];
        foreach ($defs as $key => $label) {
            $items = [];
            foreach ($ev as $r) {
                if (!$r['flags'][$key]) continue;
                $detail = match ($key) {
                    'plan_dekat', 'plan_lewat' => 'PLAN ' . $r['plan_next'],
                    'tahunan_negatif' => 'TAHUNAN ' . implode(', ', array_map(fn($x) => $x['tahunan'] . ' (P' . $x['period_no'] . '/' . $x['cycle_year'] . ')', $r['neg_periods'])),
                    'belum_di_roster' => 'rentang ' . $r['roster_range'][0] . ($r['roster_range'][1] !== $r['roster_range'][0] ? ' s/d ' . $r['roster_range'][1] : ''),
                    'selesai_tanpa_berikutnya' => 'AKHIR terakhir ' . $r['last_end'],
                };
                $items[] = ['nrp' => $r['nrp'], 'name' => $r['name'], 'status' => $r['status'], 'detail' => $detail];
            }
            $out[$key] = ['label' => $label, 'count' => count($items), 'items' => $items];
        }
        return $out;
    }

    public function getMonitoringRows(array $filters, $sort = 'status', $dir = 'asc', $page = 1, $perPage = 25, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        $f = $this->normalizeFilters($filters, $today);
        return $this->buildRows($this->evaluateAll($f), $f, $sort, $dir, $page, $perPage);
    }

    private function buildRows(array $ev, array $f, $sort, $dir, $page, $perPage) {
        if ($f['status'] !== '') $ev = array_values(array_filter($ev, fn($r) => $r['status'] === $f['status']));
        $sort = in_array($sort, self::SORTS, true) ? $sort : 'status';
        $dir = strtolower((string)$dir) === 'desc' ? 'desc' : 'asc';
        $rank = array_flip(self::STATUSES);
        $key = function ($r) use ($sort, $rank) {
            return match ($sort) {
                'nrp' => $r['nrp'], 'nama' => mb_strtolower($r['name']), 'jabatan' => mb_strtolower($r['jabatan']),
                'group' => mb_strtolower($r['group']), 'status' => $rank[$r['status']],
                'awal' => $r['awal'], 'akhir' => $r['akhir'], 'induksi' => $r['induksi'], 'plan' => $r['plan'],
            };
        };
        usort($ev, function ($a, $b) use ($key, $dir) {
            $ka = $key($a); $kb = $key($b);
            if ($ka === null && $kb !== null) return 1;      // kosong selalu di bawah
            if ($kb === null && $ka !== null) return -1;
            $c = $ka <=> $kb;
            if ($c === 0) $c = strcmp($a['name'], $b['name']) ?: strcmp($a['nrp'], $b['nrp']);
            return $dir === 'desc' ? -$c : $c;
        });
        $perPage = max(1, min(self::MAX_PER_PAGE, (int)$perPage));
        $total = count($ev);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min(max(1, (int)$page), $pages);
        return ['rows' => array_slice($ev, ($page - 1) * $perPage, $perPage), 'total' => $total,
                'page' => $page, 'pages' => $pages, 'per_page' => $perPage, 'sort' => $sort, 'dir' => $dir];
    }

    /** Satu pass untuk halaman: konteks tahun + summary + perhatian + tabel dari evaluasi yang sama. */
    public function getOverview(array $filters, $sort = 'status', $dir = 'asc', $page = 1, $perPage = 25, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        $f = $this->normalizeFilters($filters, $today);
        $ev = $this->evaluateAll($f);
        $ctx = self::buildContext($f['year'], $today);
        return ['filters' => $f, 'today' => $today, 'context' => $ctx, 'summary' => $this->summarize($ev),
                'attention_applicable' => $ctx['mode'] === self::MODE_CURRENT,
                'attention' => $this->attention($ev), 'table' => $this->buildRows($ev, $f, $sort, $dir, $page, $perPage)];
    }

    /** Detail read-only satu karyawan (status dalam konteks tahun yang sama dengan tabel). */
    public function getEmployeeDetail($nrp, $year = null, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        $year = ($year && (int)$year >= 2000 && (int)$year <= 2100) ? (int)$year : (int)substr($today, 0, 4);
        $nrp = trim((string)$nrp);
        $e = $nrp === '' ? null : $this->repo->findEmployee($nrp);
        if (!$e) throw new RosterV2NotFound('Karyawan tidak ditemukan di MONCU.');

        $f = $this->normalizeFilters(['year' => $year], $today);
        $all = $this->repo->periodsForEmployees([$nrp])[$nrp] ?? [];
        $members = $f['mode'] === self::MODE_CURRENT
            ? ($this->repo->rosterMemberships([$nrp], ($year - 1) . '-01-01', ($year + 1) . '-12-31')[$nrp] ?? [])
            : [];
        $ev = $this->evaluateEmployee($e, $all, $members, $f);
        $ctx = self::buildContext($year, $today);

        $slots = [];
        for ($n = 1; $n <= self::SLOTS_PER_YEAR; $n++) {
            $slots[$n] = ['period_no' => $n, 'cycle_year' => $year, 'awal' => null, 'akhir' => null, 'tahunan' => null,
                          'izin_resmi' => null, 'induksi' => null, 'plan' => null];
        }
        $used = 0;
        foreach ($all as $p) {
            if ((int)$p['cycle_year'] !== $year) continue;
            $slots[(int)$p['period_no']] = array_intersect_key($p, $slots[1]);
            if (!empty($p['awal'])) $used += max(0, (int)$p['tahunan']);   // negatif tidak dihitung sebagai pemakaian
        }
        $ent = $this->core->calculateAnnualEntitlement($e['hiring_date'], $today);
        return [
            'employee' => ['nrp' => $e['nrp'], 'name' => (string)$e['employee_name'], 'jabatan' => (string)$e['jabatan'],
                           'group' => (string)$e['group_name'], 'hiring_date' => $e['hiring_date']],
            'year' => $year, 'today' => $today, 'context' => $ctx,
            'status' => $ev['status'], 'plan_next' => $ev['plan_next'], 'in_roster' => $ev['in_roster'],
            'periods' => array_values($slots),
            'entitlement' => ['hak' => $ent, 'pemakaian' => $used, 'sisa' => $ent - $used],
            'rosters' => array_map(fn($m) => ['id' => (int)$m['id'], 'group' => $m['roster_group'], 'status' => $m['status'],
                                              'start' => $m['period_start'], 'end' => $m['period_end']], $members),
            'flags' => $ev['flags'],
        ];
    }
}
