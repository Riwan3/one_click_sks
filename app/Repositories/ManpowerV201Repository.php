<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Throwable;

// Port One-Click V1 (PDO + SQL V1 dipertahankan). WRITE hanya ke tabel v2_mp_*; Roster V2 & employees hanya di-SELECT.
class ManpowerV201Repository {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? DB::connection()->getPdo();
    }

    public function transaction(callable $fn) {
        if ($this->db->inTransaction()) {
            return $fn();
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

    public function tablesReady() {
        try {
            $stmt = $this->db->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('v2_mp_csa','v2_mp_categories','v2_mp_units')"
            );
            return (int)$stmt->fetchColumn() === 3;
        } catch (Throwable $e) {
            Log::error('[ManpowerV201] cek tabel gagal: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------------------ Tree

    /** Seluruh master: CSA -> kategori -> unit, sudah terurut. */
    public function tree() {
        $csas = $this->db->query("SELECT * FROM v2_mp_csa ORDER BY sort_order, id")->fetchAll();
        $cats = $this->db->query("SELECT * FROM v2_mp_categories ORDER BY col_pos, sort_order, id")->fetchAll();
        $units = $this->db->query("SELECT * FROM v2_mp_units ORDER BY sort_order, id")->fetchAll();

        $unitsByCat = [];
        foreach ($units as $u) $unitsByCat[$u['category_id']][] = $u;
        $catsByCsa = [];
        foreach ($cats as $c) {
            $c['units'] = $unitsByCat[$c['id']] ?? [];
            $catsByCsa[$c['csa_id']][] = $c;
        }
        foreach ($csas as &$s) $s['categories'] = $catsByCsa[$s['id']] ?? [];
        unset($s);
        return $csas;
    }

    // ------------------------------------------------------------------- CSA

    public function findCsa($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_csa WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function findCsaByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_csa WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch() ?: null;
    }

    public function addCsa($name, $actor) {
        $order = (int)$this->db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM v2_mp_csa")->fetchColumn();
        $stmt = $this->db->prepare("INSERT INTO v2_mp_csa (name, sort_order, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$name, $order, $actor]);
        return (int)$this->db->lastInsertId();
    }

    public function renameCsa($id, $name) {
        $stmt = $this->db->prepare("UPDATE v2_mp_csa SET name = ? WHERE id = ?");
        $stmt->execute([$name, (int)$id]);
    }

    public function setCsaLocked($id, $locked) {
        $stmt = $this->db->prepare("UPDATE v2_mp_csa SET is_locked = ? WHERE id = ?");
        $stmt->execute([$locked ? 1 : 0, (int)$id]);
    }

    public function deleteCsa($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_csa WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    public function moveCsa($id, $dir) {
        $this->swapOrder('v2_mp_csa', '1 = 1', [], (int)$id, $dir);
    }

    // -------------------------------------------------------------- Kategori

    public function findCategory($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_categories WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function findCategoryByName($csaId, $name) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_categories WHERE csa_id = ? AND name = ?");
        $stmt->execute([(int)$csaId, $name]);
        return $stmt->fetch() ?: null;
    }

    public function addCategory($csaId, $name, $colPos, $actor) {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM v2_mp_categories WHERE csa_id = ? AND col_pos = ?");
        $stmt->execute([(int)$csaId, (int)$colPos]);
        $order = (int)$stmt->fetchColumn();
        $ins = $this->db->prepare("INSERT INTO v2_mp_categories (csa_id, name, col_pos, sort_order, created_by) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([(int)$csaId, $name, (int)$colPos, $order, $actor]);
        return (int)$this->db->lastInsertId();
    }

    /** Ubah nama/kolom. Pindah kolom -> diletakkan di urutan paling bawah kolom tujuan. */
    public function updateCategory($id, $name, $colPos) {
        $cat = $this->findCategory($id);
        if (!$cat) return;
        $order = (int)$cat['sort_order'];
        if ((int)$cat['col_pos'] !== (int)$colPos) {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM v2_mp_categories WHERE csa_id = ? AND col_pos = ?");
            $stmt->execute([(int)$cat['csa_id'], (int)$colPos]);
            $order = (int)$stmt->fetchColumn();
        }
        $upd = $this->db->prepare("UPDATE v2_mp_categories SET name = ?, col_pos = ?, sort_order = ? WHERE id = ?");
        $upd->execute([$name, (int)$colPos, $order, (int)$id]);
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_categories WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    public function moveCategory($id, $dir) {
        $cat = $this->findCategory($id);
        if (!$cat) return;
        $this->swapOrder('v2_mp_categories', 'csa_id = ? AND col_pos = ?', [(int)$cat['csa_id'], (int)$cat['col_pos']], (int)$id, $dir);
    }

    public function countUnitsInCategory($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM v2_mp_units WHERE category_id = ?");
        $stmt->execute([(int)$id]);
        return (int)$stmt->fetchColumn();
    }

    // ----------------------------------------------------------------- Unit

    public function findUnit($id) {
        $stmt = $this->db->prepare(
            "SELECT u.*, c.csa_id FROM v2_mp_units u JOIN v2_mp_categories c ON c.id = u.category_id WHERE u.id = ?"
        );
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    /** Lokasi unit (CSA + kategori) bila kodenya sudah terdaftar, untuk pesan duplikat. */
    public function findUnitByCode($unit) {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.unit, c.name AS category_name, s.name AS csa_name
             FROM v2_mp_units u
             JOIN v2_mp_categories c ON c.id = u.category_id
             JOIN v2_mp_csa s ON s.id = c.csa_id
             WHERE u.unit = ?"
        );
        $stmt->execute([$unit]);
        return $stmt->fetch() ?: null;
    }

    public function addUnit($categoryId, $unit, $isSpareSlot, $isHighRisk, $actor) {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM v2_mp_units WHERE category_id = ?");
        $stmt->execute([(int)$categoryId]);
        $order = (int)$stmt->fetchColumn();
        $ins = $this->db->prepare(
            "INSERT INTO v2_mp_units (category_id, unit, is_spare_slot, is_high_risk, sort_order, created_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([(int)$categoryId, $unit, $isSpareSlot ? 1 : 0, $isHighRisk ? 1 : 0, $order, $actor]);
        return (int)$this->db->lastInsertId();
    }

    public function renameUnit($id, $unit, $isSpareSlot) {
        $stmt = $this->db->prepare("UPDATE v2_mp_units SET unit = ?, is_spare_slot = ? WHERE id = ?");
        $stmt->execute([$unit, $isSpareSlot ? 1 : 0, (int)$id]);
    }

    public function setUnitHighRisk($id, $flag) {
        $stmt = $this->db->prepare("UPDATE v2_mp_units SET is_high_risk = ? WHERE id = ?");
        $stmt->execute([$flag ? 1 : 0, (int)$id]);
    }

    public function deleteUnit($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_units WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    /** Pindahkan unit ke kategori lain (urutan paling bawah kategori tujuan). */
    public function moveUnitToCategory($id, $categoryId) {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM v2_mp_units WHERE category_id = ?");
        $stmt->execute([(int)$categoryId]);
        $order = (int)$stmt->fetchColumn();
        $upd = $this->db->prepare("UPDATE v2_mp_units SET category_id = ?, sort_order = ? WHERE id = ?");
        $upd->execute([(int)$categoryId, $order, (int)$id]);
    }

    public function moveUnit($id, $dir) {
        $u = $this->findUnit($id);
        if (!$u) return;
        $this->swapOrder('v2_mp_units', 'category_id = ?', [(int)$u['category_id']], (int)$id, $dir);
    }

    // -------------------------------------------------------------- Helper

    /**
     * Tukar sort_order baris $id dengan tetangganya (dir = 'up'|'down') dalam lingkup $scopeSql.
     * $table hanya diisi konstanta dari kelas ini.
     */
    private function swapOrder($table, $scopeSql, array $scopeParams, $id, $dir) {
        $this->transaction(function () use ($table, $scopeSql, $scopeParams, $id, $dir) {
            $sel = $this->db->prepare("SELECT id, sort_order FROM $table WHERE id = ?");
            $sel->execute([$id]);
            $row = $sel->fetch();
            if (!$row) return;

            $cmp = $dir === 'up' ? '<' : '>';
            $ord = $dir === 'up' ? 'DESC' : 'ASC';
            $nb = $this->db->prepare(
                "SELECT id, sort_order FROM $table
                 WHERE $scopeSql AND (sort_order $cmp ? OR (sort_order = ? AND id $cmp ?))
                 ORDER BY sort_order $ord, id $ord LIMIT 1"
            );
            $nb->execute(array_merge($scopeParams, [(int)$row['sort_order'], (int)$row['sort_order'], (int)$row['id']]));
            $other = $nb->fetch();
            if (!$other) return;

            // Bila sort_order kembar, geser salah satunya supaya tukar tetap bermakna.
            $a = (int)$row['sort_order'];
            $b = (int)$other['sort_order'];
            if ($a === $b) $b = $dir === 'up' ? $a - 1 : $a + 1;
            $upd = $this->db->prepare("UPDATE $table SET sort_order = ? WHERE id = ?");
            $upd->execute([$b, (int)$row['id']]);
            $upd->execute([$a, (int)$other['id']]);
        });
    }
}
