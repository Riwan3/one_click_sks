<?php

namespace App\Services;

use App\Models\MppMasterCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MppMasterCategoryService
{
    public function all(): Collection
    {
        return MppMasterCategory::active()->ordered()->get();
    }

    public function paginated(int $perPage = 15)
    {
        return MppMasterCategory::ordered()->paginate($perPage);
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return MppMasterCategory::search($keyword)->ordered()->paginate($perPage);
    }

    public function create(array $data): MppMasterCategory
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        // Set sort_order to max + 1 if not provided
        if (! isset($data['sort_order'])) {
            $maxSort = MppMasterCategory::max('sort_order') ?? 0;
            $data['sort_order'] = $maxSort + 1;
        }

        return MppMasterCategory::create($data);
    }

    public function update(MppMasterCategory $category, array $data): MppMasterCategory
    {
        $data['updated_by'] = auth()->id();
        $category->update($data);

        return $category->refresh();
    }

    public function delete(MppMasterCategory $category): bool
    {
        return $category->delete();
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                MppMasterCategory::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });
    }

    public function findByKode(string $kode): ?MppMasterCategory
    {
        return MppMasterCategory::where('kode', $kode)->first();
    }
}
