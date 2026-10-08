<?php

namespace App\Services;

use App\Exceptions\ManpowerV201Conflict;
use App\Exceptions\ManpowerV201NotFound;
use App\Repositories\ManpowerV201Repository;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

// Aturan bisnis Master CSA Setting Manpower v2.01 (port V1): otorisasi, validasi, penguncian CSA.
// Pesan exception aman ditampilkan ke user.
class ManpowerV201Service {
    private $repo;

    public function __construct(ManpowerV201Repository $repo) {
        $this->repo = $repo;
    }

    /** Admin, Admin Produksi, dan GL Manpower. */
    public static function canManage() {
        $user = Auth::user();
        if (!$user) return false;
        return $user->isAdmin() || strtolower((string) $user->role) === 'admin produksi' || $user->isGlManpower();
    }

    private function actor() {
        return substr((string) Auth::user()?->username, 0, 50);
    }

    public function tree() {
        return $this->repo->tree();
    }

    // ---------------------------------------------------------------- Util

    /** Kode unit/slot: huruf besar, spasi dirapikan. */
    public static function normalizeUnit($s) {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string)$s)));
    }

    public static function isSpareSlot($unit) {
        return (bool)preg_match('/^SPARE\s/', $unit);
    }

    private function cleanName($s, $max, $label) {
        $s = trim(preg_replace('/\s+/', ' ', (string)$s));
        if ($s === '') throw new InvalidArgumentException("$label wajib diisi.");
        if (mb_strlen($s) > $max) throw new InvalidArgumentException("$label maksimal $max karakter.");
        return $s;
    }

    private function csaOrFail($id) {
        $csa = $this->repo->findCsa($id);
        if (!$csa) throw new ManpowerV201NotFound('CSA tidak ditemukan.');
        return $csa;
    }

    private function categoryOrFail($id) {
        $cat = $this->repo->findCategory($id);
        if (!$cat) throw new ManpowerV201NotFound('Kategori tidak ditemukan.');
        return $cat;
    }

    private function unitOrFail($id) {
        $u = $this->repo->findUnit($id);
        if (!$u) throw new ManpowerV201NotFound('Unit tidak ditemukan.');
        return $u;
    }

    /** CSA terkunci tidak boleh diubah isinya (nama, kategori, unit). */
    private function assertUnlocked(array $csa) {
        if ((int)$csa['is_locked'] === 1) {
            throw new ManpowerV201Conflict('CSA "' . $csa['name'] . '" sedang dikunci. Buka kunci dulu untuk mengubahnya.');
        }
    }

    private static function colPos($v) {
        $v = (int)$v;
        if ($v !== 1 && $v !== 2) throw new InvalidArgumentException('Kolom layout harus kiri (1) atau kanan (2).');
        return $v;
    }

    private static function direction($v) {
        if ($v !== 'up' && $v !== 'down') throw new InvalidArgumentException('Arah pindah tidak valid.');
        return $v;
    }

    // ----------------------------------------------------------------- CSA

    public function addCsa($name) {
        $name = strtoupper($this->cleanName($name, 100, 'Nama CSA'));
        if ($this->repo->findCsaByName($name)) throw new ManpowerV201Conflict("CSA \"$name\" sudah ada.");
        return $this->repo->addCsa($name, $this->actor());
    }

    public function renameCsa($id, $name) {
        $csa = $this->csaOrFail($id);
        $this->assertUnlocked($csa);
        $name = strtoupper($this->cleanName($name, 100, 'Nama CSA'));
        $dup = $this->repo->findCsaByName($name);
        if ($dup && (int)$dup['id'] !== (int)$id) throw new ManpowerV201Conflict("CSA \"$name\" sudah ada.");
        $this->repo->renameCsa($id, $name);
    }

    public function setCsaLocked($id, $locked) {
        $this->csaOrFail($id);
        $this->repo->setCsaLocked($id, $locked);
    }

    public function deleteCsa($id) {
        $csa = $this->csaOrFail($id);
        $this->assertUnlocked($csa);
        $this->repo->deleteCsa($id); // kategori & unit ikut terhapus (FK CASCADE)
    }

    public function moveCsa($id, $dir) {
        $this->csaOrFail($id);
        $this->repo->moveCsa($id, self::direction($dir));
    }

    // ------------------------------------------------------------ Kategori

    public function addCategory($csaId, $name, $colPos) {
        $csa = $this->csaOrFail($csaId);
        $this->assertUnlocked($csa);
        $name = strtoupper($this->cleanName($name, 120, 'Nama kategori'));
        if ($this->repo->findCategoryByName($csaId, $name)) {
            throw new ManpowerV201Conflict("Kategori \"$name\" sudah ada di CSA ini.");
        }
        return $this->repo->addCategory($csaId, $name, self::colPos($colPos), $this->actor());
    }

    public function updateCategory($id, $name, $colPos) {
        $cat = $this->categoryOrFail($id);
        $this->assertUnlocked($this->csaOrFail($cat['csa_id']));
        $name = strtoupper($this->cleanName($name, 120, 'Nama kategori'));
        $dup = $this->repo->findCategoryByName($cat['csa_id'], $name);
        if ($dup && (int)$dup['id'] !== (int)$id) {
            throw new ManpowerV201Conflict("Kategori \"$name\" sudah ada di CSA ini.");
        }
        $this->repo->updateCategory($id, $name, self::colPos($colPos));
    }

    public function deleteCategory($id) {
        $cat = $this->categoryOrFail($id);
        $this->assertUnlocked($this->csaOrFail($cat['csa_id']));
        $n = $this->repo->countUnitsInCategory($id);
        if ($n > 0) {
            throw new ManpowerV201Conflict("Kategori masih berisi $n unit. Pindahkan atau hapus unitnya dulu.");
        }
        $this->repo->deleteCategory($id);
    }

    public function moveCategory($id, $dir) {
        $cat = $this->categoryOrFail($id);
        $this->assertUnlocked($this->csaOrFail($cat['csa_id']));
        $this->repo->moveCategory($id, self::direction($dir));
    }

    // ---------------------------------------------------------------- Unit

    /**
     * Tambah banyak unit sekaligus (dipisah koma/spasi/baris baru; kode berspasi seperti
     * "SPARE 785A" atau "W57301 AMM" ditulis satu per baris atau dipisah koma).
     * Unit yang sudah terdaftar di tempat lain dilewati dan dilaporkan, bukan dipindah.
     */
    public function addUnits($categoryId, $text, $highRisk) {
        $cat = $this->categoryOrFail($categoryId);
        $this->assertUnlocked($this->csaOrFail($cat['csa_id']));

        $parts = preg_split('/[,\r\n;]+/', (string)$text);
        $units = [];
        foreach ($parts as $p) {
            $u = self::normalizeUnit($p);
            if ($u === '') continue;
            if (mb_strlen($u) > 50) throw new InvalidArgumentException("Kode unit \"$u\" terlalu panjang (maks 50).");
            $units[$u] = true;
        }
        $units = array_keys($units);
        if (!$units) throw new InvalidArgumentException('Isi minimal satu kode unit.');

        $added = [];
        $skipped = [];
        $this->repo->transaction(function () use ($categoryId, $units, $highRisk, &$added, &$skipped) {
            foreach ($units as $u) {
                $dup = $this->repo->findUnitByCode($u);
                if ($dup) {
                    $skipped[] = $u . ' (sudah di ' . $dup['csa_name'] . ' / ' . $dup['category_name'] . ')';
                    continue;
                }
                $this->repo->addUnit($categoryId, $u, self::isSpareSlot($u), $highRisk && !self::isSpareSlot($u), $this->actor());
                $added[] = $u;
            }
        });
        return ['added' => $added, 'skipped' => $skipped];
    }

    public function renameUnit($id, $unit) {
        $u = $this->unitOrFail($id);
        $this->assertUnlocked($this->csaOrFail($u['csa_id']));
        $unit = self::normalizeUnit($unit);
        if ($unit === '') throw new InvalidArgumentException('Kode unit wajib diisi.');
        if (mb_strlen($unit) > 50) throw new InvalidArgumentException('Kode unit maksimal 50 karakter.');
        $dup = $this->repo->findUnitByCode($unit);
        if ($dup && (int)$dup['id'] !== (int)$id) {
            throw new ManpowerV201Conflict("Unit \"$unit\" sudah ada di {$dup['csa_name']} / {$dup['category_name']}.");
        }
        $spare = self::isSpareSlot($unit);
        $this->repo->renameUnit($id, $unit, $spare);
        if ($spare) $this->repo->setUnitHighRisk($id, false);
    }

    public function setUnitHighRisk($id, $flag) {
        $u = $this->unitOrFail($id);
        $this->assertUnlocked($this->csaOrFail($u['csa_id']));
        if ((int)$u['is_spare_slot'] === 1 && $flag) {
            throw new InvalidArgumentException('Slot SPARE tidak bisa ditandai high risk.');
        }
        $this->repo->setUnitHighRisk($id, $flag);
    }

    public function deleteUnit($id) {
        $u = $this->unitOrFail($id);
        $this->assertUnlocked($this->csaOrFail($u['csa_id']));
        $this->repo->deleteUnit($id);
    }

    public function moveUnit($id, $dir) {
        $u = $this->unitOrFail($id);
        $this->assertUnlocked($this->csaOrFail($u['csa_id']));
        $this->repo->moveUnit($id, self::direction($dir));
    }

    public function moveUnitToCategory($id, $categoryId) {
        $u = $this->unitOrFail($id);
        $target = $this->categoryOrFail($categoryId);
        $this->assertUnlocked($this->csaOrFail($u['csa_id']));
        $this->assertUnlocked($this->csaOrFail($target['csa_id']));
        if ((int)$u['category_id'] === (int)$categoryId) return;
        $this->repo->moveUnitToCategory($id, $categoryId);
    }
}
