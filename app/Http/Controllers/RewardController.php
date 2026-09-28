<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Reward;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    private function canManage(): bool
    {
        $user = Auth::user();
        if (! $user) return false;
        $role = strtolower($user->role ?? '');
        return $user->isAdmin() || in_array($role, ['admin', 'super-admin', 'group-leader', 'pimpinan', 'management']);
    }

    public function index(): Response
    {
        $rewards = Reward::with(['user:id,name,username', 'employee:id,nrp,name,jabatan_ss6,unit,foto'])
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
                    'recipient' => $item->employee?->name ?? $item->user?->name ?? 'Semua Karyawan',
                    'recipient_nrp' => $item->employee?->nrp ?? null,
                    'recipient_jabatan' => $item->employee?->jabatan_ss6 ?? null,
                    'recipient_unit' => $item->employee?->unit ?? null,
                    'recipient_foto' => $item->employee?->foto ? asset('storage/' . $item->employee->foto) : null,
                    'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                ];
            });

        $employees = Employee::orderBy('name')
            ->select('id', 'nrp', 'name', 'jabatan_ss6', 'unit')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'nrp' => $e->nrp,
                'name' => $e->name,
                'jabatan' => $e->jabatan_ss6,
                'unit' => $e->unit,
                'label' => "{$e->nrp} - {$e->name}" . ($e->jabatan_ss6 ? " ({$e->jabatan_ss6})" : ''),
            ]);

        return Inertia::render('Reward/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'rewards' => $rewards,
            'employees' => $employees,
            'canManage' => $this->canManage(),
            'menu' => config('one_procedure_menu'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canManage()) {
            abort(403, 'Hanya Admin atau Group Leader yang dapat menambahkan penghargaan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'reward_date' => 'required|date',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
            'employee_id' => 'nullable|exists:employees,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'title.required' => 'Judul penghargaan wajib diisi.',
            'reward_date.required' => 'Tanggal penghargaan wajib diisi.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('photos/rewards', 'public');
        }

        $employeeIds = $request->input('employee_ids', []);
        if (empty($employeeIds) && !empty($validated['employee_id'])) {
            $employeeIds = [$validated['employee_id']];
        }

        if (!empty($employeeIds)) {
            foreach ($employeeIds as $empId) {
                Reward::create([
                    'user_id' => Auth::id(),
                    'employee_id' => $empId,
                    'title' => $validated['title'],
                    'type' => $validated['type'],
                    'category' => $validated['category'],
                    'description' => $validated['description'] ?? null,
                    'image_path' => $imagePath,
                    'reward_date' => $validated['reward_date'],
                ]);
            }
            $count = count($employeeIds);
            return redirect()->back()->with('success', "Penghargaan berhasil ditambahkan untuk {$count} karyawan!");
        }

        Reward::create([
            'user_id' => Auth::id(),
            'employee_id' => null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'reward_date' => $validated['reward_date'],
        ]);

        return redirect()->back()->with('success', 'Penghargaan berhasil ditambahkan!');
    }

    public function destroy(Reward $reward): RedirectResponse
    {
        if (! $this->canManage()) {
            abort(403, 'Hanya Admin atau Group Leader yang dapat menghapus penghargaan.');
        }

        if ($reward->image_path && Storage::disk('public')->exists($reward->image_path)) {
            Storage::disk('public')->delete($reward->image_path);
        }

        $reward->delete();

        return redirect()->back()->with('success', 'Penghargaan berhasil dihapus!');
    }

    public function markSeen(Reward $reward): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        if ($userId) {
            \App\Models\RewardRead::firstOrCreate([
                'reward_id' => $reward->id,
                'user_id' => $userId,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
