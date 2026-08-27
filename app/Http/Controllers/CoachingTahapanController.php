<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCoachingTahapanRequest;
use App\Models\CoachingTahapan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingTahapanController extends Controller
{
    public function index()
    {
        $tahapans = CoachingTahapan::query()
            ->orderBy('urutan')
            ->get();

        return Inertia::render('CoachingClinic/Tahapan', [
            'tahapans' => $tahapans,
        ]);
    }

    public function update(UpdateCoachingTahapanRequest $request, CoachingTahapan $tahapan)
    {
        $tahapan->update($request->validated());

        return redirect()->back()
            ->with('success', 'Tahapan berhasil diperbarui.');
    }

    public function destroy(Request $request, CoachingTahapan $tahapan)
    {
        $tahapan->delete();

        return redirect()->back()
            ->with('success', 'Tahapan berhasil dihapus.');
    }

    public function reorder(Request $request, CoachingTahapan $tahapan)
    {
        $request->validate(['urutan' => 'required|integer|min:0']);

        $tahapan->update(['urutan' => $request->input('urutan')]);

        return response()->json(['success' => true, 'tahapan' => $tahapan]);
    }
}

