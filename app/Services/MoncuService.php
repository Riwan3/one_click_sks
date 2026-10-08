<?php

namespace App\Services;

use App\Exceptions\RosterV2Conflict;
use App\Exceptions\RosterV2Forbidden;
use App\Exceptions\RosterV2NotFound;
use App\Repositories\MoncuRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

//
// Business rules MONCU (Monitoring Cuti). Repository = akses data, service = aturan bisnis.
//
// Prinsip (final, dari workbook ROSTER.xlsx sheet MONCU):
//   - MANUAL     : AWAL (tanggal aktual mulai cuti), TAHUNAN, IZIN RESMI.
//   - OTOMATIS   : AKHIR, INDUKSI, PLAN  (tidak pernah diterima dari input).
//   - AKHIR   = AWAL + 13 + TAHUNAN + IZIN RESMI, +1 hari bila AWAL jatuh pada hari Rabu.
//   - INDUKSI = AKHIR + 1
//   - PLAN    = INDUKSI + interval (default 70 hari; setara Excel AKHIR + 71)
//   - PLAN hanya acuan monitoring, BUKAN pemicu cuti dan BUKAN otomatis menjadi AWAL periode berikutnya.
//   - Roster V2 mengikuti cuti AKTUAL saja: AWAL..AKHIR -> CT, tanggal INDUKSI -> ID.
//     AWAL kosong -> tidak ada CT; INDUKSI kosong -> tidak ada ID.

class MoncuService {
    private $repo;
    private $cfg;

    public function __construct(MoncuRepository $repo, ?array $config = null) {
        $this->repo = $repo;
        $this->cfg  = $config ?? config('moncu');
    }

    // ---------------------------------------------------------- Authorization
    // Terpisah secara fungsi dari Roster V2 agar bisa berbeda di masa depan; saat ini disamakan:
    //   LIHAT  = yang boleh melihat Roster V2;  KELOLA = Admin, Admin Produksi, Manpower (User::canManageRosterV2).

    public static function canView()   { return (bool) Auth::user()?->canViewRosterV2(); }
    public static function canManage() { return (bool) Auth::user()?->canManageRosterV2(); }

    private function requireView() {
        if (!self::canView()) throw new RosterV2Forbidden('Tidak berwenang melihat MONCU.');
    }
    private function requireManage() {
        if (!self::canManage()) throw new RosterV2Forbidden('Anda hanya memiliki akses lihat pada MONCU.');
    }
    private function actor() {
        return substr((string) Auth::user()?->username, 0, 50);
    }

    // ------------------------------------------------------------ Date helpers

