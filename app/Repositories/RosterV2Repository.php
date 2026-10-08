<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

//
// Akses data Roster V2. WRITE hanya ke tabel v2_roster*.
// Tabel existing (employees dll.) hanya di-SELECT.
// Semua query pakai prepared statement; nama tabel hardcode, tidak dari input.

class RosterV2Repository
{
    private $db;

    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
    }

    /** Jalankan $fn dalam satu transaksi; rollback bila melempar. */
    public function transaction(callable $fn) {
        if ($this->db->inTransaction()) {
            return $fn(); // sudah dalam transaksi pemanggil (batas commit/rollback ada di pemanggil)
        }
        $this->db->beginTransaction();
        try {
            $r = $fn();
            $this->db->commit();
            return $r;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    // ---------------------------------------------------------------- Shifts

    public function listShifts($onlyActive = true) {
        $sql = "SELECT * FROM v2_roster_shifts"
             . ($onlyActive ? " WHERE is_active = 1" : "")
             . " ORDER BY sort_order, code";
        return $this->db->query($sql)->fetchAll();
    }

    public function findShiftByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_shifts WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public function findShiftById($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_shifts WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function upsertShift(array $s) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_shifts (code, label, category, start_time, end_time, color, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                label = VALUES(label), category = VALUES(category),
                start_time = VALUES(start_time), end_time = VALUES(end_time),
                color = VALUES(color), sort_order = VALUES(sort_order),
                is_active = VALUES(is_active)"
        );
        $stmt->execute([
            $s['code'], $s['label'], $s['category'],
            $s['start_time'] ?? null, $s['end_time'] ?? null, $s['color'] ?? null,
            (int)($s['sort_order'] ?? 0), (int)($s['is_active'] ?? 1),
        ]);
    }

    // --------------------------------------------------------------- Rosters

    private static $picCols = null;

    /** Kolom pic_name / pic_jabatan sudah ada di v2_rosters? (di-cache per request) */
    public function hasPicColumns() {
        if (self::$picCols === null) {
            $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'v2_rosters' AND COLUMN_NAME IN ('pic_name','pic_jabatan')");
            self::$picCols = ((int)$stmt->fetchColumn() === 2);
        }
        return self::$picCols;
    }

    private static $visCol = null;

    /** Kolom visible_months sudah ada di v2_rosters? (di-cache per request) */
    public function hasVisibleMonthsColumn() {
        if (self::$visCol === null) {
            $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'v2_rosters' AND COLUMN_NAME = 'visible_months'");
            self::$visCol = ((int)$stmt->fetchColumn() === 1);
        }
        return self::$visCol;
    }

    /** Simpan bulan yang dibuka (CSV 'YYYY-MM,...' atau null). */
    public function setVisibleMonths($id, $csv) {
        $stmt = $this->db->prepare("UPDATE v2_rosters SET visible_months = ? WHERE id = ?");
        $stmt->execute([$csv, (int)$id]);
        return $stmt->rowCount();
    }

    public function createRoster(array $r) {
        $pic = $this->hasPicColumns();
        $stmt = $this->db->prepare(
            "INSERT INTO v2_rosters (name, roster_group, period_start, period_end, status, notes, created_by, updated_by" . ($pic ? ", pic_name, pic_jabatan" : "") . ")
             VALUES (?, ?, ?, ?, ?, ?, ?, ?" . ($pic ? ", ?, ?" : "") . ")"
        );
        $args = [
            $r['name'], $r['roster_group'], $r['period_start'], $r['period_end'],
            $r['status'], $r['notes'] ?? null, $r['actor'], $r['actor'],
        ];
        if ($pic) { $args[] = $r['pic_name'] ?? null; $args[] = $r['pic_jabatan'] ?? null; }
        $stmt->execute($args);
        return (int)$this->db->lastInsertId();
    }

    public function findRoster($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_rosters WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function findRosterByPeriod($group, $start, $end) {
        $stmt = $this->db->prepare(
            "SELECT * FROM v2_rosters WHERE roster_group = ? AND period_start = ? AND period_end = ?"
        );
        $stmt->execute([$group, $start, $end]);
        return $stmt->fetch() ?: null;
    }

    /** Filter opsional: status, group, date (roster yang mencakup tanggal itu). */
    public function listRosters(array $f = [], $limit = 100, $offset = 0) {
        $where = [];
        $args  = [];
        if (!empty($f['status']))       { $where[] = "status = ?";       $args[] = $f['status']; }
        if (isset($f['roster_group']))  { $where[] = "roster_group = ?"; $args[] = $f['roster_group']; }
        if (!empty($f['date']))         { $where[] = "? BETWEEN period_start AND period_end"; $args[] = $f['date']; }
        // Rentang filter: roster yang periodenya beririsan dengan [date_from, date_to]
        if (!empty($f['date_from']))    { $where[] = "period_end >= ?";   $args[] = $f['date_from']; }
        if (!empty($f['date_to']))      { $where[] = "period_start <= ?"; $args[] = $f['date_to']; }
        $sql = "SELECT * FROM v2_rosters"
             . ($where ? " WHERE " . implode(" AND ", $where) : "")
             . " ORDER BY period_start DESC, id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    /** Jumlah roster per status + total (untuk summary card). */
    public function countRostersByStatus() {
        $out = ['total' => 0, 'draft' => 0, 'published' => 0, 'archived' => 0];
        foreach ($this->db->query("SELECT status, COUNT(*) c FROM v2_rosters GROUP BY status") as $r) {
            $out['total'] += (int)$r['c'];
            if (isset($out[$r['status']])) $out[$r['status']] = (int)$r['c'];
        }
        return $out;
    }

    public function listRosterGroups() {
        return $this->db->query(
            "SELECT DISTINCT roster_group FROM v2_rosters WHERE roster_group <> '' ORDER BY roster_group"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateRosterHeader($id, array $r) {
        $pic = $this->hasPicColumns() && array_key_exists('pic_name', $r);
        $stmt = $this->db->prepare(
            "UPDATE v2_rosters SET name = ?, roster_group = ?, period_start = ?, period_end = ?, notes = ?, updated_by = ?" . ($pic ? ", pic_name = ?, pic_jabatan = ?" : "") . "
             WHERE id = ?"
        );
        $args = [$r['name'], $r['roster_group'], $r['period_start'], $r['period_end'], $r['notes'], $r['actor']];
        if ($pic) { $args[] = $r['pic_name']; $args[] = $r['pic_jabatan'] ?? null; }
        $args[] = (int)$id;
        $stmt->execute($args);
        return $stmt->rowCount();
    }

    public function updateRosterStatus($id, $status, $actor) {
        $stmt = $this->db->prepare("UPDATE v2_rosters SET status = ?, updated_by = ? WHERE id = ?");
        $stmt->execute([$status, $actor, (int)$id]);
        return $stmt->rowCount();
    }

    // --------------------------------------------------------------- Details

    /** Set/replace shift sebuah sel (roster, nrp, tanggal). Idempotent. */
    public function upsertDetail(array $d) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_details
                (roster_id, nrp, employee_name, jabatan, work_date, shift_id, notes, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                shift_id = VALUES(shift_id), employee_name = VALUES(employee_name),
                jabatan = VALUES(jabatan), notes = VALUES(notes), updated_by = VALUES(updated_by)"
        );
        $stmt->execute([
            (int)$d['roster_id'], $d['nrp'], $d['employee_name'] ?? null, $d['jabatan'] ?? null,
            $d['work_date'], (int)$d['shift_id'], $d['notes'] ?? null, $d['actor'], $d['actor'],
        ]);
    }

    /**
     * Upsert banyak sel sekaligus (multi-row, per 400 baris). $rows: [nrp, employee_name, jabatan, work_date, shift_id].
     * Untuk impor massal; satu statement per potongan sehingga jauh lebih cepat & log aktivitas tidak membengkak.
     */
    public function bulkUpsertDetails($rosterId, array $rows, $actor) {
        foreach (array_chunk($rows, 400) as $chunk) {
            $ph = []; $args = [];
            foreach ($chunk as $r) {
                $ph[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
                array_push($args, (int)$rosterId, $r['nrp'], $r['employee_name'] ?? null, $r['jabatan'] ?? null, $r['work_date'], (int)$r['shift_id'], $actor, $actor);
            }
            $stmt = $this->db->prepare(
                "INSERT INTO v2_roster_details (roster_id, nrp, employee_name, jabatan, work_date, shift_id, created_by, updated_by)
                 VALUES " . implode(', ', $ph) . "
                 ON DUPLICATE KEY UPDATE shift_id = VALUES(shift_id), employee_name = VALUES(employee_name),
                    jabatan = VALUES(jabatan), updated_by = VALUES(updated_by)"
            );
            $stmt->execute($args);
        }
    }

    /** INSERT murni (bukan upsert): bentrok unique key melempar PDOException. Return id. */
    public function insertDetail(array $d) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_details
                (roster_id, nrp, employee_name, jabatan, work_date, shift_id, notes, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$d['roster_id'], $d['nrp'], $d['employee_name'] ?? null, $d['jabatan'] ?? null,
            $d['work_date'], (int)$d['shift_id'], $d['notes'] ?? null, $d['actor'], $d['actor'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Ubah tanggal & shift sebuah detail. Snapshot employee TIDAK disentuh. */
    public function updateDetailFields($id, $workDate, $shiftId, $actor) {
        $stmt = $this->db->prepare(
            "UPDATE v2_roster_details SET work_date = ?, shift_id = ?, updated_by = ? WHERE id = ?"
        );
        $stmt->execute([$workDate, (int)$shiftId, $actor, (int)$id]);
        return $stmt->rowCount();
    }

    /** Set kunci 'nrp|tanggal' semua sel roster (untuk deteksi duplikat massal). */
    public function existingCellKeys($rosterId) {
        $stmt = $this->db->prepare("SELECT nrp, work_date FROM v2_roster_details WHERE roster_id = ?");
        $stmt->execute([(int)$rosterId]);
        $keys = [];
        foreach ($stmt as $r) { $keys[$r['nrp'] . '|' . $r['work_date']] = true; }
        return $keys;
    }

    public function findDetail($rosterId, $nrp, $workDate) {
        $stmt = $this->db->prepare(
            "SELECT * FROM v2_roster_details WHERE roster_id = ? AND nrp = ? AND work_date = ?"
        );
        $stmt->execute([(int)$rosterId, $nrp, $workDate]);
        return $stmt->fetch() ?: null;
    }

    public function deleteDetail($rosterId, $nrp, $workDate) {
        $stmt = $this->db->prepare(
            "DELETE FROM v2_roster_details WHERE roster_id = ? AND nrp = ? AND work_date = ?"
        );
        $stmt->execute([(int)$rosterId, $nrp, $workDate]);
        return $stmt->rowCount();
    }

    /** Semua sel satu roster, sudah join kode shift. Opsional filter nrp. */
    public function getDetails($rosterId, $nrp = null, $limit = null, $offset = 0) {
        $sql = "SELECT d.*, s.code AS shift_code, s.label AS shift_label, s.category AS shift_category
                FROM v2_roster_details d
                JOIN v2_roster_shifts s ON s.id = d.shift_id
                WHERE d.roster_id = ?";
        $args = [(int)$rosterId];
        if ($nrp !== null) { $sql .= " AND d.nrp = ?"; $args[] = $nrp; }
        $sql .= " ORDER BY d.nrp, d.work_date, d.id";
        if ($limit !== null) { $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function countDetails($rosterId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM v2_roster_details WHERE roster_id = ?");
        $stmt->execute([(int)$rosterId]);
        return (int)$stmt->fetchColumn();
    }

    /** [min, max] work_date sel milik roster, atau null jika kosong. */
    public function detailDateRange($rosterId) {
        $stmt = $this->db->prepare(
            "SELECT MIN(work_date) mn, MAX(work_date) mx FROM v2_roster_details WHERE roster_id = ?"
        );
        $stmt->execute([(int)$rosterId]);
        $r = $stmt->fetch();
        return ($r && $r['mn'] !== null) ? [$r['mn'], $r['mx']] : null;
    }

    public function findDetailById($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_details WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------- Allocations

    public function findAllocation($detailId, $unit, $section) {
        $stmt = $this->db->prepare(
            "SELECT * FROM v2_roster_allocations WHERE detail_id = ? AND unit = ? AND section = ?"
        );
        $stmt->execute([(int)$detailId, $unit, $section]);
        return $stmt->fetch() ?: null;
    }

    public function addAllocation($detailId, $unit, $section, $notes, $actor) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_allocations (detail_id, unit, section, notes, created_by)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE notes = VALUES(notes)"
        );
        $stmt->execute([(int)$detailId, $unit, $section, $notes, $actor]);
    }

    public function listAllocationsByDetail($detailId) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_allocations WHERE detail_id = ? ORDER BY id");
        $stmt->execute([(int)$detailId]);
        return $stmt->fetchAll();
    }

    public function updateAllocation($id, $unit, $section) {
        $stmt = $this->db->prepare("UPDATE v2_roster_allocations SET unit = ?, section = ? WHERE id = ?");
        $stmt->execute([$unit, $section, (int)$id]);
        return $stmt->rowCount();
    }

    public function deleteAllocationById($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_roster_allocations WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->rowCount();
    }

    public function removeAllocation($detailId, $unit, $section) {
        $stmt = $this->db->prepare(
            "DELETE FROM v2_roster_allocations WHERE detail_id = ? AND unit = ? AND section = ?"
        );
        $stmt->execute([(int)$detailId, $unit, $section]);
        return $stmt->rowCount();
    }

    public function getAllocationsByRoster($rosterId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, d.nrp, d.work_date
             FROM v2_roster_allocations a
             JOIN v2_roster_details d ON d.id = a.detail_id
             WHERE d.roster_id = ?
             ORDER BY d.work_date, d.nrp, a.unit"
        );
        $stmt->execute([(int)$rosterId]);
        return $stmt->fetchAll();
    }

    // ------------------------------------------- Employee (existing, SELECT only)

    public function findEmployeeByNrp($nrp) {
        $stmt = $this->db->prepare(
            "SELECT nrp, name, COALESCE(NULLIF(jabatan_roster,''), jabatan_ss6) AS jabatan,
                    unit, status_karyawan
             FROM employees WHERE nrp = ? ORDER BY id LIMIT 1"
        );
        $stmt->execute([$nrp]);
        return $stmt->fetch() ?: null;
    }

    /** Lookup banyak NRP sekaligus (chunk 500). Return [nrp => row] (baris pertama per NRP). */
    public function findEmployeesByNrps(array $nrps) {
        $out = [];
        foreach (array_chunk(array_values(array_unique($nrps)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $this->db->prepare(
                "SELECT nrp, name, COALESCE(NULLIF(jabatan_roster,''), jabatan_ss6) AS jabatan
                 FROM employees WHERE nrp IN ($in) ORDER BY id"
            );
            $stmt->execute($chunk);
            foreach ($stmt as $r) {
                if (!isset($out[$r['nrp']])) $out[$r['nrp']] = $r;
            }
        }
        return $out;
    }

    /** Cari karyawan: NRP prefix atau nama mengandung term. Selalu LIMIT. */
    public function searchEmployees($term, $limit = 20) {
        $esc  = addcslashes($term, '%_\\');
        $like = '%' . $esc . '%';
        $stmt = $this->db->prepare(
            "SELECT nrp, name, COALESCE(NULLIF(jabatan_roster,''), jabatan_ss6) AS jabatan,
                    unit, status_karyawan
             FROM employees
             WHERE nrp LIKE ? OR name LIKE ?
             ORDER BY name LIMIT " . (int)$limit
        );
        $stmt->execute([$esc . '%', $like]); // NRP: prefix; nama: contains
        return $stmt->fetchAll();
    }


    // ------------------------------------------------ Section & anggota (matriks)

    public function sectionTablesReady() {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME IN ('v2_roster_sections','v2_roster_members')"
        );
        return (int)$stmt->fetchColumn() === 2;
    }

    public function listSections($rosterId) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_sections WHERE roster_id = ? ORDER BY sort_order, id");
        $stmt->execute([(int)$rosterId]);
        return $stmt->fetchAll();
    }

    public function findSection($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_sections WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function findSectionByName($rosterId, $name) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_sections WHERE roster_id = ? AND name = ?");
        $stmt->execute([(int)$rosterId, $name]);
        return $stmt->fetch() ?: null;
    }

    public function createSection($rosterId, $name, $actor) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_sections (roster_id, name, sort_order, created_by)
             VALUES (?, ?, (SELECT COALESCE(MAX(s.sort_order), 0) + 1 FROM v2_roster_sections s WHERE s.roster_id = ?), ?)"
        );
        $stmt->execute([(int)$rosterId, $name, (int)$rosterId, $actor]);
        return (int)$this->db->lastInsertId();
    }

    public function updateSectionTargets($id, $siang, $malam) {
        $stmt = $this->db->prepare("UPDATE v2_roster_sections SET target_siang = ?, target_malam = ? WHERE id = ?");
        $stmt->execute([$siang, $malam, (int)$id]);
        return $stmt->rowCount();
    }

    public function deleteSection($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_roster_sections WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->rowCount();
    }

    public function countMembersInSection($sectionId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM v2_roster_members WHERE section_id = ?");
        $stmt->execute([(int)$sectionId]);
        return (int)$stmt->fetchColumn();
    }

    public function listMembers($rosterId) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_members WHERE roster_id = ? ORDER BY section_id, sort_order, id");
        $stmt->execute([(int)$rosterId]);
        return $stmt->fetchAll();
    }

    public function findMemberById($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_members WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function findMemberByNrp($rosterId, $nrp) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_members WHERE roster_id = ? AND nrp = ?");
        $stmt->execute([(int)$rosterId, $nrp]);
        return $stmt->fetch() ?: null;
    }

    public function addMember(array $m) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_members (roster_id, section_id, nrp, employee_name, jabatan, unit, kode_off, work_pattern, sort_order, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?,
                (SELECT COALESCE(MAX(x.sort_order), 0) + 1 FROM v2_roster_members x WHERE x.section_id = ?), ?)"
        );
        $stmt->execute([
            (int)$m['roster_id'], (int)$m['section_id'], $m['nrp'], $m['employee_name'] ?? null, $m['jabatan'] ?? null,
            $m['unit'] ?? null, $m['kode_off'] ?? null, $m['work_pattern'] ?? 'ROTASI', (int)$m['section_id'], $m['actor'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** ID anggota satu section, urut tampilan (sort_order, id). */
    public function listMemberIdsOfSection($sectionId) {
        $stmt = $this->db->prepare("SELECT id FROM v2_roster_members WHERE section_id = ? ORDER BY sort_order, id");
        $stmt->execute([(int)$sectionId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Tetapkan urutan 1..n anggota pada sebuah section (sekaligus memindahkan ke section itu bila perlu). */
    public function applySectionOrder($sectionId, array $ids) {
        $stmt = $this->db->prepare("UPDATE v2_roster_members SET section_id = ?, sort_order = ? WHERE id = ?");
        foreach (array_values($ids) as $i => $id) $stmt->execute([(int)$sectionId, $i + 1, (int)$id]);
    }

    public function updateMemberSection($id, $sectionId) {
        $stmt = $this->db->prepare(
            "UPDATE v2_roster_members SET section_id = ?,
                sort_order = (SELECT COALESCE(MAX(x.sort_order), 0) + 1 FROM (SELECT sort_order FROM v2_roster_members WHERE section_id = ?) x)
             WHERE id = ?"
        );
        $stmt->execute([(int)$sectionId, (int)$sectionId, (int)$id]);
        return $stmt->rowCount();
    }

    public function updateMemberRotation($id, $kodeOff, $pattern) {
        $stmt = $this->db->prepare("UPDATE v2_roster_members SET kode_off = ?, work_pattern = ? WHERE id = ?");
        $stmt->execute([$kodeOff, $pattern, (int)$id]);
        return $stmt->rowCount();
    }

    public function updateMemberUnit($id, $unit) {
        $stmt = $this->db->prepare("UPDATE v2_roster_members SET unit = ? WHERE id = ?");
        $stmt->execute([$unit, (int)$id]);
        return $stmt->rowCount();
    }

    public function updateMemberInfo($id, $unit, $kodeOff) {
        $stmt = $this->db->prepare("UPDATE v2_roster_members SET unit = ?, kode_off = ? WHERE id = ?");
        $stmt->execute([$unit, $kodeOff, (int)$id]);
        return $stmt->rowCount();
    }

    public function deleteMember($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_roster_members WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->rowCount();
    }

    public function moncuTablesReady() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME IN ('v2_moncu_employees','v2_moncu_audit')");
        return (int)$stmt->fetchColumn() === 2;
    }

    /**
     * Daftarkan NRP ke MONCU bila belum ada (Hiring Date kosong; grup = roster group). Tidak menimpa data MONCU yang sudah ada.
     * Return true bila baris baru dibuat.
     */
    public function ensureMoncuEmployee($nrp, $group, $actor) {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO v2_moncu_employees (nrp, hiring_date, group_name, created_by, updated_by) VALUES (?, NULL, ?, ?, ?)"
        );
        $stmt->execute([$nrp, $group !== '' ? $group : null, $actor, $actor]);
        if ($stmt->rowCount() < 1) return false;
        $aud = $this->db->prepare("INSERT INTO v2_moncu_audit (nrp, action, note, actor) VALUES (?, 'employee_add', ?, ?)");
        $aud->execute([$nrp, 'otomatis dari section Roster V2; hiring date belum diisi', $actor]);
        return true;
    }

    // ---------------------------------------- Monitoring Roster Produksi (baca dari Roster V2 PUBLISHED)

    private function monitoringRange($year, $month) {
        $from = sprintf('%04d-%02d-01', (int)$year, (int)$month);
        return [$from, date('Y-m-t', strtotime($from))];
    }

    /** Grup roster (kategori) yang punya roster PUBLISHED beririsan dengan bulan itu. */
    public function monitoringGroups($year, $month) {
        [$from, $to] = $this->monitoringRange($year, $month);
        $stmt = $this->db->prepare(
            "SELECT DISTINCT r.roster_group FROM v2_rosters r
             WHERE r.status = 'published' AND r.roster_group <> '' AND r.period_start <= ? AND r.period_end >= ?
             ORDER BY r.roster_group"
        );
        $stmt->execute([$to, $from]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Jabatan anggota (snapshot pada sel) roster PUBLISHED pada bulan itu, opsional dibatasi grup roster. */
    public function monitoringJabatan($year, $month, $group = '') {
        [$from, $to] = $this->monitoringRange($year, $month);
        $sql = "SELECT DISTINCT d.jabatan FROM v2_roster_details d JOIN v2_rosters r ON r.id = d.roster_id
                WHERE r.status = 'published' AND d.work_date BETWEEN ? AND ? AND d.jabatan IS NOT NULL AND d.jabatan <> ''";
        $args = [$from, $to];
        if ($group !== '') { $sql .= " AND r.roster_group = ?"; $args[] = $group; }
        $stmt = $this->db->prepare($sql . " ORDER BY d.jabatan");
        $stmt->execute($args);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Sel satu bulan dari roster PUBLISHED: [{nrp, nama, jabatan, tanggal, kode, kategori, section}], urut
     * grup roster -> urutan section -> nomor urut anggota. NRP yang ada di >1 roster published: roster ber-id lebih besar menimpa.
     */
    public function monitoringCells($year, $month, $group = '', $jabatan = '') {
        [$from, $to] = $this->monitoringRange($year, $month);
        $sql = "SELECT d.nrp, d.employee_name AS nama, COALESCE(NULLIF(d.jabatan, ''), '-') AS jabatan, d.work_date AS tanggal, s.code AS kode,
                       r.roster_group AS kategori, COALESCE(sec.name, '') AS section, r.id AS roster_id,
                       COALESCE(sec.sort_order, 9999) AS so, COALESCE(m.sort_order, 9999) AS mo
                FROM v2_roster_details d
                JOIN v2_rosters r ON r.id = d.roster_id
                JOIN v2_roster_shifts s ON s.id = d.shift_id
                LEFT JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
                LEFT JOIN v2_roster_sections sec ON sec.id = m.section_id
                WHERE r.status = 'published' AND d.work_date BETWEEN ? AND ?";
        $args = [$from, $to];
        if ($group !== '') { $sql .= " AND r.roster_group = ?"; $args[] = $group; }
        if ($jabatan !== '') { $sql .= " AND d.jabatan = ?"; $args[] = $jabatan; }
        $sql .= " ORDER BY r.roster_group, so, mo, d.nrp, r.id, d.work_date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    // ---------------------------------------- Dashboard One-Roster (baca dari Roster V2 PUBLISHED)

    /**
     * Anggota roster PUBLISHED yang periodenya mencakup $date beserta kode shift pada tanggal itu (null = belum diisi):
     * [{nrp, nama, jabatan, grup, roster_id, section, kode}]. NRP yang ada di >1 roster published dihitung sekali (roster ber-id terbesar).
     */
    public function dashboardMembers($date) {
        $stmt = $this->db->prepare(
            "SELECT m.nrp, m.employee_name AS nama, COALESCE(NULLIF(m.jabatan, ''), '-') AS jabatan, r.roster_group AS grup, r.id AS roster_id, COALESCE(sec.name, '') AS section,
                    (SELECT s.code FROM v2_roster_details d JOIN v2_roster_shifts s ON s.id = d.shift_id
                      WHERE d.roster_id = r.id AND d.nrp = m.nrp AND d.work_date = ? LIMIT 1) AS kode
             FROM v2_roster_members m JOIN v2_rosters r ON r.id = m.roster_id
             LEFT JOIN v2_roster_sections sec ON sec.id = m.section_id
             WHERE r.status = 'published' AND r.period_start <= ? AND r.period_end >= ?
             ORDER BY r.roster_group, sec.sort_order, r.id, m.nrp"
        );
        $stmt->execute([$date, $date, $date]);
        $out = [];
        foreach ($stmt as $row) { $out[$row['nrp']] = $row; }   // id roster lebih besar menimpa
        return array_values($out);
    }

    // ---------------------------------------- Analisis Harian (jumlah orang siang/malam per section per hari, roster PUBLISHED)

    /** Roster PUBLISHED yang periodenya beririsan dengan rentang: [{id, roster_group, period_start, period_end}]. */
    public function analysisRosters($from, $to) {
        $stmt = $this->db->prepare(
            "SELECT id, roster_group, period_start, period_end FROM v2_rosters
             WHERE status = 'published' AND period_start <= ? AND period_end >= ? ORDER BY roster_group, id"
        );
        $stmt->execute([$to, $from]);
        return $stmt->fetchAll();
    }

    /** Section roster tertentu dengan target siang/malam, urut tampilan. */
    public function analysisSections($rosterId) {
        $stmt = $this->db->prepare("SELECT id, name, target_siang, target_malam FROM v2_roster_sections WHERE roster_id = ? ORDER BY sort_order, id");
        $stmt->execute([(int)$rosterId]);
        return $stmt->fetchAll();
    }

    /** Jumlah orang per section per tanggal untuk kode D dan N: [section_id][tanggal]['D'|'N'] = jumlah. Hanya anggota section. */
    public function analysisCounts($rosterId, $from, $to) {
        $stmt = $this->db->prepare(
            "SELECT m.section_id, d.work_date, s.code, COUNT(*) AS n
             FROM v2_roster_details d
             JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
             JOIN v2_roster_shifts s ON s.id = d.shift_id
             WHERE d.roster_id = ? AND d.work_date BETWEEN ? AND ? AND s.code IN ('D', 'N')
             GROUP BY m.section_id, d.work_date, s.code"
        );
        $stmt->execute([(int)$rosterId, $from, $to]);
        $out = [];
        foreach ($stmt as $r) { $out[(int)$r['section_id']][$r['work_date']][$r['code']] = (int)$r['n']; }
        return $out;
    }

    /** Hash password akun (tabel users) untuk verifikasi ulang; null bila akun tidak ada di tabel users. Hanya SELECT. */
    public function findUserPasswordHash($nrp) {
        $stmt = $this->db->prepare("SELECT password FROM users WHERE username = ? ORDER BY id LIMIT 1");
        $stmt->execute([$nrp]);
        $row = $stmt->fetch();
        return $row === false ? null : (string)$row['password'];
    }

    // ---------------------------------------- Riwayat perubahan roster (v2_roster_changelog)

    private $changelogReady = null;

    public function changelogReady() {
        if ($this->changelogReady === null) {
            $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'v2_roster_changelog'");
            $this->changelogReady = (int)$stmt->fetchColumn() === 1;
        }
        return $this->changelogReady;
    }

    public function addChangelog(array $r) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_changelog (roster_id, actor_nrp, actor_name, action, target_nrp, target_name, summary) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([(int)$r['roster_id'], $r['actor_nrp'], $r['actor_name'], $r['action'], $r['target_nrp'], $r['target_name'], $r['summary']]);
        if (mt_rand(1, 30) === 1) {   // pembersihan ringan: riwayat > 14 hari dibuang (yang ditampilkan hanya 3 hari terakhir)
            $this->db->exec("DELETE FROM v2_roster_changelog WHERE created_at < (NOW() - INTERVAL 14 DAY)");
        }
    }

    /** Riwayat satu roster sejak $since (Y-m-d H:i:s), terbaru dulu. */
    public function listChangelog($rosterId, $since, $limit = 300) {
        $stmt = $this->db->prepare(
            "SELECT id, actor_nrp, actor_name, action, target_nrp, target_name, summary, created_at FROM v2_roster_changelog
             WHERE roster_id = ? AND created_at >= ? ORDER BY created_at DESC, id DESC LIMIT " . (int)$limit
        );
        $stmt->execute([(int)$rosterId, $since]);
        return $stmt->fetchAll();
    }

    public function countChangelog($rosterId, $since) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM v2_roster_changelog WHERE roster_id = ? AND created_at >= ?");
        $stmt->execute([(int)$rosterId, $since]);
        return (int)$stmt->fetchColumn();
    }

    /** Tabel override target Analisis Harian sudah dibuat? (scripts/migrate_analysis_targets.php) */
    public function analysisTargetsReady() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'v2_analysis_targets'");
        return (int)$stmt->fetchColumn() === 1;
    }

    /** Override target manual satu roster: [section_id]['SIANG'|'MALAM'] = angka. Tanpa tabel -> []. */
    public function analysisOverrides($rosterId) {
        if (!$this->analysisTargetsReady()) return [];
        $stmt = $this->db->prepare("SELECT section_id, shift_label, target FROM v2_analysis_targets WHERE roster_id = ?");
        $stmt->execute([(int)$rosterId]);
        $out = [];
        foreach ($stmt as $r) { $out[(int)$r['section_id']][$r['shift_label']] = (int)$r['target']; }
        return $out;
    }

    /** $target null = hapus override (ikuti roster lagi). */
    public function saveAnalysisOverride($rosterId, $sectionId, $label, $target, $actor) {
        if ($target === null) {
            $stmt = $this->db->prepare("DELETE FROM v2_analysis_targets WHERE section_id = ? AND shift_label = ?");
            $stmt->execute([(int)$sectionId, $label]);
            return;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO v2_analysis_targets (roster_id, section_id, shift_label, target, updated_by) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE target = VALUES(target), updated_by = VALUES(updated_by), roster_id = VALUES(roster_id)"
        );
        $stmt->execute([(int)$rosterId, (int)$sectionId, $label, (int)$target, $actor]);
    }

    // ---------------------------------------- Share Roster (roster pribadi per karyawan dari Roster V2 PUBLISHED)

    /** Karyawan yang punya sel pada roster PUBLISHED: [{nrp, nama, section}] untuk pencarian. */
    public function shareEmployees() {
        return $this->db->query(
            "SELECT d.nrp, MAX(d.employee_name) AS nama, COALESCE(MAX(sec.name), '') AS section
             FROM v2_roster_details d
             JOIN v2_rosters r ON r.id = d.roster_id AND r.status = 'published'
             LEFT JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
             LEFT JOIN v2_roster_sections sec ON sec.id = m.section_id
             GROUP BY d.nrp ORDER BY nama"
        )->fetchAll();
    }

    /**
     * Bulan (1-12) pada tahun itu yang DIBUKA PIC untuk karyawan ini: gabungan visible_months roster published tempat ia punya jadwal.
     */
    public function shareVisibleMonths($nrp, $year) {
        if (!$this->hasVisibleMonthsColumn()) return [];
        $stmt = $this->db->prepare(
            "SELECT DISTINCT r.visible_months FROM v2_rosters r
             WHERE r.status = 'published' AND r.visible_months IS NOT NULL AND r.visible_months <> ''
               AND EXISTS (SELECT 1 FROM v2_roster_details d WHERE d.roster_id = r.id AND d.nrp = ?)"
        );
        $stmt->execute([(string)$nrp]);
        $months = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $csv) {
            foreach (explode(',', $csv) as $ym) {
                if (preg_match('/^(\d{4})-(\d{2})$/', trim($ym), $m) && (int)$m[1] === (int)$year) $months[(int)$m[2]] = true;
            }
        }
        $out = array_keys($months); sort($out);
        return $out;
    }

    /** Profil karyawan untuk lembar roster (read-only, sama dengan halaman Roster lama: jabatan = jabatan_ss6). */
    public function shareEmployeeInfo($nrp) {
        $stmt = $this->db->prepare("SELECT nrp, name AS nama, jabatan_ss6 AS jabatan, unit FROM employees WHERE nrp = ? ORDER BY id LIMIT 1");
        $stmt->execute([(string)$nrp]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Roster satu karyawan pada satu bulan dari roster PUBLISHED.
     * Return ['cells' => [hari => kode], 'unit' => ?, 'nama' => ?, 'jabatan' => ?, 'grup' => ?]; NRP di >1 roster published: id lebih besar menimpa.
     */
    public function shareMonth($nrp, $year, $month, $onlyVisible = false) {
        $from = sprintf('%04d-%02d-01', (int)$year, (int)$month);
        $to = date('Y-m-t', strtotime($from));
        $stmt = $this->db->prepare(
            "SELECT d.work_date, s.code, d.employee_name, d.jabatan, r.roster_group, m.unit,
                    " . ($this->hasPicColumns() ? "r.pic_name, r.pic_jabatan" : "NULL AS pic_name, NULL AS pic_jabatan") . "
             FROM v2_roster_details d
             JOIN v2_rosters r ON r.id = d.roster_id AND r.status = 'published'
             JOIN v2_roster_shifts s ON s.id = d.shift_id
             LEFT JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
             WHERE d.nrp = ? AND d.work_date BETWEEN ? AND ?" . ($onlyVisible ? "
               AND " . ($this->hasVisibleMonthsColumn() ? "FIND_IN_SET(?, r.visible_months) > 0" : "1 = 0") : "") . "
             ORDER BY r.id, d.work_date"
        );
        $args = [(string)$nrp, $from, $to];
        if ($onlyVisible && $this->hasVisibleMonthsColumn()) $args[] = sprintf('%04d-%02d', (int)$year, (int)$month);
        $stmt->execute($args);
        $out = ['cells' => [], 'unit' => null, 'nama' => null, 'jabatan' => null, 'grup' => null, 'pic_name' => null, 'pic_jabatan' => null];
        foreach ($stmt as $r) {
            $out['cells'][(int)date('j', strtotime($r['work_date']))] = $r['code'];
            if (!empty($r['unit'])) $out['unit'] = $r['unit'];
            $out['nama'] = $r['employee_name']; $out['jabatan'] = $r['jabatan']; $out['grup'] = $r['roster_group'];
            if (!empty($r['pic_name'])) { $out['pic_name'] = $r['pic_name']; $out['pic_jabatan'] = $r['pic_jabatan']; }
        }
        return $out;
    }

    /** Sel roster pada rentang tanggal: [nrp][tanggal] => kode shift. */
    public function getCellCodesInRange($rosterId, $from, $to) {
        $stmt = $this->db->prepare(
            "SELECT d.nrp, d.work_date, s.code
             FROM v2_roster_details d JOIN v2_roster_shifts s ON s.id = d.shift_id
             WHERE d.roster_id = ? AND d.work_date BETWEEN ? AND ?"
        );
        $stmt->execute([(int)$rosterId, $from, $to]);
        $out = [];
        foreach ($stmt as $r) { $out[$r['nrp']][$r['work_date']] = $r['code']; }
        return $out;
    }

    /** NRP yang punya sel di roster (semua tanggal). */
    public function listDetailNrps($rosterId) {
        $stmt = $this->db->prepare(
            "SELECT nrp, MAX(employee_name) nm, MAX(jabatan) jb FROM v2_roster_details WHERE roster_id = ? GROUP BY nrp ORDER BY nrp"
        );
        $stmt->execute([(int)$rosterId]);
        return $stmt->fetchAll();
    }

    /** Hapus sel NRP pada rentang tanggal (alokasi ikut terhapus via FK). Return jumlah baris. */
    public function deleteDetailsRange($rosterId, $nrp, $from, $to) {
        $stmt = $this->db->prepare(
            "DELETE FROM v2_roster_details WHERE roster_id = ? AND nrp = ? AND work_date BETWEEN ? AND ?"
        );
        $stmt->execute([(int)$rosterId, $nrp, $from, $to]);
        return $stmt->rowCount();
    }
}
