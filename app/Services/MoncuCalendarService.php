<?php

namespace App\Services;

use App\Exceptions\RosterV2Forbidden;
use App\Repositories\MoncuCalendarRepository;
use DateTimeImmutable;
use InvalidArgumentException;

//
// MONCU Calendar (Step 7) - visualisasi kalender cuti READ-ONLY dari tanggal aktual MONCU.
// Tidak ada tulis (tanpa INSERT/UPDATE/DELETE), tidak ada sinkron roster, tidak membuat CT/ID, tidak ada tabel baru.
//
// ------------------------------------------------------------------ Definisi event (dari v2_moncu_periods)
//   CT   : setiap tanggal AWAL..AKHIR periode ber-AWAL                    (status "CT / Cuti")
//   ID   : tanggal INDUKSI (= AKHIR + 1)                                    (status "ID / Induksi")
//   PLAN : tanggal PLAN - HANYA informasi, bukan CT, bukan ID, bukan cuti aktual
//   Bila satu karyawan bentrok pada satu tanggal (CT dan ID sekaligus) INDUKSI menang; data TIDAK diubah,
//   hanya diberi peringatan "Data MONCU memiliki periode yang beririsan."
//
// ------------------------------------------------------------------ Rentang tanggal
//   Grid bulan menampilkan minggu penuh (Senin..Minggu): dari hari Senin pada/ sebelum tanggal 1 sampai hari Minggu
//   pada/setelah tanggal terakhir (28-42 hari). SATU query mengambil semua periode yang BERIRISAN dengan rentang grid:
//       AWAL <= grid_end  DAN  COALESCE(INDUKSI, AKHIR) >= grid_start   (atau PLAN di dalam rentang)
//   sehingga cuti lintas bulan / lintas tahun tetap terbaca (filter memakai tanggal aktual, bukan cycle_year), dan sel
//   bulan tetangga pada grid juga benar. Ringkasan hanya menghitung tanggal pada bulan yang dipilih.

class MoncuCalendarService {
    const PER_PAGE_DEFAULT = 25;
    const PER_PAGE_MAX = 100;
    const SECTIONS = ['ct', 'id', 'plan'];
    const OVERLAP_WARNING = 'Data MONCU memiliki periode yang beririsan.';

    private $repo;

    public function __construct(MoncuCalendarRepository $repo) {
        $this->repo = $repo;
    }

    public static function canView() { return MoncuService::canView(); }

    private function requireView() {
        if (!self::canView()) throw new RosterV2Forbidden('Tidak berwenang melihat MONCU Calendar.');
    }

    // ------------------------------------------------------------- helper (murni)

    public static function isValidDate($s) {
        if (!is_string($s) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $s);
        return $d && $d->format('Y-m-d') === $s;
    }

    /**
     * Normalisasi filter. Nilai bertipe bukan string (mis. array) ditolak dengan InvalidArgumentException.
     * year/month tidak valid -> InvalidArgumentException (dipetakan 400 oleh API); kosong -> default bulan berjalan.
     */
    public function normalizeFilters(array $in, $today) {
        foreach (['year', 'month', 'group', 'position', 'search'] as $k) {
            if (isset($in[$k]) && !is_string($in[$k]) && !is_int($in[$k])) throw new InvalidArgumentException("Parameter $k tidak valid.");
        }
        $y = trim((string)($in['year'] ?? '')); $m = trim((string)($in['month'] ?? ''));
        if ($y === '') $y = substr($today, 0, 4);
        elseif (!preg_match('/^\d{4}$/', $y) || (int)$y < 2000 || (int)$y > 2100) throw new InvalidArgumentException('Parameter year tidak valid.');
        if ($m === '') $m = (string)(int)substr($today, 5, 2);
        elseif (!preg_match('/^\d{1,2}$/', $m) || (int)$m < 1 || (int)$m > 12) throw new InvalidArgumentException('Parameter month tidak valid.');
        return [
            'year' => (int)$y, 'month' => (int)$m,
            'group' => mb_substr(trim((string)($in['group'] ?? '')), 0, 100),
            'position' => mb_substr(trim((string)($in['position'] ?? '')), 0, 150),
            'search' => mb_substr(trim((string)($in['search'] ?? '')), 0, 50),
        ];
    }

