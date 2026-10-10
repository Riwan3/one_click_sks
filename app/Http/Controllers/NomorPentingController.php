<?php

namespace App\Http\Controllers;

use App\Models\NomorPenting;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NomorPentingController extends Controller
{
    public function index(): Response
    {
        $list = NomorPenting::query()
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $grouped = $list->groupBy('kategori');

        return Inertia::render('OneCore/NomorPenting/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'list' => $list,
            'grouped' => $grouped,
        ]);
    }

    /**
     * API mobile: hanya nomor yang aktif (admin pun melihat yang aktif saja, karena
     * pengelolaan data ada di web), dikelompokkan per kategori sesuai urutan.
     */
    public function apiMobile(): JsonResponse
    {
        $groups = NomorPenting::query()
            ->where('aktif', true)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['nama', 'jabatan', 'no_telepon', 'kategori'])
            ->groupBy('kategori')
            ->map(fn ($items, $kategori) => [
                'kategori' => $kategori,
                'items' => $items->map(fn ($n) => [
                    'nama' => $n->nama,
                    'jabatan' => $n->jabatan,
                    'no_telepon' => $n->no_telepon,
                ])->values(),
            ])
            ->values();

        return response()->json(['groups' => $groups]);
    }

    /**
     * API untuk mengambil daftar nomor penting untuk Modal & Quick Access.
     */
    public function apiList(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin()
            || ($user && in_array(strtolower((string) $user->role), ['admin', 'admin_produksi'], true))
            || ($user && str_contains(strtolower((string) $user->jabatan), 'admin'));

        $query = NomorPenting::query();
        if (! $isAdmin) {
            $query->where('aktif', true);
        }

        $list = $query
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $grouped = $list->groupBy('kategori');

        return response()->json([
            'status' => 'success',
            'list' => $list,
            'grouped' => $grouped,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin()
            || ($user && in_array(strtolower((string) $user->role), ['admin', 'admin_produksi'], true))
            || ($user && str_contains(strtolower((string) $user->jabatan), 'admin'));

        abort_unless($isAdmin, 403, 'Akses ditolak. Hanya role Admin yang dapat menambah nomor penting.');

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:255',
            'kategori' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'aktif' => 'boolean',
        ]);

        $item = NomorPenting::create([
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'kategori' => $validated['kategori'] ?? 'Umum',
            'urutan' => (int) ($validated['urutan'] ?? 0),
            'aktif' => $request->boolean('aktif', true),
        ]);

        if ($request->wantsJson() || $request->is('*/api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Nomor penting berhasil ditambahkan.',
                'item' => $item,
            ]);
        }

        return redirect()->back()->with('success', 'Nomor penting berhasil ditambahkan.');
    }

    public function update(Request $request, NomorPenting $nomorPenting): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin()
            || ($user && in_array(strtolower((string) $user->role), ['admin', 'admin_produksi'], true))
            || ($user && str_contains(strtolower((string) $user->jabatan), 'admin'));

        abort_unless($isAdmin, 403, 'Akses ditolak. Hanya role Admin yang dapat mengubah nomor penting.');

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:255',
            'kategori' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'aktif' => 'boolean',
        ]);

        $nomorPenting->update([
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'kategori' => $validated['kategori'] ?? 'Umum',
            'urutan' => (int) ($validated['urutan'] ?? 0),
            'aktif' => $request->boolean('aktif', true),
        ]);

        if ($request->wantsJson() || $request->is('*/api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Nomor penting berhasil diperbarui.',
                'item' => $nomorPenting,
            ]);
        }

        return redirect()->back()->with('success', 'Nomor penting berhasil diperbarui.');
    }

    public function destroy(Request $request, NomorPenting $nomorPenting): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin()
            || ($user && in_array(strtolower((string) $user->role), ['admin', 'admin_produksi'], true))
            || ($user && str_contains(strtolower((string) $user->jabatan), 'admin'));

        abort_unless($isAdmin, 403, 'Akses ditolak. Hanya role Admin yang dapat menghapus nomor penting.');

        $nomorPenting->delete();

        if ($request->wantsJson() || $request->is('*/api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Nomor penting berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Nomor penting berhasil dihapus.');
    }
}
