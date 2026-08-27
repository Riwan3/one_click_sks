<?php

namespace App\Services;

use App\Models\MorData;
use Illuminate\Pagination\LengthAwarePaginator;

class MorDataService
{
    public function paginate(?string $search, int $bulan, int $tahun, int $page, int $perPage): LengthAwarePaginator
    {
        return MorData::query()
            ->with('employee')
            ->search($search)
            ->period($bulan, $tahun)
            ->orderByDesc('total_poin')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): MorData
    {
        $data['total_poin'] = $this->calculateTotalPoin($data);

        $morData = MorData::create($data)->fresh();

        $this->refreshRanking((int) $data['bulan'], (int) $data['tahun']);

        return $morData->fresh();
    }

    public function update(MorData $morData, array $data): MorData
    {
        $data['total_poin'] = $this->calculateTotalPoin($data);

        $morData->update($data);

        $this->refreshRanking((int) $morData->bulan, (int) $morData->tahun);

        return $morData->fresh();
    }

    public function delete(MorData $morData): void
    {
        $bulan = $morData->bulan;
        $tahun = $morData->tahun;

        $morData->delete();

        $this->refreshRanking((int) $bulan, (int) $tahun);
    }

    private function calculateTotalPoin(array $data): float
    {
        return round(
            (float) ($data['atr_point'] ?? 0)
            + (float) ($data['timesheet_point'] ?? 0)
            + (float) ($data['hm_point'] ?? 0)
            + (float) ($data['fuel_point'] ?? 0)
            + (float) ($data['pembinaan_poin'] ?? 20),
            2
        );
    }

    private function refreshRanking(int $bulan, int $tahun): void
    {
        $ranked = MorData::query()->period($bulan, $tahun)->orderByDesc('total_poin')->pluck('id');

        foreach ($ranked as $index => $id) {
            MorData::where('id', $id)->update(['ranking' => $index + 1, 'dari_operator' => $ranked->count()]);
        }
    }
}
