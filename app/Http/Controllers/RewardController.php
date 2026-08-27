<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RewardController extends Controller
{
    private const BULAN_INDONESIA = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];

    public function index(): Response
    {
        $rewards = Reward::with('user:id,name,username')
            ->orderBy('reward_date', 'desc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::parse($item->reward_date);
                $bulanIndo = self::BULAN_INDONESIA[$date->format('F')] ?? $date->format('F');

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'category' => $item->category,
                    'description' => $item->description,
                    'reward_date' => $item->reward_date->format('Y-m-d'),
                    'date_formatted' => sprintf('%s %s %s', $date->format('d'), $bulanIndo, $date->format('Y')),
                    'recipient' => $item->user?->name ?? 'Semua Karyawan',
                ];
            });

        return Inertia::render('Reward/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'rewards' => $rewards,
            'menu' => config('one_procedure_menu'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'reward_date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Reward::create([
            'user_id' => $validated['user_id'] ?? Auth::id(),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'reward_date' => $validated['reward_date'],
        ]);

        return redirect()->back()->with('success', 'Penghargaan berhasil ditambahkan!');
    }

    public function destroy(Reward $reward): RedirectResponse
    {
        $reward->delete();

        return redirect()->back()->with('success', 'Penghargaan berhasil dihapus!');
    }
}
