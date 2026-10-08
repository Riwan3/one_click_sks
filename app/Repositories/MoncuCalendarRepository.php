<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

// Port dari One-Click V1 (PDO + SQL V1 dipertahankan). Prepared statement; nama tabel/kolom hardcode.
class MoncuCalendarRepository {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? DB::connection()->getPdo();
    }

    private const JABATAN_SQL = "COALESCE(NULLIF(TRIM(e.jabatan_ss6), ''), NULLIF(TRIM(e.jabatan_roster), ''), '')";
    private const FROM_SQL = "FROM v2_moncu_employees m
             LEFT JOIN (SELECT nrp, MIN(id) AS mid FROM employees GROUP BY nrp) x ON x.nrp = m.nrp COLLATE utf8mb4_unicode_ci
             LEFT JOIN employees e ON e.id = x.mid";

    /** WHERE dasar: karyawan aktif + filter group (exact), position/jabatan (exact), search (NRP/nama LIKE). */
    private function employeeWhere(array $f, array &$args) {
        $where = ['m.is_active = 1'];
        if (!empty($f['group']))    { $where[] = 'm.group_name = ?'; $args[] = $f['group']; }
        if (!empty($f['position'])) { $where[] = self::JABATAN_SQL . ' = ?'; $args[] = $f['position']; }
        if (!empty($f['search'])) {
            $like = '%' . addcslashes($f['search'], '%_\\') . '%';
            $where[] = '(m.nrp LIKE ? OR e.name LIKE ?)';
            $args[] = $like; $args[] = $like;
        }
        return implode(' AND ', $where);
    }

    private const COLS = "m.nrp, e.name AS employee_name, " . self::JABATAN_SQL . " AS jabatan, m.group_name, m.hiring_date,
                p.cycle_year, p.period_no, p.awal, p.akhir, p.tahunan, p.izin_resmi, p.induksi, p.plan";

    /**
     * Periode yang BERIRISAN dengan [start, end] (berdasarkan tanggal aktual, bukan cycle_year):
     *   AWAL <= end  DAN  COALESCE(INDUKSI, AKHIR) >= start      (INDUKSI = AKHIR + 1 -> ikut diperhitungkan)
     *   ATAU PLAN di dalam [start, end]                           (PLAN hanya informasi)
     */
    public function periodsOverlapping(array $f, $start, $end) {
        $args = [];
        $where = $this->employeeWhere($f, $args);
        $args = array_merge($args, [$end, $start, $start, $end]);
        $stmt = $this->db->prepare(
            "SELECT " . self::COLS . " " . self::FROM_SQL . "
             JOIN v2_moncu_periods p ON p.nrp = m.nrp
             WHERE $where AND p.awal IS NOT NULL
               AND ((p.awal <= ? AND COALESCE(p.induksi, p.akhir) >= ?) OR (p.plan BETWEEN ? AND ?))
             ORDER BY p.awal, m.nrp"
        );
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    /** Periode yang relevan untuk SATU tanggal: CT (AWAL..AKHIR), ID (INDUKSI), atau PLAN (= tanggal). */
    public function periodsOnDate(array $f, $date) {
        $args = [];
        $where = $this->employeeWhere($f, $args);
        $args = array_merge($args, [$date, $date, $date, $date]);
        $stmt = $this->db->prepare(
            "SELECT " . self::COLS . " " . self::FROM_SQL . "
             JOIN v2_moncu_periods p ON p.nrp = m.nrp
             WHERE $where AND p.awal IS NOT NULL
               AND ((p.awal <= ? AND p.akhir >= ?) OR p.induksi = ? OR p.plan = ?)
             ORDER BY e.name, m.nrp"
        );
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function countEmployees(array $f) {
        $args = [];
        $where = $this->employeeWhere($f, $args);
        $stmt = $this->db->prepare("SELECT COUNT(*) " . self::FROM_SQL . " WHERE $where");
        $stmt->execute($args);
        return (int)$stmt->fetchColumn();
    }

    /** AWAL semua periode ber-AWAL milik daftar NRP (untuk informasi "AWAL aktual berikutnya" pada PLAN). */
    public function awalByEmployees(array $nrps) {
        $out = [];
        foreach (array_chunk(array_values(array_unique($nrps)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $this->db->prepare(
                "SELECT nrp, cycle_year, period_no, awal FROM v2_moncu_periods
                 WHERE nrp IN ($in) AND awal IS NOT NULL ORDER BY nrp, cycle_year, period_no"
            );
            $stmt->execute($chunk);
            foreach ($stmt as $r) { $out[$r['nrp']][] = $r; }
        }
        return $out;
    }

    public function listGroups() {
        return $this->db->query(
            "SELECT DISTINCT group_name FROM v2_moncu_employees
             WHERE is_active = 1 AND group_name IS NOT NULL AND group_name <> '' ORDER BY group_name"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listPositions() {
        return $this->db->query(
            "SELECT j FROM (SELECT DISTINCT " . self::JABATAN_SQL . " AS j " . self::FROM_SQL . " WHERE m.is_active = 1) t
             WHERE j <> '' ORDER BY j"
        )->fetchAll(PDO::FETCH_COLUMN);
    }
}
