<?php

namespace App\Services;

use App\Exceptions\RosterV2Conflict;
use App\Exceptions\RosterV2Forbidden;
use App\Exceptions\RosterV2NotFound;
use App\Repositories\RosterV2Repository;
use App\Support\RosterV2Rotation;
use App\Support\RosterV2SheetParser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

// app/Services/RosterV2SheetImportService.php (port V1)
//
// Impor dari Google Sheets ke Roster V2 (tab roster) dan MONCU (tab MONCU).
// Alur dua langkah (sama untuk keduanya):
//   1) preview*() : ambil & baca sheet, jalankan SEMUA langkah impor di dalam satu transaksi lalu ROLLBACK,
//                   kembalikan laporan lengkap (apa yang akan ditulis, apa yang dilewati, kesalahan per baris) + token.
//   2) commit*()  : jalankan langkah yang sama dari data yang disimpan di sesi saat preview, lalu COMMIT.
// Hanya menulis ke tabel v2_* (dan hanya membaca employees). Hanya Admin / Admin Produksi (canManage).
//
// Roster : section dibuat bila belum ada; anggota ditambah / dipindah; sel jadwal NRP di sheet DIGANTI (sel lain tidak disentuh);
//          Kode Off dari kolom A/B; pola kerja (Rotasi/Full Siang) anggota yang sudah ada dipertahankan.
// MONCU  : periode lama karyawan yang ada di sheet dihapus lalu diisi ulang dari sheet (AWAL/TAHUNAN/IZIN RESMI);
//          AKHIR/INDUKSI/PLAN dihitung aplikasi; CT/ID ditimpakan ke roster aktif lewat mekanisme sinkron MONCU yang sudah ada.

class RosterV2SheetImportService {
    const TTL = 1800; // detik

    private $pdo;
    private $repo;
    private $rs;
    private $mon;

    public function __construct(RosterV2Repository $repo, RosterV2Service $rs, MoncuService $mon) {
        $this->pdo = DB::connection()->getPdo(); $this->repo = $repo; $this->rs = $rs; $this->mon = $mon;
    }

    private function requireManage() {
        if (!RosterV2Service::canManage()) throw new RosterV2Forbidden('Hanya Admin / Admin Produksi yang boleh mengimpor.');
    }

    private function actor() { return substr((string)(Auth::user()?->username ?? ''), 0, 50); }

    private function saveSlot($kind, $rosterId, $parsed) {
        $token = bin2hex(random_bytes(16));
        session(['rv2_sheet' => ['kind' => $kind, 'token' => $token, 'roster_id' => (int)$rosterId, 'at' => time(), 'parsed' => $parsed]]);
        return $token;
    }

    private function loadSlot($kind, $token, $rosterId = null) {
        $slot = session('rv2_sheet');
        if (!$slot || $slot['kind'] !== $kind || !hash_equals((string)$slot['token'], (string)$token) || ($rosterId !== null && (int)$slot['roster_id'] !== (int)$rosterId)) {
            throw new RosterV2Conflict('Pratinjau impor tidak ditemukan. Baca ulang sheet-nya.');
        }
        if (time() - (int)$slot['at'] > self::TTL) {
            session()->forget('rv2_sheet');
            throw new RosterV2Conflict('Pratinjau impor sudah kedaluwarsa. Baca ulang sheet-nya.');
        }
        return $slot['parsed'];
    }

    // ================================================================ ROSTER

    public function previewRoster($url, $rosterId) {
        $this->requireManage();
        $roster = $this->requireImportableRoster($rosterId);
        $year = (int)substr($roster['period_start'], 0, 4);
        $parsed = RosterV2SheetParser::parseRoster(RosterV2SheetParser::fetchRows($url), $year);
        $report = $this->runRoster($parsed, $roster, false, true);
        $report['token'] = $this->saveSlot('roster', $rosterId, $parsed);
        return $report;
    }

