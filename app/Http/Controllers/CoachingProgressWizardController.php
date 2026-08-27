<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachingProgressRequest;
use App\Models\CoachingClinic;
use App\Models\CoachingClinicProgress;
use App\Models\CoachingTahapan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingProgressWizardController extends Controller
{
    public function create()
    {
        $clinics = CoachingClinic::where('created_by', auth()->id())->get();
        $tahapans = CoachingTahapan::active()->ordered()->get();

        return Inertia::render('CoachingClinic/Wizard', [
            'clinics' => $clinics,
            'tahapans' => $tahapans,
        ]);
    }

    public function store(StoreCoachingProgressRequest $request)
    {
        $clinic = CoachingClinic::findOrFail($request->input('clinic_id'));

        $tahapanIds = $request->input('tahapan_ids', []);
        $progressPct = $request->input('progress_pct', 0);
        $notes = $request->input('notes');

        foreach ($tahapanIds as $tahapanId) {
            CoachingClinicProgress::updateOrCreate(
                [
                    'coaching_clinic_id' => $clinic->id,
                    'coaching_tahapan_id' => $tahapanId,
                ],
                [
                    'progress_pct' => $progressPct,
                    'notes' => $notes,
                ]
            );
        }

        return redirect()->route('coaching-clinic.show', $clinic)
            ->with('success', 'Progress berhasil disimpan.');
    }
}

