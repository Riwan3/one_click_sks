<?php

namespace App\Services;

use App\Models\FleetMasterUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

/** Master Unit Fleet: kelola loader & hauler, dan tarik dari Master CSA Setting Manpower (tambah/sinkron, tidak menimpa). */
class FleetMasterService
{
    /** Kode parkiran hauler: M = Monte, G = Gibson, J = Jumbo Vessel. */
    public const PARKING = ['M' => 'Monte', 'G' => 'Gibson', 'J' => 'Jumbo Vessel'];

    private const TYPES = [FleetMasterUnit::LOADER, FleetMasterUnit::HAULER];

    private const SKIP = 'skip';

    /** Kata kunci nama kategori Master CSA -> tipe fleet. Kategori lain tidak ikut. */
    private const TYPE_KEYWORDS = [
        FleetMasterUnit::LOADER => ['EXCAVATOR', 'WHEEL LOADER'],
        FleetMasterUnit::HAULER => ['DUMP TRUCK', 'HEAVY DUMP', 'OFF HIGHWAY TRUCK'],
    ];

    /** Kata kunci nama CSA -> kode parkiran (kategori JUMBO diperiksa lebih dulu, prioritas lebih tinggi). */
    private const CSA_PARKING = ['MONTE' => 'M', 'GIBSON' => 'G'];

    public function tablesReady(): bool
    {
        return Schema::hasTable('fleet_master_units') && Schema::hasTable('fleet_allocations');
    }

    public function listByType(string $type): Collection
    {
        return FleetMasterUnit::ofType($type)->orderBy('sort_order')->orderBy('code')->get();
    }

    public function create(array $data, ?int $sourceUnitId = null): FleetMasterUnit
    {
        $data = $this->normalize($data);
        $this->guardUnit($data);

        return FleetMasterUnit::create($data + [
            'source_unit_id' => $sourceUnitId,
            'sort_order' => (int) FleetMasterUnit::ofType($data['unit_type'])->max('sort_order') + 1,
            'created_by' => substr((string) Auth::user()?->username, 0, 50),
        ]);
    }

    public function update(int $id, array $data): FleetMasterUnit
    {
        $unit = FleetMasterUnit::findOrFail($id);
        $data = $this->normalize($data + $unit->only(['code', 'unit_type']));
        $this->guardUnit($data, $unit->id);
        $unit->update($data);

        return $unit;
    }

    public function setActive(int $id, bool $isActive): void
    {
        FleetMasterUnit::findOrFail($id)->update(['is_active' => $isActive]);
    }

    public function delete(int $id): void
    {
        FleetMasterUnit::findOrFail($id)->delete();
    }

    // --------------------------------------------------------- Tarik dari Master Manpower

    public function manpowerReady(): bool
    {
        return Schema::hasTable('v2_mp_units') && Schema::hasTable('v2_mp_categories') && Schema::hasTable('v2_mp_csa');
    }

    /** Preview per (CSA, kategori): tipe terdeteksi (bisa diubah user), parkiran hasil deteksi, jumlah unit & yang baru. */
    public function pullPreview(): array
    {
        if (! $this->manpowerReady()) return [];

        $existing = $this->existingCodes();

        return $this->manpowerUnits()->groupBy(fn ($u) => $u->csa.'|'.$u->category)->map(fn (Collection $units) => [
            'csa' => $units->first()->csa,
            'name' => $units->first()->category,
            'type' => $this->detectType($units->first()->category),
            'parking_code' => $this->detectParking($units->first()->csa, $units->first()->category),
            'total' => $units->count(),
            'new' => $units->reject(fn ($u) => isset($existing[Str::upper(trim($u->unit))]))->count(),
        ])->sortBy([['type', 'asc'], ['csa', 'asc'], ['name', 'asc']])->values()->all();
    }