    public function commitRoster($rosterId, $token, $syncMoncu = true) {
        $this->requireManage();
        $parsed = $this->loadSlot('roster', $token, $rosterId);
        $roster = $this->requireImportableRoster($rosterId);
        $report = $this->runRoster($parsed, $roster, true, (bool)$syncMoncu);
        session()->forget('rv2_sheet');
        return $report;
    }

    private function requireImportableRoster($rosterId) {
        $roster = $this->repo->findRoster((int)$rosterId);
        if (!$roster) throw new RosterV2NotFound('Roster tidak ditemukan.');
        if ($roster['status'] === 'archived') throw new RosterV2Conflict('Roster sudah diarsipkan, tidak dapat diimpor.');
        if (substr($roster['period_start'], 0, 4) !== substr($roster['period_end'], 0, 4)) {
            throw new InvalidArgumentException('Impor roster hanya untuk roster yang periodenya berada dalam satu tahun kalender.');
        }
        return $roster;
    }

    /** Jalankan impor roster dalam transaksi. $commit=false -> rollback (pratinjau). */
    private function runRoster(array $parsed, array $roster, $commit, $syncMoncu) {
        $rid = (int)$roster['id'];
        $shiftIds = [];
        foreach ($this->repo->listShifts(true) as $s) $shiftIds[$s['code']] = (int)$s['id'];
        $secIds = [];
        foreach ($this->repo->listSections($rid) as $s) $secIds[$s['name']] = (int)$s['id'];

        $skippedNoEmp = []; $unknown = []; $err = 0; $blank = 0; $outOfPeriod = 0; $written = 0;
        $newMembers = 0; $existing = 0; $moved = 0; $bySec = []; $warnings = $parsed['warnings'];
        // Bulan yang belum ada di sheet (mis. November-Desember): dilanjutkan dari putaran Kode Off (siang-malam, OFF sesuai kode)
        $missing = $parsed['missing_months'] ?? []; $filled = 0; $noKode = [];
        $fillDates = [];
        foreach ($missing as $mn) {
            $n = cal_days_in_month(CAL_GREGORIAN, $mn, (int)$parsed['year']);
            for ($d = 1; $d <= $n; $d++) {
                $dt = sprintf('%04d-%02d-%02d', (int)$parsed['year'], $mn, $d);
                if ($dt >= $roster['period_start'] && $dt <= $roster['period_end']) $fillDates[] = $dt;
            }
        }
        $sync = null;

        $prevSilent = $this->rs->logSilent; $this->rs->logSilent = true;   // satu ringkasan setelah commit, bukan per anggota
        DB::beginTransaction();
        try {
            foreach ($parsed['members'] as $nrp => $m) {
                $emp = $this->repo->findEmployeeByNrp((string)$nrp);
                if (!$emp) { $skippedNoEmp[] = "$nrp {$m['name']} ({$m['sec']})"; continue; }
                if (!isset($secIds[$m['sec']])) $secIds[$m['sec']] = $this->rs->addSection($rid, $m['sec']);
                $secId = $secIds[$m['sec']];
                $mem = $this->repo->findMemberByNrp($rid, (string)$nrp);
                if (!$mem) { $mid = $this->rs->addMember($rid, $secId, (string)$nrp, $m['unit'], '', 'ROTASI'); $newMembers++; }
                else {
                    $mid = (int)$mem['id']; $existing++;
                    if ((int)$mem['section_id'] !== $secId) { $this->rs->moveMember($rid, $mid, $secId); $moved++; }
                }
                $this->rs->setMemberRotation($rid, $mid, $m['kode'], '', '', false, null);   // simpan Kode Off; pola kerja lama dipertahankan
                $bySec[$m['sec']] = ($bySec[$m['sec']] ?? 0) + 1;

                $this->repo->deleteDetailsRange($rid, (string)$nrp, $roster['period_start'], $roster['period_end']);
                $rows = [];
                if ($fillDates) {
                    $pat = RosterV2Rotation::normalizePattern($mem['work_pattern'] ?? '') ?: RosterV2Rotation::PATTERN_ROTASI;
                    if ($m['kode'] === '') { $noKode[] = "$nrp {$m['name']}"; }
                    else foreach ($fillDates as $dt) {
                        if (isset($m['cells'][$dt])) continue;   // sheet sudah punya isi di tanggal itu: dipakai apa adanya
                        $code = RosterV2Rotation::shiftFor($m['kode'], $dt, $pat);
                        if ($code === null || !isset($shiftIds[$code])) continue;
                        $rows[] = ['nrp' => (string)$nrp, 'employee_name' => $emp['name'], 'jabatan' => $emp['jabatan'], 'work_date' => $dt, 'shift_id' => $shiftIds[$code]];
                        $filled++;
                    }
                }
                foreach ($m['cells'] as $date => $code) {
                    if ($date < $roster['period_start'] || $date > $roster['period_end']) { $outOfPeriod++; continue; }
                    if (!isset($shiftIds[$code])) { $unknown[$code] = ($unknown[$code] ?? 0) + 1; continue; }
                    $rows[] = ['nrp' => (string)$nrp, 'employee_name' => $emp['name'], 'jabatan' => $emp['jabatan'], 'work_date' => $date, 'shift_id' => $shiftIds[$code]];
                }
                $this->repo->bulkUpsertDetails($rid, $rows, $this->actor());
                $written += count($rows);
                $err += $m['error']; $blank += $m['blank'];
            }
            if ($syncMoncu && $this->repo->moncuTablesReady()) {
                try { $sync = $this->mon->syncRosterV2LeaveStatus(null, $rid); }
                catch (Throwable $e) { $warnings[] = 'Sinkron cuti MONCU dilewati: ' . $e->getMessage(); }
            }
            if ($commit) DB::commit(); else DB::rollBack();
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->rs->logSilent = $prevSilent;
            throw $e;
        }
        $this->rs->logSilent = $prevSilent;
        if ($commit) {
            $this->rs->logChange($rid, 'import', 'mengimpor spreadsheet: ' . count($parsed['members']) . ' karyawan di sheet (' . $newMembers . ' anggota baru, ' . $existing . ' jadwalnya diganti), ' . $written . ' sel jadwal ditulis'
                . ($filled ? ', ' . $filled . ' sel bulan lanjutan diisi dari Kode Off' : ''));
        }

        $stats = [
            ['Roster tujuan', $roster['name'] . ' (' . $roster['status'] . ')'],
            ['Tahun sheet', $parsed['year']],
            ['Section di sheet', count($parsed['sections'])],
            ['Karyawan di sheet', count($parsed['members'])],
            ['Anggota baru ditambahkan', $newMembers],
            ['Anggota sudah ada (jadwalnya diganti)', $existing],
            ['Anggota berpindah section', $moved],
            ['Sel jadwal ditulis', $written],
        ];
        if ($fillDates) {
            $bn = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $stats[] = ['Bulan belum ada di sheet', implode(', ', array_map(function ($x) use ($bn) { return $bn[$x]; }, $missing))];
            $stats[] = ['Sel bulan itu diisi otomatis dari Kode Off (siang-malam, OFF sesuai kode)', $filled];
        }
        if ($sync !== null) $stats[] = ['Cuti MONCU diterapkan (sel)', (int)$sync['created'] + (int)$sync['changed']];
        $lists = [];
        if ($skippedNoEmp) $lists[] = ['title' => 'Dilewati: NRP tidak ada di data karyawan (' . count($skippedNoEmp) . ')', 'tone' => 'danger', 'items' => $skippedNoEmp];
        if ($noKode) $lists[] = ['title' => 'Bulan yang belum ada di sheet tidak bisa diisi: Kode Off kosong/tidak valid (' . count($noKode) . ')', 'tone' => 'warning', 'items' => $noKode];
        $skips = [];
        if ($err) $skips[] = "$err sel berisi #ERROR! (dilewati)";
        if ($blank) $skips[] = "$blank sel kosong (dilewati)";
        if ($outOfPeriod) $skips[] = "$outOfPeriod sel di luar periode roster (dilewati)";
        foreach ($unknown as $k => $n) $skips[] = "$n sel berkode \"$k\" (tidak ada di master shift, dilewati)";
        if ($skips) $lists[] = ['title' => 'Sel yang tidak diimpor', 'tone' => 'warning', 'items' => $skips];
        $secItems = []; foreach ($bySec as $n => $c) $secItems[] = "$n: $c orang";
        if ($secItems) $lists[] = ['title' => 'Anggota per section', 'tone' => 'info', 'items' => $secItems];
        return ['kind' => 'roster', 'committed' => (bool)$commit, 'stats' => $stats, 'lists' => $lists, 'warnings' => $warnings];
    }

