<?php

namespace App\Services;

use App\Models\FleetAllocation;
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
    private const TYPES = [FleetMasterUnit::LOADER, FleetMasterUnit::HAULER];

    private const SKIP = 'skip';

    /** Kata kunci nama kategori Master CSA -> tipe fleet. Kategori lain tidak ikut. */
    private const TYPE_KEYWORDS = [
        FleetMasterUnit::LOADER => ['EXCAVATOR', 'WHEEL LOADER'],
        FleetMasterUnit::HAULER => ['DUMP TRUCK', 'HEAVY DUMP', 'OFF HIGHWAY TRUCK'],
    ];

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

    /** Group yang pernah dipakai (setting harian + default master), untuk saran isian. */
    public function groupOptions(): array
    {
        return FleetAllocation::query()->whereNotNull('group_name')->distinct()->pluck('group_name')
            ->merge(FleetMasterUnit::query()->whereNotNull('default_group')->distinct()->pluck('default_group'))
            ->filter()->unique()->sort()->values()->all();
    }

    // --------------------------------------------------------- Tarik dari Master Manpower

    public function manpowerReady(): bool
    {
        return Schema::hasTable('v2_mp_units') && Schema::hasTable('v2_mp_categories');
    }

    /** Preview: per kategori Master CSA -> tipe terdeteksi (bisa diubah user), jumlah unit & yang baru. */
    public function pullPreview(): array
    {
        if (! $this->manpowerReady()) return [];

        $existing = $this->existingCodes();

        return $this->manpowerUnits()->groupBy('category')->map(fn (Collection $units, string $category) => [
            'name' => $category,
            'type' => $this->detectType($category),
            'total' => $units->count(),
            'new' => $units->reject(fn ($u) => isset($existing[Str::upper(trim($u->unit))]))->count(),
        ])->sortBy([['type', 'asc'], ['name', 'asc']])->values()->all();
    }

    /** Tarik unit dari kategori terpilih; kode yang sudah ada dilewati. Return ['added' => n, 'skipped' => n]. */
    public function pull(array $categories): array
    {
        if (! $this->manpowerReady()) throw new InvalidArgumentException('Master Manpower belum tersedia.');

        $typeByCategory = collect($categories)->filter(fn ($c) => in_array($c['type'], self::TYPES, true))->pluck('type', 'name');
        $existing = $this->existingCodes();
        $result = ['added' => 0, 'skipped' => 0];

        DB::transaction(function () use ($typeByCategory, &$existing, &$result) {
            foreach ($this->manpowerUnits()->filter(fn ($u) => $typeByCategory->has($u->category)) as $source) {
                $code = Str::upper(trim($source->unit));
                if (isset($existing[$code])) {
                    $result['skipped']++;
                    continue;
                }
                $this->create(['code' => $code, 'unit_type' => $typeByCategory[$source->category], 'category' => $source->category], $source->id);
                $existing[$code] = true;
                $result['added']++;
            }
        });

        return $result;
    }

    private function detectType(string $category): string
    {
        foreach (self::TYPE_KEYWORDS as $type => $keywords) {
            if (Str::contains(Str::upper($category), $keywords)) return $type;
        }

        return self::SKIP;
    }

    /** Unit Master CSA (tanpa slot spare), urut sesuai tampilan. Tanpa JOIN lintas tabel/collation. */
    private function manpowerUnits(): Collection
    {
        return DB::table('v2_mp_units as u')
            ->join('v2_mp_categories as c', 'c.id', '=', 'u.category_id')
            ->where('u.is_spare_slot', 0)
            ->orderBy('c.sort_order')->orderBy('u.sort_order')->orderBy('u.id')
            ->get(['u.id', 'u.unit', 'c.name as category']);
    }

    private function existingCodes(): array
    {
        return FleetMasterUnit::query()->pluck('code')->mapWithKeys(fn ($code) => [Str::upper($code) => true])->all();
    }

    // ------------------------------------------------------------------ Validasi

    private function normalize(array $data): array
    {
        $text = fn ($key) => isset($data[$key]) && trim((string) $data[$key]) !== '' ? trim((string) $data[$key]) : null;
        $fields = ['code' => Str::upper((string) $text('code')), 'unit_type' => $text('unit_type')];

        foreach (['category', 'default_group', 'default_area', 'default_front', 'default_refueling_time'] as $key) {
            if (array_key_exists($key, $data)) $fields[$key] = $text($key);
        }
        if (array_key_exists('default_distance', $data)) $fields['default_distance'] = max(0, (int) $data['default_distance']);

        return $fields;
    }

    private function guardUnit(array $data, ?int $ignoreId = null): void
    {
        if ($data['code'] === '') throw new InvalidArgumentException('Kode unit wajib diisi.');
        if (! in_array($data['unit_type'], self::TYPES, true)) throw new InvalidArgumentException('Tipe unit harus Loader atau Hauler.');

        $isDuplicate = FleetMasterUnit::where('code', $data['code'])->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        if ($isDuplicate) throw new InvalidArgumentException("Kode {$data['code']} sudah terdaftar di Master Unit.");
    }
}