    private function parseDate($s) {
        $s = trim((string)$s);
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $s);
        return ($d && $d->format('Y-m-d') === $s) ? $d : null;
    }

    private function fmt(DateTimeImmutable $d) { return $d->format('Y-m-d'); }

    // ------------------------------------------------- Perhitungan (murni, tanpa DB)

    /**
     * AKHIR = AWAL + base + TAHUNAN + IZIN RESMI (+ extra bila hari AWAL = Rabu).
     * Excel: =IF(TEXT(AWAL,"dddd")="Rabu", AWAL+13+TAHUNAN+1+IZIN, AWAL+13+TAHUNAN+IZIN)
     */
    public function calculateEndDate($awal, $tahunan, $izinResmi) {
        $d = $this->parseDate($awal);
        if (!$d) return null;
        $days = (int)$this->cfg["base_days"] + (int)$tahunan + (int)$izinResmi;
        if ((int)$d->format('N') === (int)$this->cfg['extra_day_when_weekday']) {
            $days += (int)$this->cfg['extra_days'];
        }
        return $this->fmt($d->modify("+$days days"));
    }

    /** INDUKSI = AKHIR + 1 (Excel: =AKHIR+1). NULL bila AKHIR NULL. */
    public function calculateInductionDate($akhir) {
        $d = $this->parseDate($akhir);
        return $d ? $this->fmt($d->modify('+1 day')) : null;
    }

    /**
     * Cek hari INDUKSI. Return null bila sah / tanggal kosong; bila tidak sah:
     * ['hari' => 'Rabu', 'boleh' => 'Selasa, Kamis, Minggu', 'message' => '...'].
     */
    public function inductionWeekdayIssue($induksi) {
        $d = $induksi ? $this->parseDate($induksi) : null;
        if (!$d) return null;
        $allowed = $this->cfg['induction_weekdays'] ?? [2, 4, 7];
        $n = (int)$d->format('N');
        if (in_array($n, $allowed, true)) return null;
        $names = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $boleh = implode(', ', array_map(function ($x) use ($names) { return $names[$x]; }, $allowed));
        return ['hari' => $names[$n], 'boleh' => $boleh,
                'message' => 'INDUKSI jatuh pada hari ' . $names[$n] . '; induksi hanya ' . $boleh . '. Revisi AWAL / TAHUNAN / IZIN RESMI agar sesuai.'];
    }

    /** PLAN = INDUKSI + interval hari (Excel: AKHIR+71 = INDUKSI+70). NULL bila INDUKSI NULL. */
    public function calculatePlanDate($induksi, $intervalDays) {
        $d = $this->parseDate($induksi);
        return $d ? $this->fmt($d->modify('+' . (int)$intervalDays . ' days')) : null;
    }

    /** Ketiga field hitung sekaligus. AWAL kosong -> semuanya NULL. */
    public function calculatePeriod($awal, $tahunan, $izinResmi, $intervalDays) {
        $akhir = $this->calculateEndDate($awal, $tahunan, $izinResmi);
        $induksi = $this->calculateInductionDate($akhir);
        return ['akhir' => $akhir, 'induksi' => $induksi, 'plan' => $this->calculatePlanDate($induksi, $intervalDays)];
    }

    /** Interval PLAN: override karyawan > aturan jabatan (config) > default (config). */
    public function getIntervalByEmployee(array $emp) {
        if (!empty($emp['plan_interval_days']) && (int)$emp['plan_interval_days'] > 0) {
            return (int)$emp['plan_interval_days'];
        }
        $jab = strtoupper(trim(($emp['jabatan_roster'] ?? '') . ' ' . ($emp['jabatan_ss6'] ?? '')));
        foreach ($this->cfg['interval_rules'] as $rule) {
            if ($jab !== '' && strpos($jab, strtoupper($rule['jabatan_contains'])) !== false) {
                return (int)$rule['days'];
            }
        }
        return (int)$this->cfg['default_interval_days'];
    }

    /** Hak cuti tahunan (Excel: IF(TODAY()-HIRING>365, 12, 0)). $asOf default hari ini. */
    public function calculateAnnualEntitlement($hiringDate, $asOf = null) {
        $h = $this->parseDate($hiringDate);
        $a = $asOf ? $this->parseDate($asOf) : new DateTimeImmutable('today');
        if (!$h || !$a) return 0;
        $tenure = (int)$h->diff($a)->format('%r%a');
        return $tenure > (int)$this->cfg['entitlement_min_tenure_days'] ? (int)$this->cfg['annual_entitlement_days'] : 0;
    }

    // ------------------------------------------------- Pemakaian & saldo hak

    /** Pemakaian TAHUNAN aktual (hanya periode ber-AWAL) pada satu tahun. */
    public function calculateAnnualUsage($nrp, $year) {
        return $this->repo->getAnnualUsage($nrp, (int)$year);
    }

    /** Saldo = hak - pemakaian aktual. Bisa negatif (melebihi hak) -> dipantau, tidak diblokir. */
    public function calculateAnnualBalance($nrp, $year, $asOf = null) {
        $emp = $this->repo->findByEmployee($nrp);
        if (!$emp) throw new RosterV2NotFound('Karyawan belum terdaftar di MONCU.');
        $ent  = $this->calculateAnnualEntitlement($emp['hiring_date'], $asOf);
        $used = $this->calculateAnnualUsage($nrp, $year);
        return ['entitlement' => $ent, 'used' => $used, 'balance' => $ent - $used];
    }

    // ---------------------------------------------------------- Validasi masukan

    /**
     * Validasi & normalisasi masukan MANUAL periode. Return [awal, tahunan, izin] (NULL/NULL/NULL bila AWAL kosong).
     * @throws InvalidArgumentException
     */
    public function validatePeriod($year, $periodNo, array $emp, array $in) {
        $year = (int)$year; $periodNo = (int)$periodNo;
        if ($year < 2000 || $year > 2100) throw new InvalidArgumentException('Tahun tidak valid.');
        if ($periodNo < 1 || $periodNo > (int)$this->cfg['periods_per_year']) {
            throw new InvalidArgumentException('Periode harus 1 sampai ' . (int)$this->cfg['periods_per_year'] . '.');
        }

        $awalRaw = trim((string)($in['awal'] ?? ''));
        $tRaw    = trim((string)($in['tahunan'] ?? ''));
        $iRaw    = trim((string)($in['izin_resmi'] ?? ''));

        if ($awalRaw === '') {
            if ($tRaw !== '' || $iRaw !== '') {
                throw new InvalidArgumentException('TAHUNAN / IZIN RESMI tidak boleh diisi tanpa AWAL. Kosongkan keduanya bila cuti belum berjalan.');
            }
            return [null, null, null];
        }

        $awal = $this->parseDate($awalRaw);
        if (!$awal) throw new InvalidArgumentException('Format AWAL harus YYYY-MM-DD dan tanggal yang nyata.');
        $hire = $this->parseDate($emp['hiring_date']);
        if ($hire && $awal < $hire) throw new InvalidArgumentException('AWAL tidak boleh sebelum Hiring Date (' . $emp['hiring_date'] . ').');

        // TAHUNAN boleh negatif: pada workbook Excel 37 dari 269 sel TAHUNAN bernilai negatif (-1..-11) untuk
        // memperpendek cuti. Batas bawah = -base_days agar AKHIR tidak jatuh sebelum AWAL.
        $tahunan = $this->intField($tRaw, 'TAHUNAN', (int)$this->cfg['max_tahunan'], -(int)$this->cfg['base_days']);
        $izin    = $this->intField($iRaw, 'IZIN RESMI', (int)$this->cfg['max_izin_resmi'], 0);
        return [$this->fmt($awal), $tahunan, $izin];
    }

    private function intField($raw, $label, $max, $min = 0) {
        if ($raw === '') return 0; // kosong = 0 hari (rumus Excel memperlakukan sel kosong sebagai 0)
        if (!preg_match($min < 0 ? '/^-?\d+$/' : '/^\d+$/', $raw)) {
            throw new InvalidArgumentException("$label harus bilangan bulat" . ($min < 0 ? '.' : ' >= 0.'));
        }
        $v = (int)$raw;
        if ($v > $max) throw new InvalidArgumentException("$label maksimal $max hari.");
        if ($v < $min) throw new InvalidArgumentException("$label minimal $min hari.");
        return $v;
    }

    private function loadEmployee($nrp, $requireActive = true) {
        $nrp = trim((string)$nrp);
        $emp = $nrp === '' ? null : $this->repo->findByEmployee($nrp);
        if (!$emp) throw new RosterV2NotFound('Karyawan belum terdaftar di MONCU.');
        if ($requireActive && !(int)$emp['is_active']) throw new RosterV2Conflict('Karyawan ini tidak aktif di MONCU.');
        return $emp;
    }

    // ------------------------------------------------------------ Karyawan MONCU

    /** Tambah / ubah karyawan MONCU (hiring date, grup, override interval). */
    public function saveEmployee(array $in) {
        $this->requireManage();
        $nrp = trim((string)($in['nrp'] ?? ''));
        if ($nrp === '' || strlen($nrp) > 20) throw new InvalidArgumentException('NRP tidak valid.');
        if (!$this->repo->findEmployeeMaster($nrp)) throw new InvalidArgumentException('NRP tidak ditemukan di data karyawan.');

        $hire = $this->parseDate($in['hiring_date'] ?? '');
        if (!$hire) throw new InvalidArgumentException('Hiring Date wajib diisi (YYYY-MM-DD).');
        if ($hire > new DateTimeImmutable('today')) throw new InvalidArgumentException('Hiring Date tidak boleh di masa depan.');

        $earliest = $this->repo->getEarliestAwal($nrp);
        if ($earliest !== null && $this->fmt($hire) > $earliest) {
            throw new InvalidArgumentException("Hiring Date tidak boleh setelah AWAL cuti yang sudah tercatat ($earliest).");
        }

        $group = mb_substr(trim((string)($in['group_name'] ?? '')), 0, 100);
        $intRaw = trim((string)($in['plan_interval_days'] ?? ''));
        if ($intRaw !== '' && (!preg_match('/^\d+$/', $intRaw) || (int)$intRaw < 1 || (int)$intRaw > 365)) {
            throw new InvalidArgumentException('Interval PLAN harus 1-365 hari (kosongkan untuk memakai aturan jabatan).');
        }
        $data = [
            'nrp' => $nrp, 'hiring_date' => $this->fmt($hire), 'group_name' => $group !== '' ? $group : null,
            'plan_interval_days' => $intRaw !== '' ? (int)$intRaw : null,
            'notes' => mb_substr(trim((string)($in['notes'] ?? '')), 0, 255) ?: null,
            'is_active' => isset($in['is_active']) ? (int)(bool)$in['is_active'] : 1,
            'actor' => $this->actor(),
        ];

        return $this->repo->transaction(function () use ($data) {
            $old = $this->repo->findByEmployee($data['nrp']);
            if ($old) {
                $this->repo->updateEmployee($data['nrp'], $data);
                $note = 'hiring_date ' . $old['hiring_date'] . ' -> ' . $data['hiring_date']
                    . '; plan_interval ' . ($old['plan_interval_days'] ?? '-') . ' -> ' . ($data['plan_interval_days'] ?? '-')
                    . '; grup ' . ($old['group_name'] ?? '-') . ' -> ' . ($data['group_name'] ?? '-');
                $action = 'employee_upd';
                $this->recalcAllPlans($data['nrp']); // interval bisa berubah -> PLAN ikut
            } else {
                $this->repo->createEmployee($data);
                $note = 'hiring ' . $data['hiring_date'];
                $action = 'employee_add';
            }
            $this->repo->addAudit(['nrp' => $data['nrp'], 'action' => $action, 'note' => mb_substr($note, 0, 255), 'actor' => $data['actor']]);
            return $data['nrp'];
        });
    }

    /** Hitung ulang PLAN semua periode karyawan (dipanggil saat interval/jabatan berubah). AWAL/TAHUNAN/IZIN tidak disentuh. */
    private function recalcAllPlans($nrp) {
        $emp = $this->loadEmployee($nrp, false);
        $interval = $this->getIntervalByEmployee($emp);
        foreach ($this->repo->findByEmployeeAll($nrp) as $p) {
            if ($p['awal'] === null) continue;
            $c = $this->calculatePeriod($p['awal'], $p['tahunan'], $p['izin_resmi'], $interval);
            if ($c['plan'] !== $p['plan']) {
                $this->repo->updatePeriod($p['id'], [
                    'awal' => $p['awal'], 'tahunan' => $p['tahunan'], 'izin_resmi' => $p['izin_resmi'],
                    'akhir' => $c['akhir'], 'induksi' => $c['induksi'], 'plan' => $c['plan'], 'actor' => $this->actor(),
                ]);
            }
        }
    }

    // --------------------------------------------------------------- Periode

    /** Hitung tanpa menyimpan (untuk pratinjau field readonly di UI). */
    public function previewPeriod($nrp, $year, $periodNo, array $in) {
        $this->requireView();
        $emp = $this->loadEmployee($nrp, false);
        [$awal, $t, $i] = $this->validatePeriod($year, $periodNo, $emp, $in);
        $calc = $this->calculatePeriod($awal, $t, $i, $this->getIntervalByEmployee($emp));
        return ['awal' => $awal, 'tahunan' => $t, 'izin_resmi' => $i] + $calc
            + ['warnings' => $this->periodWarnings($emp, (int)$year, (int)$periodNo, $awal, $t, $calc['induksi'])];
    }

    /**
     * Simpan input manual satu periode -> hitung ulang AKHIR/INDUKSI/PLAN -> audit -> sinkron Roster V2
     * dalam SATU transaksi (gagal di mana pun = rollback total).
     */
    public function savePeriod($nrp, $year, $periodNo, array $in) {
        $this->requireManage();
        $emp = $this->loadEmployee($nrp);
        $year = (int)$year; $periodNo = (int)$periodNo;
        [$awal, $tahunan, $izin] = $this->validatePeriod($year, $periodNo, $emp, $in);
        $calc = $this->calculatePeriod($awal, $tahunan, $izin, $this->getIntervalByEmployee($emp));
        $actor = $this->actor();

        return $this->repo->transaction(function () use ($emp, $year, $periodNo, $awal, $tahunan, $izin, $calc, $actor) {
            $old = $this->repo->findByPeriod($emp['nrp'], $year, $periodNo);

            if ($awal !== null) {
                $clash = $this->repo->findOverlap($emp['nrp'], $awal, $calc['akhir'], $old ? $old['id'] : 0);
                if ($clash) {
                    throw new InvalidArgumentException(
                        "Rentang cuti {$awal} s/d {$calc['akhir']} beririsan dengan periode {$clash['period_no']} tahun {$clash['cycle_year']} ({$clash['awal']} s/d {$clash['akhir']})."
                    );
                }
            }

            $row = ['nrp' => $emp['nrp'], 'cycle_year' => $year, 'period_no' => $periodNo,
                    'awal' => $awal, 'tahunan' => $tahunan, 'izin_resmi' => $izin,
                    'akhir' => $calc['akhir'], 'induksi' => $calc['induksi'], 'plan' => $calc['plan'], 'actor' => $actor];
            if ($old) { $this->repo->updatePeriod($old['id'], $row); $periodId = (int)$old['id']; }
            else      { $periodId = $this->repo->createPeriod($row); }

            $changed = !$old || $old['awal'] !== $awal
                || ($old['tahunan'] === null ? null : (int)$old['tahunan']) !== $tahunan
                || ($old['izin_resmi'] === null ? null : (int)$old['izin_resmi']) !== $izin;
            if ($changed) {
                $this->repo->addAudit([
                    'nrp' => $emp['nrp'], 'cycle_year' => $year, 'period_no' => $periodNo,
                    'action' => $old ? 'period_upd' : 'period_add',
                    'old_awal' => $old['awal'] ?? null, 'new_awal' => $awal,
                    'old_tahunan' => $old['tahunan'] ?? null, 'new_tahunan' => $tahunan,
                    'old_izin_resmi' => $old['izin_resmi'] ?? null, 'new_izin_resmi' => $izin,
                    'new_akhir' => $calc['akhir'], 'new_induksi' => $calc['induksi'], 'new_plan' => $calc['plan'],
                    'actor' => $actor,
                ]);
            }

            $sync = $this->syncEmployeeInternal($emp['nrp']);
            $warnings = $this->periodWarnings($emp, $year, $periodNo, $awal, $tahunan, $calc['induksi']);
            foreach ($sync['not_in_roster'] as $nr) {
                $warnings[] = 'Karyawan belum ada di roster "' . $nr['name'] . '" (' . $nr['start'] . ' s/d ' . $nr['end'] . '); cuti tidak diterapkan ke roster tersebut (MONCU tidak menambah karyawan ke roster).';
            }
            if ($sync['kept'] > 0) {
                $warnings[] = $sync['kept'] . ' sel roster hasil MONCU dipertahankan karena sudah punya alokasi unit.';
            }
            return [
                'period_id' => $periodId, 'awal' => $awal, 'tahunan' => $tahunan, 'izin_resmi' => $izin,
                'akhir' => $calc['akhir'], 'induksi' => $calc['induksi'], 'plan' => $calc['plan'],
                'sync' => $sync, 'warnings' => $warnings,
            ];
        });
    }

    /**
     * Hapus/batalkan satu periode cuti (mis. batal cuti). Urutan: kosongkan nilai -> sinkron (CT/ID di roster DRAFT dikembalikan
     * ke shift semula) -> hapus baris periode. Bila ada roster terkunci (archived) yang sudah memuat CT/ID dari
     * periode ini, penghapusan DITOLAK (409) dan seluruh perubahan dibatalkan.
     */
    public function deletePeriod($nrp, $year, $periodNo) {
        $this->requireManage();
        $emp = $this->loadEmployee($nrp, false);
        $year = (int)$year; $periodNo = (int)$periodNo;
        return $this->repo->transaction(function () use ($emp, $year, $periodNo) {
            $old = $this->repo->findByPeriod($emp['nrp'], $year, $periodNo);
            if (!$old) throw new RosterV2NotFound('Periode ' . $periodNo . ' tahun ' . $year . ' belum diisi.');
            $actor = $this->actor();
            if ($this->repo->countLockedLinksOfPeriod($old['id']) > 0) {
                throw new RosterV2Conflict('Cuti periode ini sudah tercatat di roster yang diarsipkan (terkunci), jadi tidak dapat dihapus.');
            }
            $this->repo->updatePeriod($old['id'], ['awal' => null, 'tahunan' => null, 'izin_resmi' => null,
                'akhir' => null, 'induksi' => null, 'plan' => null, 'actor' => $actor]);
            $sync = $this->syncEmployeeInternal($emp['nrp']);
            if ((int)$sync['locked_rosters'] > 0) {
                throw new RosterV2Conflict('Cuti periode ini sudah tercatat di roster yang diarsipkan (terkunci), jadi tidak dapat dihapus.');
            }
            $this->repo->deletePeriod($old['id']);
            $this->repo->addAudit([
                'nrp' => $emp['nrp'], 'cycle_year' => $year, 'period_no' => $periodNo, 'action' => 'period_del',
                'old_awal' => $old['awal'], 'old_tahunan' => $old['tahunan'], 'old_izin_resmi' => $old['izin_resmi'],
                'note' => 'periode dihapus/dibatalkan', 'actor' => $actor,
            ]);
            return ['sync' => $sync, 'old_awal' => $old['awal']];
        });
    }

    /** Peringatan non-blokir: AWAL di luar bulan periode (label Excel), pemakaian melebihi hak. */
    private function periodWarnings(array $emp, $year, $periodNo, $awal, $tahunan, $induksi = null) {
        $w = [];
        if (($issue = $this->inductionWeekdayIssue($induksi)) !== null) $w[] = $issue['message'];
        if ($awal !== null) {
            $m = (int)substr($awal, 5, 2);
            $months = $this->cfg['period_months'][$periodNo] ?? [];
            if ($months && !in_array($m, $months, true)) {
                $w[] = 'AWAL berada di luar bulan yang dilabeli periode ' . $periodNo . ' (' . implode('/', $months) . ').';
            }
            if ((int)substr($awal, 0, 4) !== (int)$year) {
                $w[] = 'Tahun AWAL berbeda dengan tahun siklus ' . $year . '.';
            }
        }
        $ent = $this->calculateAnnualEntitlement($emp['hiring_date']);
        $others = 0;
        foreach ($this->repo->findByYear($emp['nrp'], $year) as $p) {
            if ((int)$p['period_no'] !== (int)$periodNo && $p['awal'] !== null) $others += max(0, (int)$p['tahunan']);
        }
        if ($awal !== null && $others + max(0, (int)$tahunan) > $ent) {
            $w[] = 'Total TAHUNAN tahun ' . $year . ' (' . ($others + max(0, (int)$tahunan)) . ') melebihi hak (' . $ent . ').';
        }
        if ($awal !== null && (int)$tahunan < 0) {
            $w[] = 'TAHUNAN negatif memperpendek cuti (tidak mengurangi hak tahunan).';
        }
        return $w;
    }

    // ----------------------------------------------------------- Tampilan

    /**
     * Data halaman MONCU: karyawan + 4 periode + hak/pemakaian/saldo untuk satu tahun.
     * PLAN periode kosong (tanpa AWAL) ditampilkan sebagai acuan = PLAN periode sebelumnya yang terisi (plan_ref);
     * nilai itu TIDAK disimpan dan TIDAK memicu CT.
     */
    public function getOverview($year, array $filters, $page = 1, $perPage = 50) {
        $this->requireView();
        $year = (int)$year; $page = max(1, (int)$page);
        $f = [];
        if (!empty($filters['q'])) $f['q'] = mb_substr(trim($filters['q']), 0, 50);
        if (!empty($filters['group'])) $f['group'] = mb_substr(trim($filters['group']), 0, 100);
        if (!empty($filters['section'])) $f['section'] = mb_substr(trim($filters['section']), 0, 100);
        $f['active'] = 1;

        $total = $this->repo->countEmployees($f);
        $emps = $this->repo->listEmployees($f, $perPage, ($page - 1) * $perPage);
        $periods = $this->repo->findByYearForMany(array_column($emps, 'nrp'), $year);
        $rosterMap = $this->repo->rosterSectionsForNrps(array_column($emps, 'nrp'));
        $per = (int)$this->cfg['periods_per_year'];

        $rows = [];
        foreach ($emps as $e) {
            $byYear = $periods[$e['nrp']] ?? [];
            $ent = $this->calculateAnnualEntitlement($e['hiring_date']);
            $used = 0; $view = []; $lastPlan = null;
            // PLAN terakhir dari akhir tahun sebelumnya, sebagai acuan periode 1.
            for ($n = $per; $n >= 1; $n--) {
                if (!empty($byYear[$year - 1][$n]['plan'])) { $lastPlan = $byYear[$year - 1][$n]['plan']; break; }
            }
            for ($n = 1; $n <= $per; $n++) {
                $p = $byYear[$year][$n] ?? null;
                $hasAwal = $p && $p['awal'] !== null;
                if ($hasAwal) $used += max(0, (int)$p['tahunan']);
                $view[$n] = [
                    'exists' => (bool)$p,
                    'awal' => $p['awal'] ?? null, 'akhir' => $p['akhir'] ?? null,
                    'tahunan' => $p['tahunan'] ?? null, 'izin_resmi' => $p['izin_resmi'] ?? null,
                    'induksi' => $p['induksi'] ?? null, 'plan' => $p['plan'] ?? null,
                    'induksi_issue' => ($ii = $this->inductionWeekdayIssue($p['induksi'] ?? null)) ? $ii['message'] : null,
                    'induksi_hari' => $ii ? $ii['hari'] : null,
                    'plan_ref' => $hasAwal ? null : $lastPlan,   // acuan saja
                ];
                if ($p && $p['plan'] !== null) $lastPlan = $p['plan'];
            }
            $rows[] = [
                'nrp' => $e['nrp'], 'name' => $e['employee_name'], 'jabatan' => $e['jabatan_ss6'] ?: $e['jabatan_roster'],
                'group_name' => $e['group_name'], 'hiring_date' => $e['hiring_date'],
                'rosters' => array_map(function ($m) {
                    $full = substr($m['period_start'], 5) === '01-01' && substr($m['period_end'], 5) === '12-31'
                        && substr($m['period_start'], 0, 4) === substr($m['period_end'], 0, 4);
                    return ['roster_id' => (int)$m['roster_id'], 'section' => $m['section'], 'status' => $m['status'],
                            'label' => $m['roster_group'] . ' ' . ($full ? substr($m['period_start'], 0, 4) : $m['period_start'] . ' s/d ' . $m['period_end'])];
                }, $rosterMap[(string)$e['nrp']] ?? []),
                'interval_days' => $this->getIntervalByEmployee($e), 'interval_override' => $e['plan_interval_days'], 'notes' => $e['notes'],
                'entitlement' => $ent, 'used' => $used, 'balance' => $ent - $used,
                'periods' => $view, 'next_plan' => $lastPlan,
            ];
        }
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage,
                'pages' => max(1, (int)ceil($total / $perPage)), 'groups' => $this->repo->listGroups(), 'sections' => $this->repo->listSectionNames()];
    }

    // ------------------------------------------------- Integrasi Roster V2

    /** Sinkron cuti aktual -> Roster V2 (roster DRAFT saja). Publik: butuh hak kelola. */
    public function syncRosterV2LeaveStatus($nrp = null, $rosterId = null) {
        $this->requireManage();
        if ($nrp === null && $rosterId === null) {
            throw new InvalidArgumentException('Tentukan NRP atau roster yang akan disinkronkan.');
        }
        return $this->repo->transaction(function () use ($nrp, $rosterId) {
            $tot = ['created' => 0, 'changed' => 0, 'reverted' => 0, 'unchanged' => 0, 'kept' => 0, 'locked_rosters' => 0, 'employees' => 0, 'not_in_roster' => []];
            if ($rosterId !== null) {
                $roster = $this->repo->findRoster($rosterId);
                if (!$roster) throw new RosterV2NotFound('Roster tidak ditemukan.');
                if ($roster['status'] === 'archived') throw new RosterV2Conflict('Roster sudah diarsipkan (terkunci); cuti tidak dapat diterapkan.');
                $nrps = $nrp !== null ? [(string)$nrp] : $this->repo->listMoncuMembersOfRoster($rosterId);
            } else {
                $nrps = [(string)$nrp];
            }
            foreach ($nrps as $n) {
                $r = $this->syncEmployeeInternal($n, $rosterId);
                foreach (['created', 'changed', 'reverted', 'unchanged', 'kept', 'locked_rosters'] as $k) $tot[$k] += $r[$k];
                $tot['employees']++;
            }
            if ($rosterId !== null && $nrp === null) {
                // Karyawan MONCU yang punya cuti pada periode roster ini tetapi belum menjadi anggota roster: dilewati & dilaporkan.
                $tot['not_in_roster'] = $this->repo->listMoncuLeaveNotInRoster($rosterId, $roster['period_start'], $roster['period_end']);
            }
            return $tot;
        });
    }

    /**
     * Terapkan cuti aktual satu karyawan ke semua roster DRAFT tempat ia anggota (atau satu roster).
     * Peta tanggal: AWAL..AKHIR -> CT; INDUKSI -> ID (CT menang bila tumpang tindih). AWAL/INDUKSI NULL -> tidak ada.
     * PLAN tidak dibaca sama sekali. Perubahan dicatat di v2_moncu_roster_links agar bisa dikembalikan.
     */
    private function syncEmployeeInternal($nrp, $onlyRosterId = null) {
        $res = ['created' => 0, 'changed' => 0, 'reverted' => 0, 'unchanged' => 0, 'kept' => 0, 'locked_rosters' => 0, 'not_in_roster' => []];
        $emp = $this->repo->findByEmployee($nrp);
        if (!$emp) return $res;

        $ct = $this->repo->findShiftByCode($this->cfg['leave_shift_code']);
        $id = $this->repo->findShiftByCode($this->cfg['induction_shift_code']);
        if (!$ct || !$id) throw new RosterV2Conflict('Kode shift CT / ID belum ada di master shift Roster V2.');
        $shiftOf = ['CT' => $ct, 'ID' => $id];

        // Peta tanggal aktual -> [kind, period_id]
        $desired = []; $minD = null; $maxD = null;
        foreach ($this->repo->findByEmployeeAll($nrp) as $p) {
            if ($p['awal'] === null || $p['akhir'] === null) continue; // AWAL kosong -> tidak ada CT
            $d = $this->parseDate($p['awal']); $end = $this->parseDate($p['akhir']);
            for ($x = $d; $x <= $end; $x = $x->modify('+1 day')) {
                $desired[$this->fmt($x)] = ['CT', (int)$p['id']];
            }
            if ($p['induksi'] !== null && !isset($desired[$p['induksi']])) {
                $desired[$p['induksi']] = ['ID', (int)$p['id']];   // INDUKSI kosong -> tidak ada ID
            }
        }
        if ($desired) { $ks = array_keys($desired); sort($ks); $minD = $ks[0]; $maxD = end($ks); }

        // Roster terkunci (archived) yang beririsan: hanya dilaporkan, tidak disentuh.
        if ($minD !== null) $res['locked_rosters'] = $this->repo->countLockedMemberRosters($nrp, $minD, $maxD);

        $actor = $this->actor();
        $memberRosters = $this->repo->listMemberRosters($nrp, 'active', $onlyRosterId);

        // Roster draft yang beririsan dengan cuti tetapi karyawan BUKAN anggota: dilewati, hanya dilaporkan.
        if ($minD !== null && $onlyRosterId === null) {
            $memberIds = array_map('intval', array_column($memberRosters, 'id'));
            foreach ($this->repo->listDraftRostersOverlapping($minD, $maxD) as $dr) {
                if (!in_array((int)$dr['id'], $memberIds, true)) {
                    $res['not_in_roster'][] = ['id' => (int)$dr['id'], 'name' => $dr['roster_group'], 'start' => $dr['period_start'], 'end' => $dr['period_end']];
                }
            }
        }

        foreach ($memberRosters as $roster) {
            $links = [];
            foreach ($this->repo->listLinks($roster['id'], $nrp) as $l) { $links[$l['d_date']] = $l; }

            foreach ($desired as $date => [$kind, $periodId]) {
                if ($date < $roster['period_start'] || $date > $roster['period_end']) continue;
                $want = $shiftOf[$kind];
                $cell = $this->repo->findDetail($roster['id'], $nrp, $date);
                $link = $links[$date] ?? null;

                if (!$cell) {
                    $detailId = $this->repo->insertDetail([
                        'roster_id' => $roster['id'], 'nrp' => $nrp, 'employee_name' => $emp['employee_name'],
                        'jabatan' => $emp['jabatan_ss6'] ?: $emp['jabatan_roster'], 'work_date' => $date,
                        'shift_id' => $want['id'], 'notes' => 'MONCU', 'actor' => $actor,
                    ]);
                    $this->repo->addLink(['period_id' => $periodId, 'detail_id' => $detailId, 'work_date' => $date,
                        'kind' => $kind, 'applied_shift_id' => $want['id'], 'prev_shift_id' => null]);
                    $res['created']++;
                } elseif ((int)$cell['shift_id'] === (int)$want['id']) {
                    if ($link) { // sudah sesuai; pastikan metadata link mutakhir
                        if ($link['kind'] !== $kind || (int)$link['period_id'] !== $periodId) {
                            $this->repo->updateLink($link['id'], ['period_id' => $periodId, 'kind' => $kind,
                                'applied_shift_id' => $want['id'], 'prev_shift_id' => $link['prev_shift_id']]);
                        }
                    }
                    $res['unchanged']++;   // tanpa link = sel CT/ID manual, dibiarkan
                } else {
                    // Sel berisi shift lain. prev = shift asli (bukan shift buatan MONCU sebelumnya).
                    $ours = $link && (int)$link['applied_shift_id'] === (int)$cell['shift_id'];
                    $prev = $ours ? $link['prev_shift_id'] : $cell['shift_id'];
                    $this->repo->setDetailShift($cell['id'], $want['id'], $actor);
                    $data = ['period_id' => $periodId, 'kind' => $kind, 'applied_shift_id' => $want['id'], 'prev_shift_id' => $prev];
                    if ($link) $this->repo->updateLink($link['id'], $data);
                    else $this->repo->addLink($data + ['detail_id' => $cell['id'], 'work_date' => $date]);
                    $res['changed']++;
                }
                unset($links[$date]);
            }

            // Sisa link = tanggal yang tidak lagi cuti/induksi -> kembalikan.
            foreach ($links as $date => $l) {
                $cell = $this->repo->findDetailById($l['detail_id']);
                if ($cell && (int)$cell['shift_id'] === (int)$l['applied_shift_id']) {
                    if ($l['prev_shift_id'] === null) {
                        // Sel buatan MONCU. Bila admin sudah menambahkan alokasi unit, sel TIDAK dihapus (alokasi dilindungi).
                        if ($this->repo->countAllocations($cell['id']) > 0) { $res['kept']++; }
                        else { $this->repo->deleteDetailById($cell['id']); $res['reverted']++; }
                    } else {
                        $this->repo->setDetailShift($cell['id'], $l['prev_shift_id'], $actor);   // alokasi tidak disentuh
                        $res['reverted']++;
                    }
                }
                $this->repo->deleteLink($l['id']);  // bila sel sudah diedit manual: lepas link saja
            }
        }
        return $res;
    }
}
