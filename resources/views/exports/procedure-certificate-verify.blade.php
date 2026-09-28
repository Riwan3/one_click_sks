@php
    $status = $certificate?->status->value;
    $employee = $certificate?->employee;
    $fields = $certificate ? [
        'Nama' => $employee?->name ?? 'NRP '.$certificate->nrp,
        'NRP' => $certificate->nrp,
        'Jabatan' => $employee?->jabatan_roster ?? '—',
        'Prosedur' => $certificate->assessment?->procedure?->title,
        'Nilai' => number_format((float) $certificate->score, 1),
        'Diterbitkan' => $certificate->issue_date->format('d M Y'),
    ] : [];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Sertifikat — OneClick</title>
    <link rel="icon" type="image/png" href="{{ asset('images/title.png') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 16px; background: linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; color: #1e293b; }
        .verify-card { background: #fff; border-radius: 20px; padding: 40px; max-width: 480px; width: 100%; box-shadow: 0 30px 80px rgba(0,0,0,.4); text-align: center; }
        .verify-icon { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; font-weight: 800; }
        .verify-icon.valid { background: #dcfce7; color: #15803d; }
        .verify-icon.invalid { background: #fee2e2; color: #b91c1c; }
        h4 { margin: 0 0 8px; font-size: 1.4rem; }
        .muted { color: #64748b; font-size: .85rem; }
        .text-danger { color: #b91c1c; } .text-warning { color: #b45309; } .text-success { color: #15803d; }
        .cert-field { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: .85rem; text-align: left; }
        .cert-field:last-child { border: none; }
        .cert-field .lbl { color: #94a3b8; }
        .cert-field .val { font-weight: 600; text-align: right; max-width: 60%; }
        .code-badge { font-family: monospace; background: #f0f4ff; color: #0f3460; padding: 6px 16px; border-radius: 20px; font-size: .9rem; font-weight: 700; letter-spacing: 2px; display: inline-block; margin: 12px 0; border: 1px solid #bfdbfe; }
        .alert { background: #fef9c3; color: #854d0e; border-radius: 10px; padding: 8px 12px; font-size: .8rem; margin-top: 8px; }
        form { display: flex; margin-top: 16px; }
        form input { flex: 1; padding: 10px; border: 1px solid #cbd5e1; border-radius: 10px 0 0 10px; }
        form button { padding: 10px 16px; border: none; background: #dc2626; color: #fff; border-radius: 0 10px 10px 0; cursor: pointer; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: .72rem; color: #64748b; }
    </style>
</head>
<body>
<div class="verify-card">
    <img src="{{ asset('images/title.png') }}" alt="OneClick" style="height:48px;margin-bottom:16px;">

    @if ($code === '')
        <div class="verify-icon invalid">?</div>
        <h4>Kode Tidak Ditemukan</h4>
        <p class="muted">Scan QR code pada sertifikat untuk verifikasi.</p>
        <form>
            <input type="text" name="code" placeholder="Masukkan kode sertifikat...">
            <button>Cek</button>
        </form>
    @elseif (! $certificate)
        <div class="verify-icon invalid">✕</div>
        <h4 class="text-danger">Sertifikat Tidak Valid</h4>
        <p class="muted">Kode <strong>{{ $code }}</strong> tidak ditemukan dalam sistem.</p>
    @elseif ($status === 'revoked')
        <div class="verify-icon invalid">⊘</div>
        <h4 class="text-danger">Sertifikat Dicabut</h4>
        <div class="code-badge">{{ $certificate->certificate_code }}</div>
        <p class="muted">Sertifikat ini telah dicabut dan tidak berlaku.</p>
    @elseif ($status === 'expired')
        <div class="verify-icon invalid">!</div>
        <h4 class="text-warning">Sertifikat Kadaluarsa</h4>
        <div class="code-badge">{{ $certificate->certificate_code }}</div>
        <div style="margin-top:12px;">
            @foreach (['Nama', 'NRP', 'Prosedur'] as $label)
                <div class="cert-field"><span class="lbl">{{ $label }}</span><span class="val">{{ $fields[$label] }}</span></div>
            @endforeach
            <div class="cert-field"><span class="lbl">Kadaluarsa</span><span class="val text-danger">{{ $certificate->valid_until->format('d M Y') }}</span></div>
        </div>
    @else
        <div class="verify-icon valid">✓</div>
        <h4 class="text-success">Sertifikat Valid ✓</h4>
        <div class="code-badge">{{ $certificate->certificate_code }}</div>
        @if ($status === 'expiring_soon')
            <div class="alert">Sertifikat akan berakhir pada {{ $certificate->valid_until->format('d M Y') }}</div>
        @endif
        <div style="margin-top:12px;">
            @foreach ($fields as $label => $value)
                <div class="cert-field"><span class="lbl">{{ $label }}</span><span class="val">{{ $value }}</span></div>
            @endforeach
            <div class="cert-field">
                <span class="lbl">Berlaku Hingga</span>
                <span class="val text-success">{{ $certificate->isLifetime() ? 'Selamanya' : $certificate->valid_until->format('d M Y') }}</span>
            </div>
        </div>
    @endif

    <div class="footer">
        Verifikasi ini dikeluarkan oleh sistem <strong>OneClick</strong> — PT. Putra Perkasa Abadi Site Adaro Indonesia<br>
        {{ now()->format('d M Y H:i') }} WIB
    </div>
</div>
</body>
</html>
