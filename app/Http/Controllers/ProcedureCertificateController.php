<?php

namespace App\Http\Controllers;

use App\Models\ProcedureCertificate;
use App\Services\ProcedureCertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Aksi tab Certification Evaluasi Prosedur + halaman cetak & verifikasi publik. */
class ProcedureCertificateController extends Controller
{
    public function __construct(private ProcedureCertificateService $certificateService) {}

    public function issueAll(Request $request): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $count = $this->certificateService->issueAll($request->integer('assessment_id'), (string) $request->user()->name);

        return back()->with('success', "{$count} sertifikat diterbitkan");
    }

    public function revoke(ProcedureCertificate $certificate): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $this->certificateService->revoke($certificate);

        return back()->with('success', 'Sertifikat dicabut');
    }

    public function sendReminders(): RedirectResponse
    {
        Gate::authorize('manage-procedure-evaluation');

        $count = $this->certificateService->expiringSoonCount();

        return back()->with('success', "{$count} karyawan akan dinotifikasi re-sertifikasi");
    }

    public function pdf(string $code): View
    {
        $certificate = $this->certificateService->findByCode($code);
        abort_unless($certificate, 404, 'Sertifikat tidak ditemukan.');

        return view('exports.procedure-certificate', $this->viewData($certificate));
    }

    /** Halaman publik (tanpa login) untuk scan QR sertifikat. */
    public function verify(Request $request): View
    {
        $code = trim((string) $request->query('code', ''));

        return view('exports.procedure-certificate-verify', [
            'code' => $code,
            'certificate' => $code === '' ? null : $this->certificateService->findByCode($code),
        ]);
    }

    private function viewData(ProcedureCertificate $certificate): array
    {
        $signer = $certificate->assessment?->signer;

        return [
            'certificate' => $certificate,
            'signerName' => $signer?->name ?? 'Group Leader',
            'signerJabatan' => $signer ? ($signer->jabatan_ss6 ?: 'Supervisor') : 'Group Leader / Supervisor',
            'signerSignature' => $signer ? $certificate->assessment->signer_signature : null,
            'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?'.http_build_query([
                'data' => route('procedure-certificates.verify', ['code' => $certificate->certificate_code]),
                'size' => '140x140',
                'bgcolor' => 'ffffff',
                'color' => '1a1a2e',
                'margin' => 6,
            ]),
        ];
    }
}
