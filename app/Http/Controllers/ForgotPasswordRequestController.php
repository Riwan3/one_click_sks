<?php

namespace App\Http\Controllers;

use App\Models\ForgotPasswordRequest;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordRequestController extends Controller
{
    public function index(): Response
    {
        $requests = ForgotPasswordRequest::query()
            ->orderByRaw("FIELD(status, 'pending', 'completed')")
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('OneCore/ResetPasswordRequests/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_core_menu'),
            'requests' => $requests,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nrp' => 'required|string|max:255',
            'no_wa' => 'required|string|max:255',
        ]);

        ForgotPasswordRequest::create([
            'nama' => $validated['nama'],
            'nrp' => $validated['nrp'],
            'no_wa' => $validated['no_wa'],
            'status' => 'pending',
        ]);

        return redirect()->route('login')->with('success', 'Permintaan reset password berhasil dikirim ke Admin.');
    }

    public function complete(ForgotPasswordRequest $forgotPasswordRequest): RedirectResponse
    {
        $forgotPasswordRequest->update(['status' => 'completed']);

        return redirect()->back()->with('success', 'Request reset password berhasil diselesaikan.');
    }
}
