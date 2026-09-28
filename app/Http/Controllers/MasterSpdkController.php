<?php

namespace App\Http\Controllers;

use App\Models\MasterSpdk;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterSpdkController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $kategori = trim((string) $request->string('kategori'));
        $page = max(1, (int) $request->integer('page', 1));

        $categories = MasterSpdk::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $spdks = MasterSpdk::query()
            ->search($search)
            ->when($kategori !== '', fn ($q) => $q->where('kategori', $kategori))
            ->orderBy('kategori')
            ->orderBy('no_pelanggaran')
            ->orderBy('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        $user = $request->user();
        $canManage = (bool) ($user?->isAdmin()
            || in_array(strtolower((string) $user?->role), ['admin', 'admin_produksi', 'management'], true)
            || str_contains(strtolower((string) $user?->jabatan), 'admin'));

        return Inertia::render('OneCore/MasterSpdk/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'spdks' => [
                'data' => $spdks->items(),
                'currentPage' => $spdks->currentPage(),
                'lastPage' => $spdks->lastPage(),
                'total' => $spdks->total(),
                'perPage' => $spdks->perPage(),
            ],
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'kategori' => $kategori,
            ],
            'canManage' => $canManage,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_pelanggaran' => ['required', 'string', 'max:50'],
            'kategori' => ['required', 'string', 'max:100'],
            'jenis_pelanggaran' => ['required', 'string'],
            'sanksi' => ['required', 'string', 'max:100'],
            'lubang' => ['nullable', 'integer', 'min:0'],
            'keterangan_sanksi' => ['nullable', 'string'],
        ], [
            'no_pelanggaran.required' => 'Nomor pasal / pelanggaran wajib diisi.',
            'kategori.required' => 'Kategori pelanggaran wajib dipilih atau diisi.',
            'jenis_pelanggaran.required' => 'Jenis pelanggaran wajib diisi.',
            'sanksi.required' => 'Sanksi wajib diisi.',
        ]);

        $validated['lubang'] = $validated['lubang'] ?? 0;

        MasterSpdk::create($validated);

        return redirect()->back()->with('success', 'Data SPDK berhasil ditambahkan.');
    }

    public function update(Request $request, MasterSpdk $spdk): RedirectResponse
    {
        $validated = $request->validate([
            'no_pelanggaran' => ['required', 'string', 'max:50'],
            'kategori' => ['required', 'string', 'max:100'],
            'jenis_pelanggaran' => ['required', 'string'],
            'sanksi' => ['required', 'string', 'max:100'],
            'lubang' => ['nullable', 'integer', 'min:0'],
            'keterangan_sanksi' => ['nullable', 'string'],
        ], [
            'no_pelanggaran.required' => 'Nomor pasal / pelanggaran wajib diisi.',
            'kategori.required' => 'Kategori pelanggaran wajib dipilih atau diisi.',
            'jenis_pelanggaran.required' => 'Jenis pelanggaran wajib diisi.',
            'sanksi.required' => 'Sanksi wajib diisi.',
        ]);

        $validated['lubang'] = $validated['lubang'] ?? 0;

        $spdk->update($validated);

        return redirect()->back()->with('success', 'Data SPDK berhasil diperbarui.');
    }

    public function destroy(MasterSpdk $spdk): RedirectResponse
    {
        $spdk->delete();

        return redirect()->back()->with('success', 'Data SPDK berhasil dihapus.');
    }
}
