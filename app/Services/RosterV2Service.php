<?php

namespace App\Services;

use App\Exceptions\RosterV2Conflict;
use App\Exceptions\RosterV2Forbidden;
use App\Exceptions\RosterV2NotFound;
use App\Repositories\RosterV2Repository;
use App\Support\RosterV2Rotation;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

// app/Services/RosterV2Service.php (port V1)
//
// Business rules Roster V2. Repository murni akses data; validasi & otorisasi di sini.
// Semua kegagalan dilempar sebagai exception bertipe (dipetakan ke HTTP status oleh OneRosterController):
//   InvalidArgumentException  -> 422 (validasi input)
//   RosterV2NotFound          -> 404
//   RosterV2Forbidden         -> 403 (tidak berwenang)
//   RosterV2Conflict          -> 409 (state bisnis tidak mengizinkan, mis. roster sudah published)
//
// Siklus status:  draft -> published -> archived   (draft -> archived juga boleh; TIDAK ada kembali ke draft)
// Isi roster (header, sel, alokasi, import) hanya boleh diubah saat DRAFT.

class RosterV2Service {
    const STATUSES = ['draft', 'published', 'archived'];
    const MAX_PERIOD_DAYS = 366;
    const IMPORT_TTL = 1800; // detik: preview import kedaluwarsa

    private $repo;

    public function __construct(RosterV2Repository $repo) {
        $this->repo = $repo;
    }

    // ---------------------------------------------------------- Authorization
    // Izin Roster V2 didelegasikan ke User::canViewRosterV2() / canManageRosterV2().

    public static function canView()
    {
        return (bool) Auth::user()?->canViewRosterV2();
    }

    public static function canManage()
    {
        return (bool) Auth::user()?->canManageRosterV2();
    }

    private function requireManage() {
        if (!self::canManage()) {
            throw new RosterV2Forbidden('Anda hanya memiliki akses lihat pada Roster V2.');
        }
    }

    private function requireView() {
        if (!self::canView()) {
            throw new RosterV2Forbidden('Tidak berwenang melihat Roster V2.');
        }
    }

    private function actor() {
        return substr((string)(Auth::user()?->username ?? ''), 0, 50);
    }

    // ------------------------------------------------------------ Validation

    private function validDate($s) {
        $d = DateTime::createFromFormat('Y-m-d', (string)$s);
        return $d && $d->format('Y-m-d') === $s;
    }

    private function cleanText($s, $max) {
        return mb_substr(trim((string)$s), 0, $max);
    }

    /** Validasi group + periode; return [group, start, end]. */
    private function validateHeader(array $in) {
        $group = $this->cleanText($in['roster_group'] ?? '', 50);
        if ($group === '') {
            throw new InvalidArgumentException('Roster Group wajib diisi.');
        }
        $start = (string)($in['period_start'] ?? '');
        $end   = (string)($in['period_end'] ?? '');
        $year  = trim((string)($in['year'] ?? ''));
        if ($year !== '') {
            if (!preg_match('/^(20[0-9]{2})$/', $year)) {
                throw new InvalidArgumentException('Tahun roster tidak valid.');
            }
            $start = $year . '-01-01';
            $end   = $year . '-12-31';
        }
        if ($start === '' || $end === '') {
            throw new InvalidArgumentException('Period Start dan Period End wajib diisi.');
        }
        if (!$this->validDate($start) || !$this->validDate($end)) {
            throw new InvalidArgumentException('Format tanggal periode harus YYYY-MM-DD.');
        }
        if ($end < $start) {
            throw new InvalidArgumentException('Tanggal akhir tidak boleh sebelum tanggal awal.');
        }
        $days = (new DateTime($start))->diff(new DateTime($end))->days + 1;
        if ($days > self::MAX_PERIOD_DAYS) {
            throw new InvalidArgumentException('Periode maksimal ' . self::MAX_PERIOD_DAYS . ' hari.');
        }
        return [$group, $start, $end];
    }

    private function buildName($group, $start, $end) {
        return $this->cleanText("Roster $group $start s/d $end", 150);
    }

    // ---------------------------------------------------------------- Rosters

    public function createRoster(array $in) {
        $this->requireManage();
        [$group, $start, $end] = $this->validateHeader($in);

        if ($this->repo->findRosterByPeriod($group, $start, $end)) {
            throw new InvalidArgumentException('Roster untuk grup & periode ini sudah ada.');
        }

        $name = $this->cleanText($in['name'] ?? '', 150);
        $newRid = $this->repo->createRoster([
            'name' => $name !== '' ? $name : $this->buildName($group, $start, $end),
            'roster_group' => $group,
            'period_start' => $start, 'period_end' => $end,
            'status' => 'draft',
            'notes' => $this->cleanText($in['notes'] ?? '', 255) ?: null,
            'pic_name' => $this->cleanText($in['pic_name'] ?? '', 150) ?: ($this->cleanText(Auth::user()?->name ?? (Auth::user()?->username ?? ''), 150) ?: null),
            'pic_jabatan' => $this->cleanText($in['pic_jabatan'] ?? '', 100) ?: ($this->cleanText(Auth::user()?->jabatan ?? '', 100) ?: null),
            'actor' => $this->actor(),
        ]);
        $this->rlog($newRid, 'roster', 'membuat roster ' . $group . ' (' . $this->fmtDate($start) . ' s/d ' . $this->fmtDate($end) . ')');
        return $newRid;
    }

    /** Daftar bulan ('YYYY-MM') yang dicakup periode roster, urut. */
    public function rosterMonths(array $roster) {
        $out = [];
        for ($d = new DateTime(substr($roster['period_start'], 0, 7) . '-01'), $end = new DateTime($roster['period_end']); $d <= $end; $d->modify('+1 month')) {
            $out[] = $d->format('Y-m');
        }
        return $out;
    }