    /** Rentang grid: Senin sebelum/pada tgl 1 s.d. Minggu setelah/pada tgl terakhir. */
    public static function gridRange($year, $month) {
        $first = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $last = $first->modify('last day of this month');
        $start = $first->modify('-' . ((int)$first->format('N') - 1) . ' days');
        $end = $last->modify('+' . (7 - (int)$last->format('N')) . ' days');
        return ['first' => $first->format('Y-m-d'), 'last' => $last->format('Y-m-d'),
                'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')];
    }

    // ---------------------------------------------------------------- tampilan bulan

    public function getFilterOptions() {
        $this->requireView();
        return ['groups' => $this->repo->listGroups(), 'positions' => $this->repo->listPositions()];
    }

    /**
     * @return array ['filters','range','cells'=>[date=>['ct','id','plan']],'summary','warnings','employees_matched','state']
     * Query konstan: periodsOverlapping (1) + countEmployees (1).
     */
    public function getMonth(array $input, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        $f = $this->normalizeFilters($input, $today);
        $r = self::gridRange($f['year'], $f['month']);
        $matched = $this->repo->countEmployees($f);
        $rows = $matched ? $this->repo->periodsOverlapping($f, $r['start'], $r['end']) : [];

        $ctBy = []; $idBy = []; $planN = [];   // date => [nrp => jumlah periode]  / date => jumlah PLAN
        $conflicts = [];
        foreach ($rows as $p) {
            $nrp = $p['nrp'];
            if ($p['awal'] && $p['akhir']) {
                $from = max($p['awal'], $r['start']); $to = min($p['akhir'], $r['end']);
                for ($d = new DateTimeImmutable($from); $d->format('Y-m-d') <= $to; $d = $d->modify('+1 day')) {
                    $k = $d->format('Y-m-d');
                    $ctBy[$k][$nrp] = ($ctBy[$k][$nrp] ?? 0) + 1;
                }
            }
            if ($p['induksi'] && $p['induksi'] >= $r['start'] && $p['induksi'] <= $r['end']) {
                $idBy[$p['induksi']][$nrp] = ($idBy[$p['induksi']][$nrp] ?? 0) + 1;
            }
            if ($p['plan'] && $p['plan'] >= $r['start'] && $p['plan'] <= $r['end']) {
                $planN[$p['plan']] = ($planN[$p['plan']] ?? 0) + 1;   // informasi saja
            }
        }

        // Prioritas: INDUKSI menang atas CUTI pada tanggal & karyawan yang sama. Data tidak diubah, hanya diberi peringatan.
        foreach ($ctBy as $d => $people) {
            foreach ($people as $nrp => $cnt) {
                if ($cnt > 1 || isset($idBy[$d][$nrp])) $conflicts[$nrp] = true;
                if (isset($idBy[$d][$nrp])) unset($ctBy[$d][$nrp]);
            }
        }
        foreach ($idBy as $d => $people) foreach ($people as $nrp => $cnt) if ($cnt > 1) $conflicts[$nrp] = true;

        $cells = []; $sum = ['hari_ct' => 0, 'ct_employee_day' => 0, 'id_employee_day' => 0, 'ct_unik' => 0, 'id_unik' => 0, 'plan' => 0];
        $uniqCt = []; $uniqId = [];
        for ($d = new DateTimeImmutable($r['start']); $d->format('Y-m-d') <= $r['end']; $d = $d->modify('+1 day')) {
            $k = $d->format('Y-m-d');
            $ct = isset($ctBy[$k]) ? count($ctBy[$k]) : 0; $id = isset($idBy[$k]) ? count($idBy[$k]) : 0; $pl = $planN[$k] ?? 0;
            $cells[$k] = ['ct' => $ct, 'id' => $id, 'plan' => $pl];
            if ($k >= $r['first'] && $k <= $r['last']) {     // ringkasan hanya untuk bulan yang dipilih
                if ($ct > 0) $sum['hari_ct']++;
                $sum['ct_employee_day'] += $ct; $sum['id_employee_day'] += $id; $sum['plan'] += $pl;
                foreach (array_keys($ctBy[$k] ?? []) as $n) $uniqCt[$n] = true;
                foreach (array_keys($idBy[$k] ?? []) as $n) $uniqId[$n] = true;
            }
        }
        $sum['ct_unik'] = count($uniqCt); $sum['id_unik'] = count($uniqId);

        $hasEvents = $sum['ct_employee_day'] > 0 || $sum['id_employee_day'] > 0;
        $state = $matched === 0 ? 'no_employees' : ($hasEvents ? 'events' : ($sum['plan'] > 0 ? 'plan_only' : 'empty'));

        return ['filters' => $f, 'today' => $today, 'range' => $r, 'cells' => $cells, 'summary' => $sum,
                'warnings' => $conflicts ? [self::OVERLAP_WARNING] : [], 'overlap_nrp' => array_map('strval', array_slice(array_keys($conflicts), 0, 5)),   // kunci array numerik -> string
                'employees_matched' => $matched, 'state' => $state];
    }

    // ---------------------------------------------------------------- detail satu tanggal

    /**
     * @param string $date Y-m-d (wajib valid)
     * @param string|null $section ct|id|plan|null (null = ketiganya, halaman $page)
     * Query: periodsOnDate (1) + awalByEmployees (1, hanya bila ada PLAN).
     */
    public function getDay($date, array $input, $section = null, $page = 1, $perPage = self::PER_PAGE_DEFAULT, $today = null) {
        $this->requireView();
        $today = $today ?: date('Y-m-d');
        if (!self::isValidDate($date)) throw new InvalidArgumentException('Parameter date harus YYYY-MM-DD dan tanggal yang nyata.');
        if ($section !== null && !in_array($section, self::SECTIONS, true)) throw new InvalidArgumentException('Parameter section tidak valid.');
        $ymd = (int)substr($date, 0, 4);
        if ($ymd < 2000 || $ymd > 2100) throw new InvalidArgumentException('Parameter date di luar rentang.');
        $f = $this->normalizeFilters($input + ['year' => substr($date, 0, 4), 'month' => (string)(int)substr($date, 5, 2)], $today);

        $rows = $this->repo->periodsOnDate($f, $date);
        $ct = []; $id = []; $plan = []; $conflict = false;
        foreach ($rows as $p) {
            $isCt = $p['awal'] && $p['akhir'] && $p['awal'] <= $date && $date <= $p['akhir'];
            $isId = $p['induksi'] === $date;
            $item = [
                'nrp' => $p['nrp'], 'name' => (string)$p['employee_name'], 'jabatan' => (string)$p['jabatan'], 'group' => (string)$p['group_name'],
                'hiring_date' => $p['hiring_date'], 'cycle_year' => (int)$p['cycle_year'], 'period_no' => (int)$p['period_no'],
                'awal' => $p['awal'], 'akhir' => $p['akhir'], 'tahunan' => $p['tahunan'] === null ? null : (int)$p['tahunan'],
                'izin_resmi' => $p['izin_resmi'] === null ? null : (int)$p['izin_resmi'], 'induksi' => $p['induksi'], 'plan' => $p['plan'],
            ];
            if ($isId) { $id[$p['nrp']] = $item + ['status' => 'INDUKSI']; }
            if ($isCt) { if (isset($ct[$p['nrp']])) $conflict = true; else $ct[$p['nrp']] = $item + ['status' => 'CT']; }   // 1 karyawan = 1 baris (sama dengan tampilan bulan)
            if ($p['plan'] === $date) $plan[] = $item + ['status' => 'PLAN'];
        }
        // INDUKSI menang atas CT untuk karyawan yang sama pada tanggal ini
        foreach ($ct as $k => $it) { if (isset($id[$k])) { unset($ct[$k]); $conflict = true; } }

        // PLAN: sertakan AWAL aktual periode berikutnya (informasi; PLAN tidak pernah menjadi CT)
        if ($plan) {
            $awals = $this->repo->awalByEmployees(array_column($plan, 'nrp'));
            foreach ($plan as &$it) {
                $next = null;
                foreach ($awals[$it['nrp']] ?? [] as $a) {
                    if ((int)$a['cycle_year'] > $it['cycle_year'] || ((int)$a['cycle_year'] === $it['cycle_year'] && (int)$a['period_no'] > $it['period_no'])) { $next = $a['awal']; break; }
                }
                $it['next_awal'] = $next;
            }
            unset($it);
        }

        $perPage = max(1, min(self::PER_PAGE_MAX, (int)$perPage)); $page = max(1, (int)$page);
        $pack = function (array $items) use ($page, $perPage) {
            $items = array_values($items);
            usort($items, fn($a, $b) => strcmp($a['name'], $b['name']) ?: strcmp($a['nrp'], $b['nrp']));
            $total = count($items); $pages = max(1, (int)ceil($total / $perPage)); $pg = min($page, $pages);
            return ['total' => $total, 'page' => $pg, 'pages' => $pages, 'per_page' => $perPage, 'items' => array_slice($items, ($pg - 1) * $perPage, $perPage)];
        };
        $empty = ['total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => $perPage, 'items' => []];
        $sets = ['ct' => $ct, 'id' => $id, 'plan' => $plan];
        $out = ['date' => $date, 'today' => $today, 'is_today' => $date === $today, 'warnings' => $conflict ? [self::OVERLAP_WARNING] : []];
        foreach (self::SECTIONS as $s) $out[$s] = ($section === null || $section === $s) ? $pack($sets[$s]) : $empty;
        return $out;
    }
}
