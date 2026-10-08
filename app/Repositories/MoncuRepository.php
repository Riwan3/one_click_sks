<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

// Port dari One-Click V1 (PDO + SQL V1 dipertahankan). Prepared statement; nama tabel/kolom hardcode.
class MoncuRepository {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? DB::connection()->getPdo();
    }

    public function transaction(callable $fn) {
        if ($this->db->inTransaction()) {
            return $fn(); // sudah dalam transaksi pemanggil
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

    // -------------------------------------------------------------- Employees

    /** Karyawan MONCU + data jabatan/nama dari employees (SELECT). */
    public function findByEmployee($nrp) {
        $stmt = $this->db->prepare(
            "SELECT m.*, e.name AS employee_name, e.jabatan_ss6, e.jabatan_roster, e.unit AS employee_unit
             FROM v2_moncu_employees m
             LEFT JOIN employees e ON e.nrp = m.nrp COLLATE utf8mb4_unicode_ci
             WHERE m.nrp = ? ORDER BY e.id LIMIT 1"
        );
        $stmt->execute([$nrp]);
        return $stmt->fetch() ?: null;
    }

    public function createEmployee(array $e) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_moncu_employees (nrp, hiring_date, group_name, plan_interval_days, notes, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$e['nrp'], $e['hiring_date'], $e['group_name'], $e['plan_interval_days'], $e['notes'], $e['actor'], $e['actor']]);
        return (int)$this->db->lastInsertId();
    }

    public function updateEmployee($nrp, array $e) {
        $stmt = $this->db->prepare(
            "UPDATE v2_moncu_employees
             SET hiring_date = ?, group_name = ?, plan_interval_days = ?, notes = ?, is_active = ?, updated_by = ?
             WHERE nrp = ?"
        );
        $stmt->execute([$e['hiring_date'], $e['group_name'], $e['plan_interval_days'], $e['notes'], (int)$e['is_active'], $e['actor'], $nrp]);
        return $stmt->rowCount();
    }

    /** Daftar karyawan MONCU (filter: q = NRP/nama, group, active). */
    public function listEmployees(array $f, $limit, $offset) {
        [$where, $args] = $this->employeeWhere($f);
        $stmt = $this->db->prepare(
            "SELECT m.*, e.name AS employee_name, e.jabatan_ss6, e.jabatan_roster
             FROM v2_moncu_employees m
             LEFT JOIN (SELECT nrp, MIN(id) AS mid FROM employees GROUP BY nrp) x ON x.nrp = m.nrp COLLATE utf8mb4_unicode_ci
             LEFT JOIN employees e ON e.id = x.mid
             $where
             ORDER BY m.group_name, e.name, m.nrp
             LIMIT " . (int)$limit . " OFFSET " . (int)$offset
        );
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function countEmployees(array $f) {
        [$where, $args] = $this->employeeWhere($f);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM v2_moncu_employees m
             LEFT JOIN (SELECT nrp, MIN(id) AS mid FROM employees GROUP BY nrp) x ON x.nrp = m.nrp COLLATE utf8mb4_unicode_ci
             LEFT JOIN employees e ON e.id = x.mid
             $where"
        );
        $stmt->execute($args);
        return (int)$stmt->fetchColumn();
    }

    private function employeeWhere(array $f) {
        $w = []; $a = [];
        if (isset($f['active'])) { $w[] = 'm.is_active = ?'; $a[] = (int)$f['active']; }
        if (!empty($f['group'])) { $w[] = 'm.group_name = ?'; $a[] = $f['group']; }
        if (!empty($f['section']) && $this->sectionTablesExist()) {
            $w[] = "EXISTS (SELECT 1 FROM v2_roster_members rm JOIN v2_roster_sections rs ON rs.id = rm.section_id
                       JOIN v2_rosters r ON r.id = rm.roster_id
                       WHERE rm.nrp = m.nrp AND r.status <> 'archived' AND rs.name = ?)";
            $a[] = $f['section'];
        }
        if (!empty($f['q'])) {
            $like = '%' . addcslashes($f['q'], '%_\\') . '%';
            $w[] = '(m.nrp LIKE ? OR e.name LIKE ?)';
            $a[] = $like; $a[] = $like;
        }
        return [$w ? 'WHERE ' . implode(' AND ', $w) : '', $a];
    }

    /** Tabel section/anggota Roster V2 sudah dibuat? (MONCU tetap jalan tanpa keduanya.) */
    public function sectionTablesExist() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME IN ('v2_roster_sections','v2_roster_members')");
        return (int)$stmt->fetchColumn() === 2;
    }

    /** Nama section yang dipakai roster non-arsip (untuk filter). */
    public function listSectionNames() {
        if (!$this->sectionTablesExist()) return [];
        return $this->db->query(
            "SELECT DISTINCT s.name FROM v2_roster_sections s JOIN v2_rosters r ON r.id = s.roster_id
             WHERE r.status <> 'archived' ORDER BY s.name"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Keanggotaan roster/section per NRP (roster non-arsip): [nrp => [[roster_id, roster_group, period_start, period_end, status, section]]]. */
    public function rosterSectionsForNrps(array $nrps) {
        $out = [];
        if (!$nrps || !$this->sectionTablesExist()) return $out;
        $in = implode(',', array_fill(0, count($nrps), '?'));
        $stmt = $this->db->prepare(
            "SELECT rm.nrp, r.id AS roster_id, r.roster_group, r.period_start, r.period_end, r.status, s.name AS section
             FROM v2_roster_members rm
             JOIN v2_rosters r ON r.id = rm.roster_id
             JOIN v2_roster_sections s ON s.id = rm.section_id
             WHERE rm.nrp IN ($in) AND r.status <> 'archived'
             ORDER BY r.period_start DESC, r.id DESC"
        );
        $stmt->execute(array_values(array_map('strval', $nrps)));
        foreach ($stmt as $r) { $out[(string)$r['nrp']][] = $r; }
        return $out;
    }

    public function listGroups() {
        return $this->db->query(
            "SELECT DISTINCT group_name FROM v2_moncu_employees WHERE group_name IS NOT NULL AND group_name <> '' ORDER BY group_name"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    // ---------------------------------------------------------------- Periods

    public function findByPeriod($nrp, $year, $periodNo) {
        $stmt = $this->db->prepare("SELECT * FROM v2_moncu_periods WHERE nrp = ? AND cycle_year = ? AND period_no = ?");
        $stmt->execute([$nrp, (int)$year, (int)$periodNo]);
        return $stmt->fetch() ?: null;
    }

    /** Semua periode satu karyawan pada satu tahun, urut periode. */
    public function findByYear($nrp, $year) {
        $stmt = $this->db->prepare("SELECT * FROM v2_moncu_periods WHERE nrp = ? AND cycle_year = ? ORDER BY period_no");
        $stmt->execute([$nrp, (int)$year]);
        return $stmt->fetchAll();
    }

    /** Periode banyak karyawan sekaligus untuk satu tahun: [nrp => [period_no => row]]. */
    public function findByYearForMany(array $nrps, $year) {
        $out = [];
        foreach (array_chunk(array_values(array_unique($nrps)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $this->db->prepare(
                "SELECT * FROM v2_moncu_periods WHERE cycle_year IN (?, ?) AND nrp IN ($in)"
            );
            $stmt->execute(array_merge([(int)$year, (int)$year - 1], $chunk));
            foreach ($stmt as $r) { $out[$r['nrp']][(int)$r['cycle_year']][(int)$r['period_no']] = $r; }
        }
        return $out;
    }

    /** Semua periode karyawan (semua tahun) - untuk cek tumpang tindih & sinkron roster. */
    public function findByEmployeeAll($nrp) {
        $stmt = $this->db->prepare("SELECT * FROM v2_moncu_periods WHERE nrp = ? ORDER BY cycle_year, period_no");
        $stmt->execute([$nrp]);
        return $stmt->fetchAll();
    }

    /** Periode lain yang rentang [awal, akhir]-nya beririsan dengan rentang baru. */
    public function findOverlap($nrp, $awal, $akhir, $excludeId = 0) {
        $stmt = $this->db->prepare(
            "SELECT * FROM v2_moncu_periods
             WHERE nrp = ? AND id <> ? AND awal IS NOT NULL AND akhir IS NOT NULL AND awal <= ? AND akhir >= ?
             LIMIT 1"
        );
        $stmt->execute([$nrp, (int)$excludeId, $akhir, $awal]);
        return $stmt->fetch() ?: null;
    }

    public function createPeriod(array $p) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_moncu_periods
                (nrp, cycle_year, period_no, awal, tahunan, izin_resmi, akhir, induksi, plan, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $p['nrp'], (int)$p['cycle_year'], (int)$p['period_no'], $p['awal'], $p['tahunan'], $p['izin_resmi'],
            $p['akhir'], $p['induksi'], $p['plan'], $p['actor'], $p['actor'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updatePeriod($id, array $p) {
        $stmt = $this->db->prepare(
            "UPDATE v2_moncu_periods
             SET awal = ?, tahunan = ?, izin_resmi = ?, akhir = ?, induksi = ?, plan = ?, updated_by = ?
             WHERE id = ?"
        );
        $stmt->execute([$p['awal'], $p['tahunan'], $p['izin_resmi'], $p['akhir'], $p['induksi'], $p['plan'], $p['actor'], (int)$id]);
        return $stmt->rowCount();
    }

    /** Periode terakhir yang sudah punya PLAN (acuan monitoring berikutnya). */
    public function deletePeriod($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_moncu_periods WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->rowCount();
    }

    /** Jumlah sel CT/ID hasil sinkron periode ini yang berada di roster TERKUNCI (archived). */
    public function countLockedLinksOfPeriod($periodId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM v2_moncu_roster_links l
             JOIN v2_roster_details d ON d.id = l.detail_id
             JOIN v2_rosters r ON r.id = d.roster_id
             WHERE l.period_id = ? AND r.status = 'archived'"
        );
        $stmt->execute([(int)$periodId]);
        return (int)$stmt->fetchColumn();
    }

    public function getLatestPeriod($nrp) {
        $stmt = $this->db->prepare(
            "SELECT * FROM v2_moncu_periods WHERE nrp = ? AND plan IS NOT NULL ORDER BY plan DESC, id DESC LIMIT 1"
        );
        $stmt->execute([$nrp]);
        return $stmt->fetch() ?: null;
    }

    /** Total TAHUNAN aktual (hanya periode yang sudah punya AWAL) pada satu tahun. */
    public function getAnnualUsage($nrp, $year) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(GREATEST(tahunan, 0)), 0) FROM v2_moncu_periods WHERE nrp = ? AND cycle_year = ? AND awal IS NOT NULL"
        );
        $stmt->execute([$nrp, (int)$year]);
        return (int)$stmt->fetchColumn();
    }

    // ------------------------------------------------------------------ Audit

    public function addAudit(array $a) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_moncu_audit
                (nrp, cycle_year, period_no, action, old_awal, new_awal, old_tahunan, new_tahunan,
                 old_izin_resmi, new_izin_resmi, new_akhir, new_induksi, new_plan, note, actor)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $a['nrp'], $a['cycle_year'] ?? null, $a['period_no'] ?? null, $a['action'],
            $a['old_awal'] ?? null, $a['new_awal'] ?? null, $a['old_tahunan'] ?? null, $a['new_tahunan'] ?? null,
            $a['old_izin_resmi'] ?? null, $a['new_izin_resmi'] ?? null,
            $a['new_akhir'] ?? null, $a['new_induksi'] ?? null, $a['new_plan'] ?? null,
            $a['note'] ?? null, $a['actor'] ?? null,
        ]);
    }

    public function listAudit($nrp, $limit = 50) {
        $stmt = $this->db->prepare("SELECT * FROM v2_moncu_audit WHERE nrp = ? ORDER BY id DESC LIMIT " . (int)$limit);
        $stmt->execute([$nrp]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------- Sinkron ke Roster V2 (v2_*)

    public function findShiftByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_shifts WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public function findRoster($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_rosters WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    /** Roster AKTIF (draft & published; opsional dibatasi id) yang memuat NRP sebagai anggota (punya >=1 detail). $status 'active' = bukan archived. */
    public function listMemberRosters($nrp, $status = 'active', $rosterId = null) {
        $sql = "SELECT r.* FROM v2_rosters r
                WHERE r.status <> 'archived' AND EXISTS (SELECT 1 FROM v2_roster_details d WHERE d.roster_id = r.id AND d.nrp = ?)";
        $args = [$nrp];
        if ($rosterId !== null) { $sql .= " AND r.id = ?"; $args[] = (int)$rosterId; }
        $stmt = $this->db->prepare($sql . " ORDER BY r.period_start");
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    /** Roster TERKUNCI (archived) tempat NRP menjadi anggota dan periodenya beririsan rentang. */
    public function countLockedMemberRosters($nrp, $from, $to) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM v2_rosters r
             WHERE r.status = 'archived' AND r.period_start <= ? AND r.period_end >= ?
               AND EXISTS (SELECT 1 FROM v2_roster_details d WHERE d.roster_id = r.id AND d.nrp = ?)"
        );
        $stmt->execute([$to, $from, $nrp]);
        return (int)$stmt->fetchColumn();
    }

    /** Roster AKTIF (draft & published) yang periodenya beririsan dengan rentang tanggal (siapa pun anggotanya). */
    public function listDraftRostersOverlapping($from, $to) {
        $stmt = $this->db->prepare(
            "SELECT * FROM v2_rosters WHERE status <> 'archived' AND period_start <= ? AND period_end >= ? ORDER BY period_start"
        );
        $stmt->execute([$to, $from]);
        return $stmt->fetchAll();
    }

    public function countAllocations($detailId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM v2_roster_allocations WHERE detail_id = ?");
        $stmt->execute([(int)$detailId]);
        return (int)$stmt->fetchColumn();
    }

    /** NRP karyawan MONCU aktif yang punya cuti (AWAL..INDUKSI) beririsan dengan roster tetapi BUKAN anggota roster. */
    public function listMoncuLeaveNotInRoster($rosterId, $from, $to) {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT m.nrp FROM v2_moncu_employees m
             JOIN v2_moncu_periods p ON p.nrp = m.nrp AND p.awal IS NOT NULL AND p.awal <= ? AND COALESCE(p.induksi, p.akhir) >= ?
             WHERE m.is_active = 1
               AND NOT EXISTS (SELECT 1 FROM v2_roster_details d WHERE d.roster_id = ? AND d.nrp = m.nrp)
             ORDER BY m.nrp"
        );
        $stmt->execute([$to, $from, (int)$rosterId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** AWAL paling awal milik karyawan (untuk validasi perubahan hiring date). */
    public function getEarliestAwal($nrp) {
        $stmt = $this->db->prepare("SELECT MIN(awal) FROM v2_moncu_periods WHERE nrp = ? AND awal IS NOT NULL");
        $stmt->execute([$nrp]);
        $v = $stmt->fetchColumn();
        return $v ?: null;
    }

    /** NRP anggota roster yang terdaftar & aktif di MONCU. */
    public function listMoncuMembersOfRoster($rosterId) {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT d.nrp FROM v2_roster_details d
             JOIN v2_moncu_employees m ON m.nrp = d.nrp AND m.is_active = 1
             WHERE d.roster_id = ?"
        );
        $stmt->execute([(int)$rosterId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findDetail($rosterId, $nrp, $workDate) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_details WHERE roster_id = ? AND nrp = ? AND work_date = ?");
        $stmt->execute([(int)$rosterId, $nrp, $workDate]);
        return $stmt->fetch() ?: null;
    }

    public function findDetailById($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_roster_details WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function insertDetail(array $d) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_roster_details (roster_id, nrp, employee_name, jabatan, work_date, shift_id, notes, created_by, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$d['roster_id'], $d['nrp'], $d['employee_name'], $d['jabatan'], $d['work_date'], $d['shift_id'], $d['notes'], $d['actor'], $d['actor']]);
        return (int)$this->db->lastInsertId();
    }

    public function setDetailShift($detailId, $shiftId, $actor) {
        $stmt = $this->db->prepare("UPDATE v2_roster_details SET shift_id = ?, updated_by = ? WHERE id = ?");
        $stmt->execute([(int)$shiftId, $actor, (int)$detailId]);
    }

    public function deleteDetailById($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_roster_details WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    /** Link sinkron milik NRP pada satu roster (join ke detail). */
    public function listLinks($rosterId, $nrp) {
        $stmt = $this->db->prepare(
            "SELECT l.*, d.work_date AS d_date, d.shift_id AS current_shift_id
             FROM v2_moncu_roster_links l JOIN v2_roster_details d ON d.id = l.detail_id
             WHERE d.roster_id = ? AND d.nrp = ?"
        );
        $stmt->execute([(int)$rosterId, $nrp]);
        return $stmt->fetchAll();
    }

    public function addLink(array $l) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_moncu_roster_links (period_id, detail_id, work_date, kind, applied_shift_id, prev_shift_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$l['period_id'], $l['detail_id'], $l['work_date'], $l['kind'], $l['applied_shift_id'], $l['prev_shift_id']]);
    }

    public function updateLink($id, array $l) {
        $stmt = $this->db->prepare(
            "UPDATE v2_moncu_roster_links SET period_id = ?, kind = ?, applied_shift_id = ?, prev_shift_id = ? WHERE id = ?"
        );
        $stmt->execute([$l['period_id'], $l['kind'], $l['applied_shift_id'], $l['prev_shift_id'], (int)$id]);
    }

    public function deleteLink($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_moncu_roster_links WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    // ------------------------------------------- Employee (existing, SELECT only)

    public function findEmployeeMaster($nrp) {
        $stmt = $this->db->prepare(
            "SELECT nrp, name, jabatan_ss6, jabatan_roster, unit FROM employees WHERE nrp = ? ORDER BY id LIMIT 1"
        );
        $stmt->execute([$nrp]);
        return $stmt->fetch() ?: null;
    }
}
