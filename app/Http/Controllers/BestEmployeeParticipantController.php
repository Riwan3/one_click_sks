<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignBestEmployeeParticipantsRequest;
use App\Models\BestEmployeeCampaign;
use App\Services\BestEmployeeParticipantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BestEmployeeParticipantController extends Controller
{
    public function __construct(private BestEmployeeParticipantService $participants) {}

    public function filterOptions(): JsonResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);

        return response()->json($this->participants->filterOptions());
    }

    public function candidates(Request $request, BestEmployeeCampaign $campaign): JsonResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);

        $filters = $request->only(['q', 'jabatan', 'unit', 'lokasi']);

        return response()->json($this->participants->searchCandidates($campaign, $filters, (int) $request->integer('page', 1)));
    }

    public function assign(AssignBestEmployeeParticipantsRequest $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        $locked = $campaign->participantsLocked();
        if ($locked && ! ($request->boolean('force') && $request->filled('reason'))) {
            return back()->withErrors(['reason' => 'Peserta tidak dapat ditambahkan karena kegiatan sudah dimulai/selesai. Centang override dan isi alasan untuk melanjutkan.']);
        }

        $userIds = $request->boolean('select_all')
            ? $this->participants->resolveActiveUserIdsByFilter($request->only(['q', 'jabatan', 'unit', 'lokasi']))
            : $request->input('user_ids', []);

        if (empty($userIds)) {
            return back()->withErrors(['user_ids' => 'Tidak ada peserta yang dipilih.']);
        }

        $result = $this->participants->assignBulk($campaign, $userIds, Auth::id());

        return back()->with('success', "Berhasil: {$result['assigned']} baru, {$result['already']} sudah ada, {$result['inactive']} nonaktif dilewati, {$result['not_found']} tidak ditemukan.");
    }

    public function remove(Request $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);

        $request->validate([
            'user_id' => ['required', 'integer'],
            'force' => ['nullable', 'boolean'],
            'reason' => ['required_if:force,1', 'nullable', 'string', 'max:500'],
        ], ['reason.required_if' => 'Alasan wajib diisi.']);

        $participant = $campaign->participants()->where('user_id', $request->integer('user_id'))->where('status', 'assigned')->first();
        abort_unless($participant, 404, 'Peserta tidak ditemukan atau sudah dikeluarkan.');

        $hasAttempt = $participant->attempts()->where('status', '<>', 'reset')->exists();
        $locked = $campaign->participantsLocked();

        if (($hasAttempt || $locked) && ! ($request->boolean('force') && $request->filled('reason'))) {
            $message = $hasAttempt
                ? 'Peserta ini sudah memiliki attempt dan tidak dapat dikeluarkan sembarangan. Centang override dan isi alasan untuk melanjutkan.'
                : 'Peserta tidak dapat dikeluarkan karena kegiatan sudah dimulai/selesai. Centang override dan isi alasan untuk melanjutkan.';

            return back()->withErrors(['reason' => $message]);
        }

        $this->participants->remove($campaign, $request->integer('user_id'), Auth::id(), $request->boolean('force') ? $request->string('reason')->toString() : null);

        return back()->with('success', 'Peserta dikeluarkan.');
    }
}
