<?php

namespace App\Http\Controllers;

use App\Models\NomorPenting;
use App\Support\MenuAccess;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:255',
            'kategori' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'aktif' => 'boolean',
        ]);

        NomorPenting::create([
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'] ?? null,
            'no_telepon' => $validated['no_telepon'] ?? null,
            'kategori' => $validated['kategori'] ?? 'Umum',
            'urutan' => (int) ($validated['urutan'] ?? 0),
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->back()->with('success', 'Nomor penting berhasil ditambahkan.');
    }

    public function update(Request $request, NomorPenting $nomorPenting): RedirectResponse
    {
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

        return redirect()->back()->with('success', 'Nomor penting berhasil diperbarui.');
    }

    public function destroy(NomorPenting $nomorPenting): RedirectResponse
    {
        $nomorPenting->delete();

        return redirect()->back()->with('success', 'Nomor penting berhasil dihapus.');
    }
}