    // ================================================================ MONCU

    /** $rosterId > 0 = roster tujuan (grup MONCU mengikuti roster itu; karyawan yang belum anggota dilaporkan); 0/null = tanpa roster tertentu. */
    public function previewMoncu($url, $rosterId = 0) {
        $this->requireManage();
        $this->requireMoncuTables();
        $roster = $this->resolveTargetRoster($rosterId);
        $parsed = RosterV2SheetParser::parseMoncu(RosterV2SheetParser::fetchRows($url));
        $report = $this->runMoncu($parsed, false, $roster);
        $report['token'] = $this->saveSlot('moncu', $roster ? $roster['id'] : 0, $parsed);
        return $report;
    }

    private function resolveTargetRoster($rosterId) {
        if (!$rosterId) return null;
        $r = $this->repo->findRoster((int)$rosterId);
        if (!$r) throw new RosterV2NotFound('Roster tujuan tidak ditemukan.');
        if ($r['status'] === 'archived') throw new RosterV2Conflict('Roster tujuan sudah diarsipkan.');
        return $r;
    }

    public function commitMoncu($token) {
        $this->requireManage();
        $this->requireMoncuTables();
        $slot = session('rv2_sheet');
        $parsed = $this->loadSlot('moncu', $token);
        $roster = $this->resolveTargetRoster($slot ? (int)$slot['roster_id'] : 0);
        $report = $this->runMoncu($parsed, true, $roster);
        session()->forget('rv2_sheet');
        return $report;
    }

