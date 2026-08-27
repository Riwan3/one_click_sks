<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    private const BULAN_INDONESIA = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];

    public function index(): Response
    {
        $user = Auth::user();

        // Increment views_count for all announcements on view if wanted
        $announcements = Announcement::with('user:id,name,username')
            ->latest()
            ->get()
            ->map(function ($item) {
                $created = Carbon::parse($item->created_at);
                $bulanIndo = self::BULAN_INDONESIA[$created->format('F')] ?? $created->format('F');

                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'content' => $item->content,
                    'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                    'views_count' => $item->views_count,
                    'author_name' => $item->user?->name ?? 'SYSTEM',
                    'date_formatted' => sprintf('%s %s %s, %s', $created->format('d'), $bulanIndo, $created->format('Y'), $created->format('H:i')),
                ];
            });

        return Inertia::render('Announcement/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'announcements' => $announcements,
            'menu' => config('one_procedure_menu'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        Announcement::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'views_count' => 1,
        ]);

        return redirect()->back()->with('success', 'Pengumuman berhasil dipublikasikan!');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        $announcement->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus!');
    }
}