    /**
     * Atur bulan roster yang boleh dilihat role non-management (Share Roster). $months: ['YYYY-MM', ...] (kosong = tidak ada bulan dibuka).
     * Hanya bulan yang ada di periode roster yang diterima. Roster draft & published boleh; archived terkunci.
     */
    public function setVisibleMonths($rosterId, array $months) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        if (!$this->repo->hasVisibleMonthsColumn()) {
            throw new RosterV2Conflict('Kolom visibilitas belum tersedia. Jalankan migration Roster V2 terlebih dahulu.');
        }
        $valid = $this->rosterMonths($roster);
        $clean = [];
        foreach ($months as $ym) {
            $ym = trim((string)$ym);
            if ($ym === '') continue;
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym) || !in_array($ym, $valid, true)) {
                throw new InvalidArgumentException('Bulan "' . $ym . '" tidak berada dalam periode roster ini.');
            }
            $clean[$ym] = true;
        }
        $list = array_keys($clean); sort($list);
        $this->repo->setVisibleMonths($roster['id'], $list ? implode(',', $list) : null);
        $this->rlog($roster['id'], 'roster', $list ? 'mengatur bulan yang dibuka di Share Roster: ' . implode(', ', $list) : 'menutup semua bulan di Share Roster');
        return $list;
    }

    /** Ubah header roster (group, periode, catatan). Draft dan published; archived terkunci. */
    public function updateRoster($rosterId, array $in) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        [$group, $start, $end] = $this->validateHeader($in);

        $dup = $this->repo->findRosterByPeriod($group, $start, $end);
        if ($dup && (int)$dup['id'] !== (int)$roster['id']) {
            throw new InvalidArgumentException('Roster untuk grup & periode ini sudah ada.');
        }
        $range = $this->repo->detailDateRange($roster['id']);
        if ($range && ($range[0] < $start || $range[1] > $end)) {
            throw new InvalidArgumentException(
                "Periode baru tidak mencakup sel yang sudah ada ({$range[0]} s/d {$range[1]})."
            );
        }

        // Nama otomatis ikut berubah hanya bila sebelumnya juga otomatis.
        $autoOld = $this->buildName($roster['roster_group'], $roster['period_start'], $roster['period_end']);
        $name = ($roster['name'] === $autoOld) ? $this->buildName($group, $start, $end) : $roster['name'];

        $this->repo->updateRosterHeader($roster['id'], [
            'name' => $name, 'roster_group' => $group,
            'period_start' => $start, 'period_end' => $end,
            'notes' => $this->cleanText($in['notes'] ?? '', 255) ?: null,
            // PIC: hanya diubah bila dikirim oleh form (pemanggil tanpa kolom PIC tidak menghapusnya)
            'pic_name' => array_key_exists('pic_name', $in) ? ($this->cleanText($in['pic_name'], 150) ?: null) : ($roster['pic_name'] ?? null),
            'pic_jabatan' => array_key_exists('pic_jabatan', $in) ? ($this->cleanText($in['pic_jabatan'], 100) ?: null) : ($roster['pic_jabatan'] ?? null),
            'actor' => $this->actor(),
        ]);
        $ch = [];
        if ($group !== $roster['roster_group']) $ch[] = 'grup ' . $roster['roster_group'] . ' menjadi ' . $group;
        if ($start !== $roster['period_start'] || $end !== $roster['period_end']) $ch[] = 'periode menjadi ' . $this->fmtDate($start) . ' s/d ' . $this->fmtDate($end);
        if (array_key_exists('pic_name', $in) && ($this->cleanText($in['pic_name'], 150) ?: null) !== ($roster['pic_name'] ?? null)) $ch[] = 'PIC menjadi ' . ($this->cleanText($in['pic_name'], 150) ?: '-');
        if ($this->cleanText($in['notes'] ?? '', 255) !== (string)($roster['notes'] ?? '')) $ch[] = 'catatan';
        $this->rlog($roster['id'], 'roster', 'mengubah data roster' . ($ch ? ': ' . implode('; ', $ch) : ''));
    }

    /**
     * Ubah status. Aturan publish: roster ada, status draft, detail >= 1.
     * (Aturan "semua tanggal terisi" sengaja TIDAK ada - belum dikonfirmasi.)
     */
    public function changeStatus($rosterId, $status) {
        $this->requireManage();
        if (!in_array($status, ['published', 'archived'], true)) {
            throw new InvalidArgumentException('Status tujuan tidak valid.');
        }
        $roster = $this->getRosterOrFail($rosterId);
        $from = $roster['status'];

        $allowed = ['draft' => ['published', 'archived'], 'published' => ['archived']];
        if (!in_array($status, $allowed[$from] ?? [], true)) {
            throw new RosterV2Conflict("Roster berstatus $from tidak dapat diubah menjadi $status.");
        }
        if ($status === 'published' && $this->repo->countDetails($roster['id']) < 1) {
            throw new RosterV2Conflict('Roster belum berisi detail, tidak dapat dipublish.');
        }
        $this->repo->updateRosterStatus($roster['id'], $status, $this->actor());
        $this->rlog($roster['id'], 'status', $status === 'archived' ? 'mengarsipkan roster (terkunci)' : 'mempublish roster');
    }

    // ------------------------------------------------------------ Riwayat perubahan

    /** true = tidak mencatat riwayat per operasi (dipakai proses massal yang mencatat satu ringkasan sendiri). */
    public $logSilent = false;
    private $shiftTextCache = [];

    private function fmtDate($ymd) {
        $b = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t = strtotime((string)$ymd);
        return $t ? ((int)date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t)) : (string)$ymd;
    }

    /** "Siang (D)", "Malam (N)", "OFF", "Cuti (CT)" ... */
    private function shiftText($code) {
        $code = (string)$code;
        if ($code === '') return '(kosong)';
        if (!isset($this->shiftTextCache[$code])) {
            if ($code === 'D') $t = 'Siang (D)';
            elseif ($code === 'N') $t = 'Malam (N)';
            elseif ($code === 'OFF') $t = 'OFF';
            else { $s = $this->repo->findShiftByCode($code); $t = ($s && !empty($s['label']) && $s['label'] !== $code) ? $s['label'] . ' (' . $code . ')' : $code; }
            $this->shiftTextCache[$code] = $t;
        }
        return $this->shiftTextCache[$code];
    }

    private function prevCode($rosterId, $nrp, $date) {
        $m = $this->repo->getCellCodesInRange($rosterId, $date, $date);
        return $m[$nrp][$date] ?? '';
    }

    /** Catat satu perubahan. Tidak pernah menggagalkan operasi aslinya. */
    private function rlog($rosterId, $action, $summary, $targetNrp = null, $targetName = null) {
        if ($this->logSilent) return;
        try {
            if (!$this->repo->changelogReady()) return;
            $this->repo->addChangelog([
                'roster_id' => (int)$rosterId,
                'actor_nrp' => $this->actor() ?: null,
                'actor_name' => $this->cleanText(Auth::user()?->name ?? '', 150) ?: null,
                'action' => substr((string)$action, 0, 20),
                'target_nrp' => $targetNrp !== null ? substr((string)$targetNrp, 0, 20) : null,
                'target_name' => $targetName !== null ? $this->cleanText($targetName, 150) : null,
                'summary' => $this->cleanText($summary, 600),
            ]);
        } catch (Throwable $e) {
            Log::warning('[RosterV2] riwayat perubahan gagal dicatat: ' . $e->getMessage());
        }
    }

    /** Untuk layanan lain (mis. import spreadsheet) yang mencatat ringkasan sendiri. */
    public function logChange($rosterId, $action, $summary, $targetNrp = null, $targetName = null) {
        $this->rlog($rosterId, $action, $summary, $targetNrp, $targetName);
    }

    /** Riwayat perubahan roster $days hari terakhir (bawaan 3). Return ['ready', 'days', 'total', 'rows']. */
    public function getChangelog($rosterId, $days = 3, $limit = 300) {
        $this->requireView();
        $days = max(1, min(14, (int)$days ?: 3));
        if (!$this->repo->changelogReady()) return ['ready' => false, 'days' => $days, 'total' => 0, 'rows' => []];
        $since = date('Y-m-d H:i:s', time() - $days * 86400);
        return ['ready' => true, 'days' => $days, 'total' => $this->repo->countChangelog($rosterId, $since),
                'rows' => $this->repo->listChangelog($rosterId, $since, $limit)];
    }

    const AUTH_MAX_FAIL = 5;     // percobaan salah berturut-turut
    const AUTH_LOCK_SEC = 600;   // dikunci 10 menit setelah itu

    /**
     * Verifikasi ulang akun yang sedang login sebelum aksi berisiko (archive roster): NRP harus NRP akun ini dan password harus benar.
     * Aturan password sama dengan login (hash di tabel users; belum punya password sendiri = password sama dengan NRP).
     * @throws InvalidArgumentException NRP/password salah; RosterV2Conflict terlalu banyak percobaan.
     */
    public function verifyActorCredentials($nrp, $password) {
        $this->requireManage();
        $me = (string)(Auth::user()?->username ?? '');
        $fail = session('rv2_auth_fail', ['n' => 0, 't' => 0]);
        if ((int)$fail['n'] >= self::AUTH_MAX_FAIL) {
            $left = self::AUTH_LOCK_SEC - (time() - (int)$fail['t']);
            if ($left > 0) throw new RosterV2Conflict('Terlalu banyak percobaan salah. Coba lagi dalam ' . (int)ceil($left / 60) . ' menit.');
            $fail = ['n' => 0, 't' => 0];
        }
        $nrp = trim((string)$nrp); $password = (string)$password;
        $ok = false;
        if ($me !== '' && $nrp !== '' && $password !== '' && hash_equals($me, $nrp)) {
            $hash = $this->repo->findUserPasswordHash($me);
            $ok = ($hash !== null && $hash !== '') ? password_verify($password, $hash) : hash_equals($me, $password);
        }
        if (!$ok) {
            session(['rv2_auth_fail' => ['n' => (int)$fail['n'] + 1, 't' => time()]]);
            Log::warning('[RosterV2] verifikasi archive gagal untuk ' . $me);
            throw new InvalidArgumentException('NRP atau password salah.');
        }
        session()->forget('rv2_auth_fail');
        return true;
    }

    private function getRosterOrFail($id) {
        $r = $this->repo->findRoster($id);
        if (!$r) throw new RosterV2NotFound('Roster tidak ditemukan.');
        return $r;
    }

    /**
     * Roster DRAFT dan PUBLISHED masih boleh diubah isinya (publish = roster final untuk dilihat, tetapi penyesuaian tetap terjadi).
     * Hanya roster ARCHIVED yang terkunci.
     */
    private function getEditableRoster($id) {
        $r = $this->getRosterOrFail($id);
        if ($r['status'] === 'archived') {
            throw new RosterV2Conflict('Roster sudah diarsipkan, tidak dapat diubah.');
        }
        return $r;
    }

    private function getDetailOfRoster($rosterId, $detailId) {
        $d = $this->repo->findDetailById($detailId);
        if (!$d || (int)$d['roster_id'] !== (int)$rosterId) {
            throw new RosterV2NotFound('Detail tidak ditemukan pada roster ini.');
        }
        return $d;
    }

    // ------------------------------------------------------------------ Cells

    /**
     * Tetapkan shift untuk NRP pada tanggal tertentu (upsert).
     * Snapshot nama/jabatan dari employees (read-only).
     */
    public function assignShift($rosterId, $nrp, $workDate, $shiftCode, $notes = null) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        return $this->writeCell($roster, $nrp, $workDate, $shiftCode, $notes);
    }

    private function checkDateInPeriod(array $roster, $workDate) {
        if (!$this->validDate((string)$workDate)) {
            throw new InvalidArgumentException('Format tanggal harus YYYY-MM-DD.');
        }
        if ($workDate < $roster['period_start'] || $workDate > $roster['period_end']) {
            throw new InvalidArgumentException('Tanggal di luar periode roster.');
        }
    }

    private function writeCell(array $roster, $nrp, $workDate, $shiftCode, $notes) {
        $nrp = trim((string)$nrp);
        if ($nrp === '' || strlen($nrp) > 20) {
            throw new InvalidArgumentException('NRP tidak valid.');
        }
        $this->checkDateInPeriod($roster, $workDate);
        $shift = $this->repo->findShiftByCode(trim((string)$shiftCode));
        if (!$shift || !(int)$shift['is_active']) {
            throw new InvalidArgumentException('Kode shift tidak dikenal atau nonaktif.');
        }
        $emp = $this->repo->findEmployeeByNrp($nrp);
        if (!$emp) {
            throw new InvalidArgumentException('NRP tidak ditemukan di data karyawan.');
        }

        $this->repo->upsertDetail([
            'roster_id' => $roster['id'], 'nrp' => $nrp,
            'employee_name' => $emp['name'], 'jabatan' => $emp['jabatan'],
            'work_date' => $workDate, 'shift_id' => $shift['id'],
            'notes' => $notes !== null ? ($this->cleanText($notes, 255) ?: null) : null,
            'actor' => $this->actor(),
        ]);
        return $this->repo->findDetail($roster['id'], $nrp, $workDate);
    }

    /**
     * Tambah detail baru (NRP x tanggal). Sel yang sudah ada DITOLAK (bukan ditimpa).
     * Unit/section opsional; bila unit diisi, alokasi dibuat dalam transaksi yang sama.
     */
    public function addDetail($rosterId, $nrp, $workDate, $shiftCode, $unit = '', $section = '', $notes = null) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);

        $unit    = $this->cleanText($unit, 50);
        $section = $this->cleanText($section, 100);
        if ($unit === '' && $section !== '') {
            throw new InvalidArgumentException('Section tidak boleh diisi tanpa Unit.');
        }
        if ($this->repo->findDetail($roster['id'], trim((string)$nrp), (string)$workDate)) {
            throw new InvalidArgumentException('NRP ini sudah punya detail pada tanggal tersebut. Gunakan Edit untuk mengubahnya.');
        }

        $detail = $this->repo->transaction(function () use ($roster, $nrp, $workDate, $shiftCode, $unit, $section, $notes) {
            $detail = $this->writeCell($roster, $nrp, $workDate, $shiftCode, $notes);
            if ($unit !== '') {
                $this->repo->addAllocation($detail['id'], $unit, $section, null, $this->actor());
            }
            return $detail;
        });
        $this->rlog($roster['id'], 'detail', 'menambah detail jadwal tanggal ' . $this->fmtDate($workDate) . ' = ' . $this->shiftText($detail['shift_code'] ?? $shiftCode) . ($unit !== '' ? ', unit ' . $unit : ''), $detail['nrp'], $detail['employee_name'] ?? null);
        return $detail;
    }

    /**
     * Edit langsung sebuah detail: tanggal, shift, unit, section. NRP read-only.
     * - Snapshot employee TIDAK dibuat ulang (NRP tidak berubah).
     * - Unit/section mengedit alokasi PERTAMA detail (yang dibuat saat "Tambah Detail");
     *   kosongkan unit = hapus alokasi pertama itu. Alokasi tambahan tidak disentuh.
     */
    public function updateDetail($rosterId, $detailId, array $in) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $detail = $this->getDetailOfRoster($roster['id'], $detailId);

        if (isset($in['nrp']) && trim((string)$in['nrp']) !== '' && trim((string)$in['nrp']) !== $detail['nrp']) {
            throw new InvalidArgumentException('NRP tidak dapat diubah. Hapus detail lalu tambahkan untuk NRP lain.');
        }

        // Tanggal
        $newDate = isset($in['work_date']) && trim((string)$in['work_date']) !== '' ? trim((string)$in['work_date']) : $detail['work_date'];
        $this->checkDateInPeriod($roster, $newDate);
        if ($newDate !== $detail['work_date']) {
            $clash = $this->repo->findDetail($roster['id'], $detail['nrp'], $newDate);
            if ($clash && (int)$clash['id'] !== (int)$detail['id']) {
                throw new InvalidArgumentException('NRP ini sudah punya detail pada tanggal tersebut.');
            }
        }

        // Shift
        $curShift = $this->repo->findShiftById($detail['shift_id']);
        $code = isset($in['shift_code']) && trim((string)$in['shift_code']) !== '' ? trim((string)$in['shift_code']) : ($curShift['code'] ?? '');
        if ($curShift && $code === $curShift['code']) {
            $shiftId = (int)$curShift['id']; // tidak berubah: tetap sah walau shift kini nonaktif
        } else {
            $shift = $this->repo->findShiftByCode($code);
            if (!$shift || !(int)$shift['is_active']) {
                throw new InvalidArgumentException('Kode shift tidak dikenal atau nonaktif.');
            }
            $shiftId = (int)$shift['id'];
        }

        // Unit / section
        $unit    = $this->cleanText($in['unit'] ?? '', 50);
        $section = $this->cleanText($in['section'] ?? '', 100);
        if ($unit === '' && $section !== '') {
            throw new InvalidArgumentException('Section tidak boleh diisi tanpa Unit.');
        }

        $this->repo->transaction(function () use ($detail, $newDate, $shiftId, $unit, $section) {
            if ($newDate !== $detail['work_date'] || $shiftId !== (int)$detail['shift_id']) {
                $this->repo->updateDetailFields($detail['id'], $newDate, $shiftId, $this->actor());
            }
            $allocs  = $this->repo->listAllocationsByDetail($detail['id']);
            $primary = $allocs[0] ?? null;

            if ($unit === '') {
                if ($primary) $this->repo->deleteAllocationById($primary['id']);
                return;
            }
            if ($primary && $primary['unit'] === $unit && $primary['section'] === $section) return;

            foreach ($allocs as $a) {
                if ($a['unit'] === $unit && $a['section'] === $section) {
                    throw new InvalidArgumentException('Alokasi unit/section ini sudah ada pada detail tersebut.');
                }
            }
            if ($primary) {
                $this->repo->updateAllocation($primary['id'], $unit, $section);
            } else {
                $this->repo->addAllocation($detail['id'], $unit, $section, null, $this->actor());
            }
        });
        $ch = [];
        if ($newDate !== $detail['work_date']) $ch[] = 'tanggal ' . $this->fmtDate($detail['work_date']) . ' menjadi ' . $this->fmtDate($newDate);
        if ($shiftId !== (int)$detail['shift_id']) $ch[] = 'shift ' . $this->shiftText($curShift['code'] ?? '') . ' menjadi ' . $this->shiftText($code);
        if ($unit !== '') $ch[] = 'unit ' . $unit . ($section !== '' ? ' / ' . $section : '');
        $this->rlog($roster['id'], 'detail', 'mengubah detail jadwal tanggal ' . $this->fmtDate($detail['work_date']) . ($ch ? ': ' . implode('; ', $ch) : ''), $detail['nrp'], $detail['employee_name'] ?? null);
    }

    public function clearCell($rosterId, $nrp, $workDate) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $nrp = trim((string)$nrp); $prev = $this->prevCode($roster['id'], $nrp, (string)$workDate);
        $res = $this->repo->deleteDetail($roster['id'], $nrp, (string)$workDate);
        if ($prev !== '') { $mm = $this->repo->findMemberByNrp($roster['id'], $nrp); $this->rlog($roster['id'], 'jadwal', 'mengosongkan jadwal tanggal ' . $this->fmtDate($workDate) . ' (sebelumnya ' . $this->shiftText($prev) . ')', $nrp, $mm['employee_name'] ?? null); }
        return $res;
    }

    /** Hapus detail berdasarkan id (alokasinya ikut terhapus via FK CASCADE). */
    public function deleteDetail($rosterId, $detailId) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $d = $this->getDetailOfRoster($roster['id'], $detailId);
        $prev = $this->prevCode($roster['id'], $d['nrp'], $d['work_date']);
        $this->repo->deleteDetail($roster['id'], $d['nrp'], $d['work_date']);
        $this->rlog($roster['id'], 'detail', 'menghapus detail jadwal tanggal ' . $this->fmtDate($d['work_date']) . ($prev !== '' ? ' (sebelumnya ' . $this->shiftText($prev) . ')' : ''), $d['nrp'], $d['employee_name'] ?? null);
    }

    // ------------------------------------------------------------ Allocations

    /** Alokasi berdasarkan NRP + tanggal (dipakai test/service lain). */
    public function allocate($rosterId, $nrp, $workDate, $unit, $section = '', $notes = null) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $detail = $this->repo->findDetail($roster['id'], trim((string)$nrp), (string)$workDate);
        if (!$detail) {
            throw new InvalidArgumentException('Sel roster belum ada; tetapkan shift dulu.');
        }
        $this->insertAllocation($detail, $unit, $section, $notes);
    }

    /** Alokasi berdasarkan id detail (dipakai UI). */
    public function allocateDetail($rosterId, $detailId, $unit, $section = '', $notes = null) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $d = $this->getDetailOfRoster($roster['id'], $detailId);
        $this->insertAllocation($d, $unit, $section, $notes);
        $this->rlog($roster['id'], 'detail', 'menambah alokasi unit ' . $this->cleanText($unit, 50) . ' pada tanggal ' . $this->fmtDate($d['work_date']), $d['nrp'], $d['employee_name'] ?? null);
    }

    private function insertAllocation(array $detail, $unit, $section, $notes) {
        $unit = $this->cleanText($unit, 50);
        if ($unit === '') {
            throw new InvalidArgumentException('Unit wajib diisi.');
        }
        $section = $this->cleanText($section, 100);
        if ($this->repo->findAllocation($detail['id'], $unit, $section)) {
            throw new InvalidArgumentException('Alokasi unit/section ini sudah ada pada detail tersebut.');
        }
        $this->repo->addAllocation(
            $detail['id'], $unit, $section,
            $notes !== null ? ($this->cleanText($notes, 255) ?: null) : null,
            $this->actor()
        );
    }

    public function removeAllocation($rosterId, $detailId, $unit, $section = '') {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $detail = $this->getDetailOfRoster($roster['id'], $detailId);
        if (!$this->repo->removeAllocation($detail['id'], $this->cleanText($unit, 50), $this->cleanText($section, 100))) {
            throw new RosterV2NotFound('Alokasi tidak ditemukan.');
        }
        $this->rlog($roster['id'], 'detail', 'menghapus alokasi unit ' . $this->cleanText($unit, 50) . ' pada tanggal ' . $this->fmtDate($detail['work_date']), $detail['nrp'], $detail['employee_name'] ?? null);
    }

    // ----------------------------------------------------------------- Import
    // Alur: previewImport() (read-only, tidak menulis apa pun) -> user konfirmasi -> commitImport()
    // (satu transaksi, semua-atau-tidak-sama-sekali). Baris ERROR tidak pernah di-insert.

    /**
     * Validasi baris hasil parse Excel terhadap roster + DB. Tidak menulis apa pun.
     * @param array $rawRows dari RosterV2Xlsx::readImportFile()['rows']
     * @return array ['summary'=>[total,valid,warning,error], 'rows'=>[...preview...], 'importable'=>[...]]
     */
    public function previewImport($rosterId, array $rawRows) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        return $this->validateImportRows($roster, $rawRows);
    }

    private function validateImportRows(array $roster, array $rawRows) {
        $nrps = [];
        foreach ($rawRows as $r) {
            $n = $this->normalizeNrp($r['nrp'] ?? '');
            if ($n !== '') $nrps[] = $n;
        }
        $employees = $this->repo->findEmployeesByNrps($nrps);
        $shifts = [];
        foreach ($this->repo->listShifts(false) as $s) { $shifts[$s['code']] = $s; }
        $existing = $this->repo->existingCellKeys($roster['id']);

        $seen = [];   // 'nrp|tanggal' => baris Excel pertama
        $out = []; $importable = [];
        $sum = ['total' => 0, 'valid' => 0, 'warning' => 0, 'error' => 0];

        foreach ($rawRows as $r) {
            $errors = []; $warnings = [];
            $nrp     = $this->normalizeNrp($r['nrp'] ?? '');
            $date    = $this->normalizeDate($r['tanggal'] ?? '');
            $code    = strtoupper(trim((string)($r['shift'] ?? '')));
            $unit    = trim((string)($r['unit'] ?? ''));
            $section = trim((string)($r['section'] ?? ''));
            $emp = null; $shift = null;

            if ($nrp === '') {
                $errors[] = 'NRP kosong';
            } elseif (strlen($nrp) > 20 || !isset($employees[$nrp])) {
                $errors[] = 'NRP tidak ditemukan';
            } else {
                $emp = $employees[$nrp];
                $fileName = trim((string)($r['nama'] ?? ''));
                if ($fileName !== '' && strcasecmp($fileName, (string)$emp['name']) !== 0) {
                    $warnings[] = 'Nama di file berbeda dengan data karyawan (' . $emp['name'] . '); data karyawan yang dipakai';
                }
            }

            if ($date === null) {
                $errors[] = ($r['tanggal'] ?? '') === '' ? 'Tanggal kosong' : 'Tanggal tidak valid';
            } elseif ($date < $roster['period_start'] || $date > $roster['period_end']) {
                $errors[] = 'Tanggal di luar periode roster';
            }

            if ($code === '') {
                $errors[] = 'Shift kosong';
            } elseif (!isset($shifts[$code])) {
                $errors[] = 'Shift tidak ditemukan';
            } elseif (!(int)$shifts[$code]['is_active']) {
                $errors[] = 'Shift nonaktif';
            } else {
                $shift = $shifts[$code];
            }

            if (mb_strlen($unit) > 50)    $errors[] = 'Unit maksimal 50 karakter';
            if (mb_strlen($section) > 100) $errors[] = 'Section maksimal 100 karakter';
            if ($unit === '' && $section !== '') {
                $errors[] = 'Section diisi tanpa Unit';
            } elseif ($unit === '' && $shift && $shift['category'] === 'work') {
                $warnings[] = 'Unit kosong pada shift kerja';
            }

            if ($nrp !== '' && $date !== null) {
                $key = $nrp . '|' . $date;
                if (isset($existing[$key])) {
                    $errors[] = 'Sudah ada di roster ini (duplikat dengan database)';
                } elseif (isset($seen[$key])) {
                    $errors[] = 'Duplikat dalam file (sama dengan baris ' . $seen[$key] . ')';
                } else {
                    $seen[$key] = (int)($r['row'] ?? 0);
                }
            }

            $status = $errors ? 'ERROR' : ($warnings ? 'WARNING' : 'VALID');
            $sum['total']++;
            $sum[$errors ? 'error' : ($warnings ? 'warning' : 'valid')]++;

            $out[] = [
                'row' => (int)($r['row'] ?? 0), 'nrp' => $nrp, 'nama' => $emp['name'] ?? '',
                'tanggal' => $date ?? (string)($r['tanggal'] ?? ''), 'shift' => $code,
                'unit' => $unit, 'section' => $section, 'status' => $status,
                'message' => implode('; ', $errors ?: $warnings),
            ];
            if (!$errors) {
                $importable[] = [
                    'row' => (int)($r['row'] ?? 0), 'nrp' => $nrp, 'employee_name' => $emp['name'],
                    'jabatan' => $emp['jabatan'], 'work_date' => $date, 'shift_id' => (int)$shift['id'],
                    'unit' => $unit, 'section' => $section,
                ];
            }
        }
        return ['summary' => $sum, 'rows' => $out, 'importable' => $importable];
    }

    /** NRP dari Excel bisa berupa angka ("12345" atau "12345.0"). */
    private function normalizeNrp($v) {
        $v = trim((string)$v);
        return preg_match('/^\d+\.0+$/', $v) ? preg_replace('/\.0+$/', '', $v) : $v;
    }

    /** Terima Y-m-d, d/m/Y, d-m-Y (ketat, tanggal harus nyata). Null bila tidak valid. */
    private function normalizeDate($v) {
        $v = trim((string)$v);
        if ($v === '') return null;
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            $d = DateTime::createFromFormat('!' . $fmt, $v);
            if ($d && $d->format($fmt) === $v) return $d->format('Y-m-d');
        }
        return null;
    }

    /**
     * Simpan hasil preview di sesi user (server-side; klien tidak mengirim ulang isi baris).
     * Slot tunggal per user; token acak mengikat preview ke roster ini.
     */
    public function stashImport($rosterId, array $importable) {
        $token = bin2hex(random_bytes(16));
        session(['rv2_import' => [
            'token' => $token, 'roster_id' => (int)$rosterId,
            'rows' => $importable, 'at' => time(),
        ]]);
        return $token;
    }

    /**
     * Commit import: validasi ulang terhadap state DB SAAT INI (roster masih draft, tidak ada
     * duplikat baru), lalu insert semua baris dalam satu transaksi. Gagal di mana pun = rollback total.
     * @return int jumlah baris ter-insert
     */
    public function commitImport($rosterId, $token) {
        $this->requireManage();
        $slot = session('rv2_import');
        if (!$slot || !hash_equals((string)$slot['token'], (string)$token) || (int)$slot['roster_id'] !== (int)$rosterId) {
            throw new RosterV2Conflict('Preview import tidak ditemukan. Upload ulang file Anda.');
        }
        if (time() - (int)$slot['at'] > self::IMPORT_TTL) {
            session()->forget('rv2_import');
            throw new RosterV2Conflict('Preview import sudah kedaluwarsa. Upload ulang file Anda.');
        }
        $roster = $this->getEditableRoster($rosterId);
        $rows = $slot['rows'];
        if (!$rows) {
            throw new InvalidArgumentException('Tidak ada baris valid untuk diimport.');
        }

        // Re-check duplikat vs DB (bisa berubah sejak preview).
        $existing = $this->repo->existingCellKeys($roster['id']);
        foreach ($rows as $r) {
            if (isset($existing[$r['nrp'] . '|' . $r['work_date']])) {
                throw new RosterV2Conflict("Data roster berubah sejak preview (baris {$r['row']} sudah ada). Upload ulang file Anda.");
            }
        }

        $actor = $this->actor();
        $count = $this->repo->transaction(function () use ($roster, $rows, $actor) {
            $n = 0;
            foreach ($rows as $r) {
                $id = $this->repo->insertDetail([
                    'roster_id' => $roster['id'], 'nrp' => $r['nrp'],
                    'employee_name' => $r['employee_name'], 'jabatan' => $r['jabatan'],
                    'work_date' => $r['work_date'], 'shift_id' => $r['shift_id'],
                    'notes' => null, 'actor' => $actor,
                ]);
                if ($r['unit'] !== '') {
                    $this->repo->addAllocation($id, $r['unit'], $r['section'], null, $actor);
                }
                $n++;
            }
            return $n;
        });
        session()->forget('rv2_import');
        $this->rlog($roster['id'], 'import', 'mengimpor file Excel: ' . $count . ' sel jadwal');
        return $count;
    }

    // ------------------------------------------------------------------ Reads

    /** Filter dari query string -> filter repository (nilai tak valid diabaikan). */
    public function listRosters(array $q) {
        $this->requireView();
        $f = [];
        if (!empty($q['date_from']) && $this->validDate($q['date_from'])) $f['date_from'] = $q['date_from'];
        if (!empty($q['date_to'])   && $this->validDate($q['date_to']))   $f['date_to']   = $q['date_to'];
        if (isset($q['roster_group']) && $q['roster_group'] !== '') {
            $f['roster_group'] = $this->cleanText($q['roster_group'], 50);
        }
        if (!empty($q['status']) && in_array($q['status'], self::STATUSES, true)) $f['status'] = $q['status'];
        return $this->repo->listRosters($f, 200, 0);
    }

    public function getSummary() {
        $this->requireView();
        return $this->repo->countRostersByStatus();
    }

    public function getGroups() {
        $this->requireView();
        return $this->repo->listRosterGroups();
    }

    public function getShifts() {
        $this->requireView();
        return $this->repo->listShifts();
    }

    public function searchEmployees($term) {
        $this->requireView();
        $term = $this->cleanText($term, 50);
        return mb_strlen($term) < 2 ? [] : $this->repo->searchEmployees($term, 15);
    }

    public function lookupEmployee($nrp) {
        $this->requireView();
        return $this->repo->findEmployeeByNrp(trim((string)$nrp));
    }

    /** Roster + jumlah detail (header halaman view tanpa memuat grid). */
    public function getRosterHeader($rosterId) {
        $this->requireView();
        $roster = $this->getRosterOrFail($rosterId);
        return ['roster' => $roster, 'total' => $this->repo->countDetails($roster['id'])];
    }

    /** Roster + halaman detail + alokasi (dikelompokkan per detail_id). */
    public function getRosterGrid($rosterId, $page = 1, $perPage = 100) {
        $this->requireView();
        $roster = $this->getRosterOrFail($rosterId);
        $page   = max(1, (int)$page);
        $total  = $this->repo->countDetails($roster['id']);
        $details = $this->repo->getDetails($roster['id'], null, $perPage, ($page - 1) * $perPage);

        $alloc = [];
        foreach ($this->repo->getAllocationsByRoster($roster['id']) as $a) {
            $alloc[$a['detail_id']][] = $a;
        }
        return [
            'roster' => $roster, 'details' => $details, 'allocations' => $alloc,
            'total' => $total, 'page' => $page, 'per_page' => $perPage,
            'pages' => max(1, (int)ceil($total / $perPage)),
        ];
    }

    // ------------------------------------------------- Section & matriks
    // Section = kelompok orang di satu roster (mis. ADMIN PRODUKSI, HELPER PIT SERVICE).
    // Member = NRP yang berada di sebuah section; sel jadwal tetap di v2_roster_details.
    // Semua tulis hanya saat roster DRAFT dan hanya oleh yang boleh mengelola.

    /** Baris ringkasan matriks => kode shift yang dihitung. TOTAL = jumlah anggota. */
    const MATRIX_SUMMARY = [
        'SIANG' => 'D', 'MALAM' => 'N', 'OFF' => 'OFF', 'CUTI' => 'CT',
        'INDUKSI' => 'ID', 'MCU' => 'MCU', 'H' => 'H', 'TRAINING' => 'TR',
    ];

    public function sectionsReady() {
        return $this->repo->sectionTablesReady();
    }

    public function addSection($rosterId, $name) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $name = mb_strtoupper($this->cleanText($name, 100));
        if ($name === '') {
            throw new InvalidArgumentException('Nama section wajib diisi.');
        }
        if ($this->repo->findSectionByName($roster['id'], $name)) {
            throw new InvalidArgumentException('Section "' . $name . '" sudah ada pada roster ini.');
        }
        $newSid = $this->repo->createSection($roster['id'], $name, $this->actor());
        $this->rlog($roster['id'], 'section', 'menambah section ' . $name);
        return $newSid;
    }

    private function getSectionOfRoster($rosterId, $sectionId) {
        $s = $this->repo->findSection($sectionId);
        if (!$s || (int)$s['roster_id'] !== (int)$rosterId) {
            throw new RosterV2NotFound('Section tidak ditemukan pada roster ini.');
        }
        return $s;
    }

    private function getMemberOfRoster($rosterId, $memberId) {
        $m = $this->repo->findMemberById($memberId);
        if (!$m || (int)$m['roster_id'] !== (int)$rosterId) {
            throw new RosterV2NotFound('Anggota tidak ditemukan pada roster ini.');
        }
        return $m;
    }

    private function targetValue($raw, $label) {
        $raw = trim((string)$raw);
        if ($raw === '') return null;
        if (!preg_match('/^\d{1,3}$/', $raw)) {
            throw new InvalidArgumentException("Target $label harus angka 0-999 (kosongkan untuk tanpa target).");
        }
        return (int)$raw;
    }

    /** Atur target jumlah orang per hari untuk SIANG/MALAM sebuah section. Kosong = tanpa target (tanpa warna). */
    public function setSectionTargets($rosterId, $sectionId, $siang, $malam) {
        $this->requireManage();
        $roster  = $this->getEditableRoster($rosterId);
        $section = $this->getSectionOfRoster($roster['id'], $sectionId);
        $s = $this->targetValue($siang, 'SIANG');
        $m = $this->targetValue($malam, 'MALAM');
        $this->repo->updateSectionTargets($section['id'], $s, $m);
        $this->rlog($roster['id'], 'target', 'mengubah target section ' . $section['name'] . ': Siang ' . ($s === null ? '-' : $s) . ', Malam ' . ($m === null ? '-' : $m));
        return ['target_siang' => $s, 'target_malam' => $m];
    }

    public function deleteSection($rosterId, $sectionId) {
        $this->requireManage();
        $roster  = $this->getEditableRoster($rosterId);
        $section = $this->getSectionOfRoster($roster['id'], $sectionId);
        $n = $this->repo->countMembersInSection($section['id']);
        if ($n > 0) {
            throw new RosterV2Conflict("Section masih berisi $n anggota. Keluarkan/pindahkan anggotanya dulu.");
        }
        $this->repo->deleteSection($section['id']);
        $this->rlog($roster['id'], 'section', 'menghapus section ' . $section['name']);
    }

    /** false = addMember() tidak mendaftarkan anggota ke MONCU (dipakai duplikat roster saat opsi MONCU dimatikan). */
    public $autoMoncu = true;

    /** Diisi addMember(): true bila anggota itu baru saja didaftarkan otomatis ke MONCU. */
    public $lastMemberMoncuCreated = false;
    /** Diisi addMember(): jumlah sel putaran (D/N/OFF) yang ditulis dari Kode Off. */
    public $lastMemberRotationCells = 0;

    public function addMember($rosterId, $sectionId, $nrp, $unit = '', $kodeOff = '', $pattern = 'ROTASI') {
        $this->lastMemberMoncuCreated = false;
        $this->lastMemberRotationCells = 0;
        $this->requireManage();
        $roster  = $this->getEditableRoster($rosterId);
        $section = $this->getSectionOfRoster($roster['id'], $sectionId);
        $nrp = trim((string)$nrp);
        if ($nrp === '' || strlen($nrp) > 20) {
            throw new InvalidArgumentException('NRP tidak valid.');
        }
        $emp = $this->repo->findEmployeeByNrp($nrp);
        if (!$emp) {
            throw new InvalidArgumentException('NRP tidak ditemukan di data karyawan.');
        }
        $exist = $this->repo->findMemberByNrp($roster['id'], $nrp);
        if ($exist) {
            $sec = $this->repo->findSection($exist['section_id']);
            throw new InvalidArgumentException('NRP ' . $nrp . ' sudah menjadi anggota section ' . ($sec['name'] ?? '-') . '.');
        }
        $unit    = $this->cleanText($unit, 50);
        $kodeOff = RosterV2Rotation::normalize($kodeOff);
        $pattern = RosterV2Rotation::normalizePattern($pattern);
        if ($pattern === null) {
            throw new InvalidArgumentException('Pola kerja harus Siang-Malam atau Full Siang.');
        }
        if ($kodeOff === null) {
            throw new InvalidArgumentException('Kode Off harus A1-A14 atau B1-B14 (kosongkan bila jadwal diisi manual).');
        }
        $newMid = $this->repo->transaction(function () use ($roster, $section, $nrp, $emp, $unit, $kodeOff, $pattern) {
            $id = $this->repo->addMember([
                'roster_id' => $roster['id'], 'section_id' => $section['id'], 'nrp' => $nrp,
                'employee_name' => $emp['name'], 'jabatan' => $emp['jabatan'],
                'unit' => $unit !== '' ? $unit : ($emp['unit'] ?: null), 'kode_off' => $kodeOff !== '' ? $kodeOff : null,
                'work_pattern' => $pattern, 'actor' => $this->actor(),
            ]);
            // Otomatis daftar ke MONCU (bila tabelnya ada & NRP belum terdaftar); tidak menimpa data MONCU yang sudah ada.
            if ($this->autoMoncu && $this->repo->moncuTablesReady()) {
                $this->lastMemberMoncuCreated = $this->repo->ensureMoncuEmployee($nrp, $roster['roster_group'], $this->actor());
            }
            // Putaran dasar dari Kode Off untuk seluruh periode roster (berlanjut terus, tidak berhenti di data Excel).
            if ($kodeOff !== '') {
                $r = $this->fillRotation($roster, $nrp, $emp['name'], $emp['jabatan'], $kodeOff, $roster['period_start'], $roster['period_end'], $pattern);
                $this->lastMemberRotationCells = $r['written'];
            }
            return $id;
        });
        $this->rlog($roster['id'], 'anggota', 'menambahkan ke section ' . $section['name'] . ($kodeOff !== '' ? ' (Kode Off ' . $kodeOff . ')' : ''), $nrp, $emp['name']);
        return $newMid;
    }

    /**
     * Tulis putaran D/N/OFF dari Kode Off ke rentang tanggal. Sel yang sudah berisi kode lain (CT, ID, MCU, H, TR, ...)
     * TIDAK ditimpa. Return ['written' => sel ditulis/diubah, 'kept' => sel dipertahankan, 'unchanged' => sudah sama].
     */
    private function fillRotation(array $roster, $nrp, $name, $jabatan, $kode, $from, $to, $pattern = 'ROTASI') {
        $shift = [];
        foreach (['D', 'N', 'OFF'] as $c) {
            $s = $this->repo->findShiftByCode($c);
            if (!$s || !(int)$s['is_active']) throw new RosterV2Conflict("Kode shift $c belum ada / nonaktif di master shift Roster V2.");
            $shift[$c] = (int)$s['id'];
        }
        $existing = $this->repo->getCellCodesInRange($roster['id'], $from, $to)[$nrp] ?? [];
        $res = ['written' => 0, 'kept' => 0, 'unchanged' => 0];
        foreach (RosterV2Rotation::range($kode, $from, $to, $pattern) as $date => $code) {
            $cur = $existing[$date] ?? null;
            if ($cur !== null && !isset($shift[$cur])) { $res['kept']++; continue; }
            if ($cur === $code) { $res['unchanged']++; continue; }
            $this->repo->upsertDetail([
                'roster_id' => $roster['id'], 'nrp' => $nrp, 'employee_name' => $name, 'jabatan' => $jabatan,
                'work_date' => $date, 'shift_id' => $shift[$code], 'notes' => null, 'actor' => $this->actor(),
            ]);
            $res['written']++;
        }
        return $res;
    }

    /** Ubah Kode Off anggota; bila $apply, terapkan putaran ke rentang (tanpa menimpa CT/ID/MCU/H/TR yang sudah ada). */
    public function setMemberRotation($rosterId, $memberId, $kodeOff, $from, $to, $apply = true, $pattern = null) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $member = $this->getMemberOfRoster($roster['id'], $memberId);
        $kode = RosterV2Rotation::normalize($kodeOff);
        if ($kode === null) throw new InvalidArgumentException('Kode Off harus A1-A14 atau B1-B14.');
        $pat = $pattern === null ? (RosterV2Rotation::normalizePattern($member['work_pattern'] ?? '') ?: 'ROTASI') : RosterV2Rotation::normalizePattern($pattern);
        if ($pat === null) throw new InvalidArgumentException('Pola kerja harus Siang-Malam atau Full Siang.');
        if ($apply) {
            if ($kode === '') throw new InvalidArgumentException('Pilih Kode Off untuk menerapkan putaran.');
            $this->checkDateInPeriod($roster, $from);
            $this->checkDateInPeriod($roster, $to);
            if ($to < $from) throw new InvalidArgumentException('Tanggal akhir tidak boleh sebelum tanggal awal.');
        }
        $oldKode = (string)($member['kode_off'] ?? ''); $oldPat = RosterV2Rotation::normalizePattern($member['work_pattern'] ?? '') ?: 'ROTASI';
        $out = $this->repo->transaction(function () use ($roster, $member, $kode, $from, $to, $apply, $pat) {
            $this->repo->updateMemberRotation($member['id'], $kode !== '' ? $kode : null, $pat);
            $res = ['written' => 0, 'kept' => 0, 'unchanged' => 0];
            if ($apply) {
                $res = $this->fillRotation($roster, $member['nrp'], $member['employee_name'], $member['jabatan'], $kode, $from, $to, $pat);
            }
            return $res + ['kode_off' => $kode, 'work_pattern' => $pat];
        });
        $ch = [];
        if ($kode !== $oldKode) $ch[] = 'Kode Off ' . ($oldKode !== '' ? $oldKode : '-') . ' menjadi ' . ($kode !== '' ? $kode : '-');
        if ($pat !== $oldPat) $ch[] = 'pola kerja menjadi ' . ($pat === 'FULL_SIANG' ? 'Full Siang' : 'Siang-Malam');
        if ($apply) $ch[] = 'menerapkan putaran ' . $this->fmtDate($from) . ' s/d ' . $this->fmtDate($to) . ' (' . (int)$out['written'] . ' hari ditulis' . ((int)$out['kept'] ? ', ' . (int)$out['kept'] . ' dipertahankan' : '') . ')';
        if ($ch) $this->rlog($roster['id'], 'putaran', 'mengubah ' . implode('; ', $ch), $member['nrp'], $member['employee_name'] ?? null);
        return $out;
    }

    /** Ubah Unit anggota pada roster ini (teks bebas, maks 50 karakter; kosong = hapus). Return unit yang tersimpan. */
    public function setMemberUnit($rosterId, $memberId, $unit) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $member = $this->getMemberOfRoster($roster['id'], $memberId);
        $unit   = $this->cleanText($unit, 50);
        $this->repo->updateMemberUnit($member['id'], $unit === '' ? null : $unit);
        if ($unit !== (string)($member['unit'] ?? '')) $this->rlog($roster['id'], 'unit', 'mengubah unit dari ' . ((string)($member['unit'] ?? '') !== '' ? $member['unit'] : '-') . ' menjadi ' . ($unit !== '' ? $unit : '-'), $member['nrp'], $member['employee_name'] ?? null);
        return $unit;
    }

    /**
     * Duplikat roster ke tahun lain (bawaan: tahun berikutnya). Yang disalin: section (urutan + target), anggota (NRP, Unit, Kode Off,
     * pola kerja, urutan), PIC. Jadwal TIDAK disalin: dibuat ulang bersih dari putaran Kode Off (D/N/OFF, tanpa cuti/MCU/dll).
     * $registerMoncu: daftarkan anggota ke MONCU bila belum terdaftar. Roster baru berstatus draft.
     * Return ['id', 'sections', 'members', 'cells', 'moncu_new', 'no_kode', 'skipped' => ["NRP nama", ...]].
     */
    public function duplicateRoster($sourceId, $group, $year, $registerMoncu = true) {
        $this->requireManage();
        if (!$this->sectionsReady()) throw new RosterV2Conflict('Tabel section belum dibuat (jalankan scripts/migrate_roster_v2_sections.php).');
        $src = $this->getRosterOrFail($sourceId);
        $year = (int)$year;
        if ($year < 2000 || $year > 2100) throw new InvalidArgumentException('Tahun roster tujuan tidak valid.');
        $group = $this->cleanText($group, 50);
        if ($group === '') $group = $src['roster_group'];
        $prevAuto = $this->autoMoncu; $prevSilent = $this->logSilent;
        $this->autoMoncu = (bool)$registerMoncu;
        $this->logSilent = true;   // satu ringkasan di akhir, bukan satu baris per anggota
        try {
            $out = $this->repo->transaction(function () use ($src, $group, $year) {
                $newId = $this->createRoster([
                    'roster_group' => $group, 'year' => (string)$year,
                    'notes' => 'Duplikat dari ' . $src['name'],
                    'pic_name' => $src['pic_name'] ?? '', 'pic_jabatan' => $src['pic_jabatan'] ?? '',
                ]);
                $secMap = [];
                foreach ($this->repo->listSections($src['id']) as $sec) {
                    $nid = $this->addSection($newId, $sec['name']);
                    $this->repo->updateSectionTargets($nid, $sec['target_siang'], $sec['target_malam']);
                    $secMap[(int)$sec['id']] = $nid;
                }
                $res = ['id' => $newId, 'sections' => count($secMap), 'members' => 0, 'cells' => 0, 'moncu_new' => 0, 'no_kode' => 0, 'skipped' => []];
                foreach ($this->repo->listMembers($src['id']) as $m) {
                    if (!isset($secMap[(int)$m['section_id']])) continue;
                    try {
                        $this->addMember($newId, $secMap[(int)$m['section_id']], $m['nrp'], (string)$m['unit'], (string)$m['kode_off'], $m['work_pattern'] ?: 'ROTASI');
                    } catch (InvalidArgumentException $e) {
                        $res['skipped'][] = $m['nrp'] . ' ' . ($m['employee_name'] ?? '') . ' (' . $e->getMessage() . ')';
                        continue;
                    }
                    $res['members']++;
                    $res['cells'] += (int)$this->lastMemberRotationCells;
                    if ($this->lastMemberMoncuCreated) $res['moncu_new']++;
                    if ((string)$m['kode_off'] === '') $res['no_kode']++;
                }
                return $res;
            });
            $this->logSilent = $prevSilent;
            $this->rlog($out['id'], 'roster', 'membuat roster ini sebagai duplikat dari ' . $src['name'] . ' (' . substr($src['period_start'], 0, 4) . '): ' . $out['sections'] . ' section, ' . $out['members'] . ' anggota, jadwal dibuat ulang dari putaran Kode Off');
            $this->rlog($src['id'], 'roster', 'diduplikasi ke roster ' . $group . ' tahun ' . $year);
            return $out;
        } finally {
            $this->autoMoncu = $prevAuto;
            $this->logSilent = $prevSilent;
        }
    }

    /**
     * Pindahkan anggota ke section (boleh section yang sama untuk mengubah nomor urut) pada nomor tertentu.
     * $position 1-based pada daftar section tujuan; null = paling akhir. Anggota lain otomatis bergeser & dinomori ulang 1..n
     * (mis. nomor 1 -> 3: nomor 2 jadi 1, 3 jadi 2, dan yang tadi nomor 1 jadi 3).
     */
    public function moveMember($rosterId, $memberId, $sectionId, $position = null) {
        $this->requireManage();
        $roster  = $this->getEditableRoster($rosterId);
        $member  = $this->getMemberOfRoster($roster['id'], $memberId);
        $section = $this->getSectionOfRoster($roster['id'], $sectionId);
        $pos = null;
        if ($position !== null && trim((string)$position) !== '') {
            if (!preg_match('/^\d{1,4}$/', trim((string)$position)) || (int)trim((string)$position) < 1) {
                throw new InvalidArgumentException('Nomor urut harus bilangan bulat >= 1.');
            }
            $pos = (int)trim((string)$position);
        }
        $srcSec = $this->repo->findSection($member['section_id']);
        $mv = $this->repo->transaction(function () use ($member, $section, $pos) {
            $srcId  = (int)$member['section_id'];
            $others = array_values(array_filter($this->repo->listMemberIdsOfSection($section['id']), function ($i) use ($member) { return $i !== (int)$member['id']; }));
            if ($pos !== null && $pos > count($others) + 1) {
                throw new InvalidArgumentException('Nomor urut maksimal ' . (count($others) + 1) . ' untuk section ini.');
            }
            $idx = $pos === null ? count($others) : $pos - 1;
            array_splice($others, $idx, 0, [(int)$member['id']]);
            $this->repo->applySectionOrder($section['id'], $others);
            if ($srcId !== (int)$section['id']) {   // rapikan nomor di section asal
                $this->repo->applySectionOrder($srcId, $this->repo->listMemberIdsOfSection($srcId));
            }
            return ['section_id' => (int)$section['id'], 'position' => $idx + 1];
        });
        $same = (int)$member['section_id'] === (int)$section['id'];
        $this->rlog($roster['id'], 'anggota', $same
            ? 'mengubah nomor urut di section ' . $section['name'] . ' menjadi ' . $mv['position']
            : 'memindahkan dari section ' . ($srcSec['name'] ?? '-') . ' ke ' . $section['name'] . ' (nomor ' . $mv['position'] . ')', $member['nrp'], $member['employee_name'] ?? null);
        return $mv;
    }

    /** Keluarkan anggota dari roster: baris member DAN seluruh sel jadwalnya di roster ini dihapus. */
    public function removeMember($rosterId, $memberId) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $member = $this->getMemberOfRoster($roster['id'], $memberId);
        $cells = $this->repo->transaction(function () use ($roster, $member) {
            $cells = $this->repo->deleteDetailsRange($roster['id'], $member['nrp'], $roster['period_start'], $roster['period_end']);
            $this->repo->deleteMember($member['id']);
            return $cells;
        });
        $sec = $this->repo->findSection($member['section_id']);
        $this->rlog($roster['id'], 'anggota', 'mengeluarkan dari roster (section ' . ($sec['name'] ?? '-') . ', ' . (int)$cells . ' sel jadwal dihapus)', $member['nrp'], $member['employee_name'] ?? null);
        return $cells;
    }

    /** Isi/kosongkan satu sel matriks. shiftCode kosong = hapus sel. NRP wajib anggota roster. */
    public function setCell($rosterId, $nrp, $workDate, $shiftCode) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $nrp = trim((string)$nrp);
        $member = $this->repo->findMemberByNrp($roster['id'], $nrp);
        if (!$member) {
            throw new RosterV2NotFound('NRP belum menjadi anggota section pada roster ini.');
        }
        $this->checkDateInPeriod($roster, $workDate);
        $code = trim((string)$shiftCode);
        $prev = $this->prevCode($roster['id'], $nrp, $workDate);
        if ($code === '') {
            $this->repo->deleteDetail($roster['id'], $nrp, $workDate);
            if ($prev !== '') $this->rlog($roster['id'], 'jadwal', 'mengosongkan jadwal tanggal ' . $this->fmtDate($workDate) . ' (sebelumnya ' . $this->shiftText($prev) . ')', $nrp, $member['employee_name'] ?? null);
            return ['code' => ''];
        }
        $shift = $this->repo->findShiftByCode($code);
        if (!$shift || !(int)$shift['is_active']) {
            throw new InvalidArgumentException('Kode shift tidak dikenal atau nonaktif.');
        }
        $this->repo->upsertDetail([
            'roster_id' => $roster['id'], 'nrp' => $nrp,
            'employee_name' => $member['employee_name'], 'jabatan' => $member['jabatan'],
            'work_date' => $workDate, 'shift_id' => $shift['id'], 'notes' => null, 'actor' => $this->actor(),
        ]);
        if ($prev !== $shift['code']) {
            $this->rlog($roster['id'], 'jadwal', $prev === ''
                ? 'mengisi jadwal tanggal ' . $this->fmtDate($workDate) . ' dengan ' . $this->shiftText($shift['code'])
                : 'mengubah jadwal tanggal ' . $this->fmtDate($workDate) . ' dari ' . $this->shiftText($prev) . ' menjadi ' . $this->shiftText($shift['code']), $nrp, $member['employee_name'] ?? null);
        }
        return ['code' => $shift['code']];
    }

    /** Isi/kosongkan rentang tanggal untuk satu anggota (mis. seluruh bulan). Return jumlah tanggal diproses. */
    public function fillRange($rosterId, $nrp, $from, $to, $shiftCode) {
        $this->requireManage();
        $roster = $this->getEditableRoster($rosterId);
        $nrp = trim((string)$nrp);
        $member = $this->repo->findMemberByNrp($roster['id'], $nrp);
        if (!$member) {
            throw new RosterV2NotFound('NRP belum menjadi anggota section pada roster ini.');
        }
        $this->checkDateInPeriod($roster, $from);
        $this->checkDateInPeriod($roster, $to);
        if ($to < $from) {
            throw new InvalidArgumentException('Tanggal akhir tidak boleh sebelum tanggal awal.');
        }
        $code = trim((string)$shiftCode);
        $shift = null;
        if ($code !== '') {
            $shift = $this->repo->findShiftByCode($code);
            if (!$shift || !(int)$shift['is_active']) {
                throw new InvalidArgumentException('Kode shift tidak dikenal atau nonaktif.');
            }
        }
        $res = $this->repo->transaction(function () use ($roster, $member, $from, $to, $shift) {
            if (!$shift) {
                return $this->repo->deleteDetailsRange($roster['id'], $member['nrp'], $from, $to);
            }
            $n = 0;
            for ($d = new DateTime($from), $end = new DateTime($to); $d <= $end; $d->modify('+1 day')) {
                $this->repo->upsertDetail([
                    'roster_id' => $roster['id'], 'nrp' => $member['nrp'],
                    'employee_name' => $member['employee_name'], 'jabatan' => $member['jabatan'],
                    'work_date' => $d->format('Y-m-d'), 'shift_id' => $shift['id'], 'notes' => null, 'actor' => $this->actor(),
                ]);
                $n++;
            }
            return $n;
        });
        $range = $from === $to ? 'tanggal ' . $this->fmtDate($from) : $this->fmtDate($from) . ' s/d ' . $this->fmtDate($to);
        $this->rlog($roster['id'], 'jadwal', $shift
            ? 'mengisi jadwal ' . $range . ' dengan ' . $this->shiftText($shift['code']) . ' (' . (int)$res . ' hari)'
            : 'mengosongkan jadwal ' . $range . ' (' . (int)$res . ' sel dihapus)', $member['nrp'], $member['employee_name'] ?? null);
        return $res;
    }

    /**
     * Data tampilan matriks satu bulan (bulan dijepit ke periode roster).
     * Return: roster, month, months[], days[], sections[{id,name,members[{...,cells[tgl=>kode]}],summary[label=>[tgl=>n]],total}],
     *         loose (sel milik NRP yang belum jadi anggota section mana pun), shifts.
     */
    const MATRIX_CTX_DAYS = 10;   // jumlah hari konteks bulan sebelum/sesudahnya

    /** $ctx = ['prev' => bool, 'next' => bool]: tampilkan 10 hari terakhir bulan sebelumnya / 10 hari pertama bulan sesudahnya (hanya dalam periode roster). */
    public function getMatrix($rosterId, $month = null, array $ctx = []) {
        $this->requireView();
        $roster = $this->getRosterOrFail($rosterId);
        $ps = new DateTime($roster['period_start']);
        $pe = new DateTime($roster['period_end']);

        // Daftar bulan di periode
        $months = [];
        for ($m = new DateTime($ps->format('Y-m-01')); $m <= $pe; $m->modify('+1 month')) {
            $months[] = $m->format('Y-m');
        }
        $month = (string)$month;
        if (!preg_match('/^\d{4}-\d{2}$/', $month) || !in_array($month, $months, true)) {
            $now = date('Y-m');
            $month = in_array($now, $months, true) ? $now : $months[0];
        }
        $ms = new DateTime($month . '-01');
        $me = (clone $ms)->modify('last day of this month');
        $from = max($ms, $ps)->format('Y-m-d');
        $to   = min($me, $pe)->format('Y-m-d');

        // Hari konteks (hanya untuk dipantau): batas dijepit ke periode roster
        $cfrom = $from; $cto = $to; $nPrev = 0; $nNext = 0;
        if (!empty($ctx['prev'])) {
            $cfrom = max(new DateTime($from . ' -' . self::MATRIX_CTX_DAYS . ' days'), $ps)->format('Y-m-d');
            $nPrev = (int)(new DateTime($cfrom))->diff(new DateTime($from))->days;
        }
        if (!empty($ctx['next'])) {
            $cto = min(new DateTime($to . ' +' . self::MATRIX_CTX_DAYS . ' days'), $pe)->format('Y-m-d');
            $nNext = (int)(new DateTime($to))->diff(new DateTime($cto))->days;
        }
        $dow = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
        $days = [];
        for ($d = new DateTime($cfrom), $end = new DateTime($cto); $d <= $end; $d->modify('+1 day')) {
            $ymd = $d->format('Y-m-d');
            $days[] = ['date' => $ymd, 'd' => (int)$d->format('j'), 'dow' => (int)$d->format('w'), 'label' => $dow[(int)$d->format('w')],
                       'ctx' => $ymd < $from ? 'prev' : ($ymd > $to ? 'next' : ''), 'mon' => (int)$d->format('n')];
        }

        $cells    = $this->repo->getCellCodesInRange($roster['id'], $cfrom, $cto);
        $sections = [];
        foreach ($this->repo->listSections($roster['id']) as $s) {
            $sections[(int)$s['id']] = ['id' => (int)$s['id'], 'name' => $s['name'], 'members' => [],
                'targets' => ['SIANG' => $s['target_siang'] === null ? null : (int)$s['target_siang'],
                              'MALAM' => $s['target_malam'] === null ? null : (int)$s['target_malam']]];
        }
        $memberNrps = [];
        foreach ($this->repo->listMembers($roster['id']) as $mb) {
            $memberNrps[$mb['nrp']] = true;
            if (!isset($sections[(int)$mb['section_id']])) continue;
            $mb['cells'] = $cells[$mb['nrp']] ?? [];
            $sections[(int)$mb['section_id']]['members'][] = $mb;
        }

        foreach ($sections as &$sec) {
            $sec['total'] = count($sec['members']);
            $sec['summary'] = [];
            foreach (self::MATRIX_SUMMARY as $label => $code) {
                foreach ($days as $dy) {
                    $sec['summary'][$label][$dy['date']] = 0;
                }
            }
            foreach ($sec['members'] as $mb) {
                foreach ($mb['cells'] as $date => $code) {
                    $label = array_search($code, self::MATRIX_SUMMARY, true);
                    if ($label !== false) $sec['summary'][$label][$date]++;
                }
            }
        }
        unset($sec);

        $loose = [];
        foreach ($this->repo->listDetailNrps($roster['id']) as $r) {
            if (isset($memberNrps[$r['nrp']]) || empty($cells[$r['nrp']])) continue;
            $loose[] = ['nrp' => $r['nrp'], 'employee_name' => $r['nm'], 'jabatan' => $r['jb'], 'cells' => $cells[$r['nrp']]];
        }

        return [
            'roster' => $roster, 'month' => $month, 'months' => $months, 'days' => $days,
            'month_from' => $from, 'month_to' => $to, 'ctx' => ['prev' => $nPrev, 'next' => $nNext],
            'sections' => array_values($sections), 'loose' => $loose,
            'shifts' => $this->repo->listShifts(true),
            'summary_labels' => array_keys(self::MATRIX_SUMMARY),
        ];
    }

    /**
     * Ringkasan dashboard One-Roster untuk satu tanggal (roster PUBLISHED). Kategori: SIANG(D), MALAM(N), CUTI(CT), OFF, LAIN (kode lain), KOSONG (belum diisi).
     * Return ['date', 'jabatan_list', 'total' => [...], 'groups' => [nama => [...]]] ; tiap entri: orang, siang, malam, cuti, off, lain, kosong.
     */
    public function getDashboard($date, $jabatan = '') {
        $this->requireView();
        $d = DateTime::createFromFormat('!Y-m-d', (string)$date);
        if (!$d || $d->format('Y-m-d') !== $date) $date = date('Y-m-d');
        $blank = ['orang' => 0, 'siang' => 0, 'malam' => 0, 'cuti' => 0, 'off' => 0, 'lain' => 0, 'kosong' => 0];
        $rows = $this->repo->dashboardMembers($date);
        $jabs = [];
        foreach ($rows as $r) $jabs[$r['jabatan']] = true;
        ksort($jabs);
        $total = $blank; $groups = []; $sections = [];
        foreach ($rows as $r) {
            if ($jabatan !== '' && $r['jabatan'] !== $jabatan) continue;
            $g = $r['grup'] !== '' ? $r['grup'] : '(tanpa grup)';
            if (!isset($groups[$g])) $groups[$g] = $blank;
            $k = $r['kode'] === null ? 'kosong' : ($r['kode'] === 'D' ? 'siang' : ($r['kode'] === 'N' ? 'malam' : ($r['kode'] === 'CT' ? 'cuti' : ($r['kode'] === 'OFF' ? 'off' : 'lain'))));
            $groups[$g]['orang']++; $groups[$g][$k]++; $total['orang']++; $total[$k]++;
            $sk = $g . "\x1f" . ($r['section'] !== '' ? $r['section'] : '(tanpa section)');
            if (!isset($sections[$sk])) $sections[$sk] = $blank + ['grup' => $g, 'section' => $r['section'] !== '' ? $r['section'] : '(tanpa section)'];
            $sections[$sk]['orang']++; $sections[$sk][$k]++;
        }
        ksort($groups);
        return ['date' => $date, 'jabatan' => $jabatan, 'jabatan_list' => array_keys($jabs), 'total' => $total, 'groups' => $groups, 'sections' => array_values($sections)];
    }

    /**
     * Analisis Harian: jumlah orang SIANG (D) dan MALAM (N) per hari untuk tiap section, mulai $start sebanyak $days hari.
     * $rosterId = 0 -> SEMUA roster PUBLISHED yang beririsan dengan rentang; > 0 -> hanya roster itu.
     * Return ['start', 'days', 'dates', 'rosters' => [{id, roster_group, period_start, period_end}], 'roster_id',
     *         'groups' => [{roster_id, roster_group, sections => [{id, name, siang[], malam[], target_siang, target_malam}], total => ['siang'[], 'malam'[]]}],
     *         'total' => ['siang'[], 'malam'[]]  // gabungan semua roster yang tampil
     * ].
     */
    public function getAnalysis($start, $days = 25, $rosterId = 0) {
        $this->requireView();
        $d = DateTime::createFromFormat('!Y-m-d', (string)$start);
        if (!$d || $d->format('Y-m-d') !== $start) $start = date('Y-m-d');
        $days = max(7, min(62, (int)$days ?: 25));
        $dates = [];
        for ($x = new DateTime($start), $i = 0; $i < $days; $i++, $x->modify('+1 day')) $dates[] = $x->format('Y-m-d');
        $from = $dates[0]; $to = end($dates);
        $rosters = $this->repo->analysisRosters($from, $to);
        $ids = array_map(function ($r) { return (int)$r['id']; }, $rosters);
        $rosterId = in_array((int)$rosterId, $ids, true) ? (int)$rosterId : 0;
        $out = ['start' => $start, 'days' => $days, 'dates' => $dates, 'rosters' => $rosters, 'roster_id' => $rosterId, 'groups' => [],
                'total' => ['siang' => array_fill(0, $days, 0), 'malam' => array_fill(0, $days, 0)]];
        foreach ($rosters as $r) {
            if ($rosterId && (int)$r['id'] !== $rosterId) continue;
            $rid = (int)$r['id'];
            $counts = $this->repo->analysisCounts($rid, $from, $to);
            $ovr = $this->repo->analysisOverrides($rid);
            $g = ['roster_id' => $rid, 'roster_group' => $r['roster_group'], 'sections' => [],
                  'total' => ['siang' => array_fill(0, $days, 0), 'malam' => array_fill(0, $days, 0)]];
            foreach ($this->repo->analysisSections($rid) as $sec) {
                $sid = (int)$sec['id']; $si = []; $ma = [];
                foreach ($dates as $i => $dt) {
                    $dv = $counts[$sid][$dt]['D'] ?? 0; $nv = $counts[$sid][$dt]['N'] ?? 0;
                    $si[] = $dv; $ma[] = $nv;
                    $g['total']['siang'][$i] += $dv; $g['total']['malam'][$i] += $nv;
                    $out['total']['siang'][$i] += $dv; $out['total']['malam'][$i] += $nv;
                }
                $rs = $sec['target_siang'] === null ? null : (int)$sec['target_siang'];   // target di roster (tab Matriks)
                $rm = $sec['target_malam'] === null ? null : (int)$sec['target_malam'];
                $os = $ovr[$sid]['SIANG'] ?? null; $om = $ovr[$sid]['MALAM'] ?? null;     // override manual Analisis Harian
                $g['sections'][] = ['id' => $sid, 'name' => $sec['name'], 'siang' => $si, 'malam' => $ma,
                    'target_siang' => $os !== null ? $os : $rs, 'target_malam' => $om !== null ? $om : $rm,
                    'roster_siang' => $rs, 'roster_malam' => $rm, 'manual_siang' => $os !== null, 'manual_malam' => $om !== null];
            }
            $out['groups'][] = $g;
        }
        return $out;
    }
    /**
     * Atur target MANUAL Analisis Harian satu section (tidak mengubah target di roster / tab Matriks).
     * Kosong = hapus override dan ikuti target roster lagi. Return nilai efektif + status manual.
     */
    public function setAnalysisTarget($rosterId, $sectionId, $siang, $malam) {
        $this->requireManage();
        $roster  = $this->getRosterOrFail($rosterId);
        $section = $this->getSectionOfRoster($roster['id'], $sectionId);
        if (!$this->repo->analysisTargetsReady()) {
            throw new RosterV2Conflict('Tabel target analisis belum dibuat. Jalankan php scripts/migrate_analysis_targets.php di server.');
        }
        $vals = ['SIANG' => $this->targetValue($siang, 'SIANG'), 'MALAM' => $this->targetValue($malam, 'MALAM')];
        $this->repo->transaction(function () use ($roster, $section, $vals) {
            foreach ($vals as $label => $v) $this->repo->saveAnalysisOverride($roster['id'], $section['id'], $label, $v, $this->actor());
        });
        $this->rlog($roster['id'], 'target', 'mengubah target analisis section ' . $section['name'] . ': Siang ' . ($vals['SIANG'] === null ? 'ikut roster' : $vals['SIANG']) . ', Malam ' . ($vals['MALAM'] === null ? 'ikut roster' : $vals['MALAM']));
        return ['manual_siang' => $vals['SIANG'] !== null, 'manual_malam' => $vals['MALAM'] !== null,
                'target_siang' => $vals['SIANG'] ?? ($section['target_siang'] === null ? null : (int)$section['target_siang']),
                'target_malam' => $vals['MALAM'] ?? ($section['target_malam'] === null ? null : (int)$section['target_malam'])];
    }

    // ---- Upload Roster: alur sama dengan spreadsheet (ROSTER -> Input Roster -> 2026 -> upload) ----
    // Langkah 1 (Input Roster): kode Roster V2 disederhanakan ke satu huruf.
    const UPLOAD_SRC_MAP  = ['CT' => 'C', 'ID' => 'D', 'TR' => 'T', 'IDS' => 'D', 'MCU' => 'H', 'FU' => 'H'];
    // Langkah 2 (sheet 2026): huruf -> Unit. Selain itu kode = huruf + Pos (mis. D + PO = DPO); OFF = OOFF.
    const UPLOAD_UNIT_MAP = ['OFF' => 'OFF', 'C' => 'CUTI', 'T' => 'TRAINING', 'I' => 'IJIN', 'R' => 'IJIN RESMI', 'A' => 'ABSEN', 'S' => 'SAKIT', 'K' => 'KARANTINA'];
    const UPLOAD_DAYS     = 62;   // batas maksimum rentang upload (hari)
    private static $hariNama = [1 => 'SENIN', 2 => 'SELASA', 3 => 'RABU', 4 => 'KAMIS', 5 => 'JUMAT', 6 => 'SABTU', 7 => 'MINGGU'];

    /** Konversi satu kode shift Roster V2: ['data' => 'C', 'kode' => 'CCUTI', 'head' => 'C', 'unit' => 'CUTI']. */
    public static function uploadConvert($shiftCode, $pos = 'PO') {
        $code = strtoupper(trim((string)$shiftCode));
        $data = self::UPLOAD_SRC_MAP[$code] ?? $code;
        $unit = self::UPLOAD_UNIT_MAP[$data] ?? $pos;
        $kode = ($data === 'OFF' ? 'O' : $data) . $unit;
        return ['data' => $data, 'kode' => $kode, 'head' => $kode[0], 'unit' => substr($kode, 1)];
    }

    /**
     * Langkah 3 (sheet upload): Status = huruf pertama kode + jenis hari, dihitung dari HARI OFF orang itu ($offWeekday, ISO 1..7):
     * hari OFF = hari ke-7 (L), sehari sebelumnya = hari ke-6 (E), sisanya R. Untuk OFF hari Minggu: Senin-Jumat R, Sabtu E, Minggu L.
     * 'days' = urutan hari (1..7) dalam pekan yang berakhir di hari OFF (sama dengan kolom K di sheet upload).
     */
    public static function uploadStatus($kodeHead, $ymd, $offWeekday = 7) {
        $n   = (int)date('N', strtotime($ymd));
        $idx = ($n - (int)$offWeekday + 6) % 7 + 1;
        return ['status' => $kodeHead . ($idx === 7 ? 'L' : ($idx === 6 ? 'E' : 'R')), 'days' => $idx];
    }

    /** Hari OFF acuan menurut Kode Off ("Acuan Hari-7"): A1/B1=Sabtu, A2/B2=Minggu, A3=Senin ... A7=Jumat, A8=Sabtu (mengulang tiap 7). ISO 1..7 atau null. */
    public static function uploadOffWeekday($kodeOff) {
        $c = RosterV2Rotation::normalize($kodeOff);
        if ($c === null || $c === '') return null;
        return (((int)substr($c, 1)) + 4) % 7 + 1;
    }

    /**
     * Data 3 langkah untuk satu roster. $opt: month (YYYY-MM), start & end (YYYY-MM-DD; bawaan 1 s/d akhir bulan itu, maks 62 hari), pos (bawaan PO),
     * nrps (teks NRP dipisah spasi/koma/baris; kosong = semua anggota).
     */
    public function getUploadData($rosterId, array $opt = []) {
        $this->requireView();
        $roster = $this->getRosterOrFail($rosterId);
        $ps = $roster['period_start']; $pe = $roster['period_end'];
        $months = [];
        for ($m = new DateTime(substr($ps, 0, 7) . '-01'); $m->format('Y-m-d') <= $pe; $m->modify('+1 month')) $months[] = $m->format('Y-m');
        $month = (string)($opt['month'] ?? '');
        if (!in_array($month, $months, true)) { $now = date('Y-m'); $month = in_array($now, $months, true) ? $now : $months[0]; }
        $start = (string)($opt['start'] ?? '');
        $sd = DateTime::createFromFormat('!Y-m-d', $start);
        if (!$sd || $sd->format('Y-m-d') !== $start || $start < $ps || $start > $pe) $start = max($month . '-01', $ps);
        $end = (string)($opt['end'] ?? '');
        $ed  = DateTime::createFromFormat('!Y-m-d', $end);
        if (!$ed || $ed->format('Y-m-d') !== $end || $end < $start) $end = date('Y-m-t', strtotime($start));
        if ($end > $pe) $end = $pe;
        $pos = strtoupper(trim((string)($opt['pos'] ?? '')));
        $pos = substr(preg_replace('/[^A-Z0-9 ]/', '', $pos), 0, 10);
        if ($pos === '') $pos = 'PO';
        $filter = [];
        foreach (preg_split('/[\s,;]+/', (string)($opt['nrps'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $n) $filter[$n] = true;

        $dates = [];
        for ($d = new DateTime($start), $i = 0; $i < self::UPLOAD_DAYS && $d->format('Y-m-d') <= $end; $d->modify('+1 day'), $i++) $dates[] = $d->format('Y-m-d');
        $cells = $this->repo->getCellCodesInRange($roster['id'], $dates[0], end($dates));

        $people = []; $seen = [];
        $members = $this->repo->listMembers($roster['id']);
        foreach ($this->repo->listSections($roster['id']) as $sec) {
            foreach ($members as $mb) {
                if ((int)$mb['section_id'] !== (int)$sec['id']) continue;
                $people[] = ['nrp' => $mb['nrp'], 'nama' => $mb['employee_name'] ?? '', 'kode_off' => (string)$mb['kode_off'], 'section' => $sec['name']];
                $seen[$mb['nrp']] = true;
            }
        }
        foreach ($this->repo->listDetailNrps($roster['id']) as $r) {
            if (isset($seen[$r['nrp']]) || empty($cells[$r['nrp']])) continue;
            $people[] = ['nrp' => $r['nrp'], 'nama' => $r['nm'] ?? '', 'kode_off' => '', 'section' => ''];
        }

        $out = []; $rows = []; $bad = 0; $no = 0;
        foreach ($people as $p) {
            if ($filter && !isset($filter[$p['nrp']])) continue;
            $no++;
            $raw = $cells[$p['nrp']] ?? [];
            $data = []; $kode = []; $firstOff = null;
            $offDays = [];   // jumlah OFF per hari-pekan (ISO) pada rentang
            foreach ($dates as $dt) { if (($raw[$dt] ?? '') === 'OFF') { $n = (int)date('N', strtotime($dt)); $offDays[$n] = ($offDays[$n] ?? 0) + 1; } }
            $acuan  = self::uploadOffWeekday($p['kode_off']);
            $aktual = $offDays ? (int)array_search(max($offDays), $offDays) : null;   // hari OFF yang paling sering
            $cek    = ($acuan === null || !$offDays) ? null : isset($offDays[$acuan]);
            // Hari ke-7 (L) = hari OFF menurut Kode Off. OFF di hari lain (mis. OFF tambahan) tetap O + R/E, bukan OL.
            // Tanpa Kode Off: pakai hari OFF yang paling sering; tanpa OFF sama sekali: Minggu.
            $w = $acuan ?? $aktual ?? 7;
            foreach ($dates as $i => $dt) {
                $code = $raw[$dt] ?? '';
                if ($code === '') { $data[$dt] = ''; $kode[$dt] = ''; continue; }
                $c = self::uploadConvert($code, $pos);
                $data[$dt] = $c['data']; $kode[$dt] = $c['kode'];
                $st = self::uploadStatus($c['head'], $dt, $w);
                $rows[] = ['no' => $no, 'nrp' => $p['nrp'], 'nama' => $p['nama'], 'idx' => $i + 1, 'tanggal' => $dt, 'status' => $st['status'],
                           'unit' => $c['unit'], 'lookup' => $c['kode'], 'days' => $st['days']];
            }
            if ($cek === false) $bad++;
            $out[] = $p + ['no' => $no, 'data' => $data, 'kode' => $kode,
                           'acuan' => $acuan ? self::$hariNama[$acuan] : '', 'aktual' => $aktual ? self::$hariNama[$aktual] : '', 'cek' => $cek];
        }
        return ['roster' => $roster, 'month' => $month, 'months' => $months, 'start' => $start, 'end' => $end, 'pos' => $pos, 'dates' => $dates,
                'people' => $out, 'rows' => $rows, 'bad' => $bad, 'nrps' => (string)($opt['nrps'] ?? '')];
    }
}