    /**
     * Tarik unit dari (CSA, kategori) terpilih; kode yang sudah ada dilewati, hanya parkiran kosong hauler yang diisi.
     * Return ['added' => n, 'skipped' => n, 'parking_filled' => n].
     */
    public function pull(array $categories): array
    {
        if (! $this->manpowerReady()) throw new InvalidArgumentException('Master Manpower belum tersedia.');

        $typeByKey = collect($categories)->filter(fn ($c) => in_array($c['type'], self::TYPES, true))
            ->mapWithKeys(fn ($c) => [($c['csa'] ?? '').'|'.$c['name'] => $c['type']]);
        $existing = FleetMasterUnit::query()->get()->keyBy(fn ($u) => Str::upper($u->code));
        $result = ['added' => 0, 'skipped' => 0, 'parking_filled' => 0];

        DB::transaction(function () use ($typeByKey, $existing, &$result) {
            foreach ($this->manpowerUnits()->filter(fn ($u) => $typeByKey->has($u->csa.'|'.$u->category)) as $source) {
                $type = $typeByKey[$source->csa.'|'.$source->category];
                $code = Str::upper(trim($source->unit));
                $parking = $type === FleetMasterUnit::HAULER ? $this->detectParking($source->csa, $source->category) : null;

                if ($existing->has($code)) {
                    $result['skipped']++;
                    $result['parking_filled'] += $this->fillEmptyParking($existing[$code], $parking);
                    continue;
                }
                $existing[$code] = $this->create(['code' => $code, 'unit_type' => $type, 'category' => $source->category, 'parking_code' => $parking], $source->id);
                $result['added']++;
            }
        });

        return $result;
    }

    private function fillEmptyParking(FleetMasterUnit $unit, ?string $parking): int
    {
        $canFill = $parking !== null && $unit->unit_type === FleetMasterUnit::HAULER && $unit->parking_code === null;
        if (! $canFill) return 0;

        $unit->update(['parking_code' => $parking]);

        return 1;
    }

    private function detectType(string $category): string
    {
        foreach (self::TYPE_KEYWORDS as $type => $keywords) {
            if (Str::contains(Str::upper($category), $keywords)) return $type;
        }

        return self::SKIP;
    }

    /** Kategori JUMBO -> J; selain itu dari nama CSA (MONTE -> M, GIBSON -> G); lainnya null. */
    private function detectParking(string $csa, string $category): ?string
    {
        if (Str::contains(Str::upper($category), 'JUMBO')) return 'J';

        foreach (self::CSA_PARKING as $keyword => $code) {
            if (Str::contains(Str::upper($csa), $keyword)) return $code;
        }

        return null;
    }

    /** Unit Master CSA (tanpa slot spare) beserta nama CSA & kategori. Tanpa JOIN lintas tabel Laravel (collation berbeda). */
    private function manpowerUnits(): Collection
    {
        return DB::table('v2_mp_units as u')
            ->join('v2_mp_categories as c', 'c.id', '=', 'u.category_id')
            ->join('v2_mp_csa as s', 's.id', '=', 'c.csa_id')
            ->where('u.is_spare_slot', 0)
            ->orderBy('s.sort_order')->orderBy('c.sort_order')->orderBy('u.sort_order')->orderBy('u.id')
            ->get(['u.id', 'u.unit', 'c.name as category', 's.name as csa']);
    }

    private function existingCodes(): array
    {
        return FleetMasterUnit::query()->pluck('code')->mapWithKeys(fn ($code) => [Str::upper($code) => true])->all();
    }

    // ------------------------------------------------------------------ Validasi

    private function normalize(array $data): array
    {
        $text = fn ($key) => isset($data[$key]) && trim((string) $data[$key]) !== '' ? trim((string) $data[$key]) : null;
        $type = $text('unit_type');
        $fields = ['code' => Str::upper((string) $text('code')), 'unit_type' => $type];

        if (array_key_exists('category', $data)) $fields['category'] = $text('category');
        if (array_key_exists('parking_code', $data) || $type === FleetMasterUnit::LOADER) {
            $fields['parking_code'] = $type === FleetMasterUnit::HAULER ? $this->parkingCode($text('parking_code')) : null;
        }

        return $fields;
    }

    private function parkingCode(?string $code): ?string
    {
        $code = $code === null ? null : Str::upper($code);
        if ($code !== null && ! isset(self::PARKING[$code])) throw new InvalidArgumentException('Parkiran harus M, G, atau J.');

        return $code;
    }

    private function guardUnit(array $data, ?int $ignoreId = null): void
    {
        if ($data['code'] === '') throw new InvalidArgumentException('Kode unit wajib diisi.');
        if (! in_array($data['unit_type'], self::TYPES, true)) throw new InvalidArgumentException('Tipe unit harus Loader atau Hauler.');

        $isDuplicate = FleetMasterUnit::where('code', $data['code'])->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        if ($isDuplicate) throw new InvalidArgumentException("Kode {$data['code']} sudah terdaftar di Master Unit.");
    }
}
