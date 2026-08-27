<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMppMasterCategoryRequest;
use App\Http\Resources\MppMasterCategoryResource;
use App\Models\MppMasterCategory;
use App\Services\MppMasterCategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MppMasterCategoryController extends Controller
{
    public function __construct(private MppMasterCategoryService $service) {}

    public function index(Request $request)
    {
        abort_if(! in_array(auth()->user()->role, ['admin', 'pimpinan']), 403);

        $categories = $this->service->paginated(15);

        return Inertia::render('MppDepartemen/MasterCategories', [
            'categories' => MppMasterCategoryResource::collection($categories),
            'pagination' => [
                'total' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
            ],
        ]);
    }

    public function store(StoreMppMasterCategoryRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('mpp-master-categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(StoreMppMasterCategoryRequest $request, MppMasterCategory $mppMasterCategory)
    {
        $this->service->update($mppMasterCategory, $request->validated());

        return redirect()->route('mpp-master-categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(MppMasterCategory $mppMasterCategory)
    {
        $this->service->delete($mppMasterCategory);

        return redirect()->route('mpp-master-categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function reorder(Request $request)
    {
        abort_if(! in_array(auth()->user()->role, ['admin', 'pimpinan']), 403);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:mpp_master_categories,id'],
        ]);

        $this->service->reorder($validated['ids']);

        return response()->json(['success' => true, 'message' => 'Urutan kategori berhasil diperbarui.']);
    }
}