    private function requireMoncuTables() {
        if (!$this->repo->moncuTablesReady()) throw new RosterV2Conflict('Tabel MONCU belum tersedia. Jalankan migration MONCU terlebih dahulu.');
    }

    private function runMoncu(array $parsed, $commit, $roster = null) {
        $delOld = 0; $newEmp = 0; $updEmp = 0; $perOk = 0; $shifted = 0; $noHire = 0; $unm = [];
        $skippedNoEmp = []; $errs = []; $noPeriods = 0; $notMember = [];

        DB::beginTransaction();
        try {
            foreach ($parsed['employees'] as $nrp => $e) {
                $nrp = (string)$nrp;
                if (!$this->repo->findEmployeeByNrp($nrp)) { $skippedNoEmp[] = "$nrp {$e['name']}"; continue; }
                // 1. bersihkan periode lama (CT/ID di roster aktif dikembalikan)
                $old = $this->pdo->prepare('SELECT cycle_year, period_no FROM v2_moncu_periods WHERE nrp = ?');
                $old->execute([$nrp]);
                foreach ($old->fetchAll() as $p) {
                    try { $this->mon->deletePeriod($nrp, $p['cycle_year'], $p['period_no']); $delOld++; }
                    catch (Throwable $ex) { $errs[] = "NRP $nrp: periode lama {$p['cycle_year']}/{$p['period_no']} tidak dapat dihapus - " . $ex->getMessage(); }
                }
                // 2. karyawan MONCU: hiring date + grup. Dengan roster tujuan: grup = grup roster itu. Tanpa: grup lama dipertahankan,
                //    yang baru = grup roster tempat ia anggota.
                $ex = $this->pdo->prepare('SELECT group_name FROM v2_moncu_employees WHERE nrp = ?');
                $ex->execute([$nrp]); $row = $ex->fetch();
                $group = $roster ? $roster['roster_group'] : ($row ? $row['group_name'] : null);
                if ($roster && !$this->repo->findMemberByNrp((int)$roster['id'], $nrp)) $notMember[] = "$nrp {$e['name']}";
                if ($group === null || $group === '') {
                    $g = $this->pdo->prepare("SELECT r.roster_group FROM v2_roster_members m JOIN v2_rosters r ON r.id = m.roster_id
                                              WHERE m.nrp = ? AND r.status <> 'archived' ORDER BY r.period_start DESC LIMIT 1");
                    $g->execute([$nrp]); $group = $g->fetchColumn() ?: null;
                }
                if ($e['hiring']) {
                    try { $this->mon->saveEmployee(['nrp' => $nrp, 'hiring_date' => $e['hiring'], 'group_name' => (string)$group]); $row ? $updEmp++ : $newEmp++; }
                    catch (Throwable $ex2) { $errs[] = "NRP $nrp: karyawan MONCU - " . $ex2->getMessage(); continue; }
                } else {
                    $noHire++;
                    if (!$row) { $this->repo->ensureMoncuEmployee($nrp, (string)$group, $this->actor()); $newEmp++; }
                }
                // 3. periode
                if (!$e['periods']) $noPeriods++;
                foreach ($e['periods'] as $p) {
                    try {
                        $this->mon->savePeriod($nrp, $p['year'], $p['no'], ['awal' => $p['awal'], 'tahunan' => (string)$p['tahunan'], 'izin_resmi' => (string)$p['izin']]);
                        $perOk++; if ($p['shifted']) $shifted++;
                    } catch (Throwable $ex3) { $errs[] = "NRP $nrp {$p['year']}/P{$p['no']} AWAL {$p['awal']}: " . $ex3->getMessage(); }
                }
                foreach ($e['unmapped'] as $u) $unm[] = "NRP $nrp AWAL {$u['awal']} (TAHUNAN {$u['tahunan']}) - lebih dari 5 periode (4 reguler + 1 tambahan) pada tahun " . substr($u['awal'], 0, 4);
            }
            if ($commit) DB::commit(); else DB::rollBack();
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            throw $e;
        }

        $stats = [
            ['Roster tujuan', $roster ? $roster['name'] . ' (grup ' . $roster['roster_group'] . ')' : 'Semua roster aktif (tanpa tujuan tertentu)'],
            ['Karyawan di sheet', count($parsed['employees'])],
            ['Karyawan MONCU baru', $newEmp],
            ['Karyawan MONCU diperbarui', $updEmp],
            ['Tanpa Hiring Date di sheet', $noHire],
            ['Periode lama dihapus', $delOld],
            ['Periode cuti disimpan', $perOk],
            ['Nomor periode digeser (bentrok label)', $shifted],
        ];
        $lists = [];
        if ($skippedNoEmp) $lists[] = ['title' => 'Dilewati: NRP tidak ada di data karyawan (' . count($skippedNoEmp) . ')', 'tone' => 'danger', 'items' => $skippedNoEmp];
        if ($errs) $lists[] = ['title' => 'Periode/karyawan yang ditolak (' . count($errs) . ')', 'tone' => 'danger', 'items' => $errs];
        if ($unm) $lists[] = ['title' => 'Tidak muat: aplikasi membatasi 5 periode per tahun (4 reguler + 1 tambahan) (' . count($unm) . ')', 'tone' => 'warning', 'items' => $unm];
        if ($notMember) $lists[] = ['title' => 'Belum menjadi anggota roster tujuan (' . count($notMember) . '): tetap masuk MONCU, tetapi cuti (CT/ID) tidak muncul di roster ini sampai ia ditambahkan sebagai anggota', 'tone' => 'warning', 'items' => $notMember];
        return ['kind' => 'moncu', 'committed' => (bool)$commit, 'stats' => $stats, 'lists' => $lists, 'warnings' => $parsed['warnings']];
    }
}
