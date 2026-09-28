@php
    $employee = $certificate->employee;
    $jabatan = $employee?->jabatan_ss6 ?: ($employee?->jabatan_roster ?: '—');
    $isLifetime = $certificate->isLifetime();
    $issueDate = $certificate->issue_date->translatedFormat('d F Y');
    $validDate = $isLifetime ? 'Selamanya ♾️' : $certificate->valid_until->translatedFormat('d F Y');
    $statusStyle = [
        'active' => ['#dcfce7', '#15803d'],
        'expiring_soon' => ['#fef9c3', '#854d0e'],
    ][$certificate->status->value] ?? ['#fee2e2', '#b91c1c'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Sertifikat — {{ $certificate->certificate_code }}</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Georgia',serif;background:#f0f0f0;display:flex;justify-content:center;align-items:flex-start;min-height:100vh;padding:20px;}
  .no-print{background:#1e293b;color:#fff;padding:12px 20px;margin-bottom:20px;border-radius:10px;display:flex;gap:10px;align-items:center;}
  .no-print button{padding:8px 20px;border-radius:20px;border:none;cursor:pointer;font-weight:600;}
  .btn-print{background:#b91c1c;color:#fff;}
  .btn-close-btn{background:#e2e8f0;color:#1e293b;}
  .cert-page{width:794px;min-height:562px;background:#fff;position:relative;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);}
  .cert-page::before{content:'';position:absolute;inset:12px;border:2px solid #c9a84c;pointer-events:none;z-index:10;}
  .cert-page::after{content:'';position:absolute;inset:18px;border:1px solid #e8d5a0;pointer-events:none;z-index:10;}
  .cert-header{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);padding:22px 40px 18px;display:flex;align-items:center;justify-content:space-between;}
  .cert-header-title{text-align:center;flex:1;}
  .cert-header-title .small{color:rgba(255,255,255,.6);font-size:9px;letter-spacing:2px;text-transform:uppercase;}
  .cert-header-title .main{color:#c9a84c;font-size:14px;font-weight:700;letter-spacing:1px;margin-top:3px;}
  .cert-body{padding:30px 50px 20px;text-align:center;position:relative;}
  .cert-watermark{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:.04;pointer-events:none;}
  .cert-watermark img{width:260px;}
  .cert-subtitle{font-size:10px;color:#94a3b8;letter-spacing:3px;text-transform:uppercase;margin-bottom:6px;}
  .cert-divider{width:80px;height:2px;background:linear-gradient(90deg,transparent,#c9a84c,transparent);margin:14px auto;}
  .cert-name{font-size:24px;color:#0f3460;font-weight:700;font-style:italic;margin-bottom:2px;}
  .cert-nrp{font-size:10px;color:#64748b;letter-spacing:1px;}
  .cert-desc{margin:12px 0 8px;font-size:10px;color:#475569;}
  .cert-procedure{font-size:14px;color:#1a1a2e;font-weight:700;margin-bottom:4px;}
  .cert-score-wrap{display:inline-flex;align-items:center;gap:16px;margin:10px 0;background:#f8f9fc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 24px;}
  .cert-score-item{text-align:center;}
  .cert-score-item .val{font-size:18px;font-weight:800;color:#0f3460;}
  .cert-score-item .lbl{font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;}
  .cert-score-div{width:1px;height:30px;background:#e2e8f0;}
  .cert-footer{padding:12px 50px 24px;display:flex;align-items:flex-end;justify-content:space-between;}
  .cert-sig{text-align:center;width:160px;}
  .cert-sig-line{height:1px;background:#1a1a2e;margin-bottom:4px;}
  .cert-sig-name{font-size:10px;font-weight:700;color:#1a1a2e;}
  .cert-sig-role{font-size:9px;color:#64748b;}
  .cert-qr-wrap{text-align:center;}
  .cert-qr-wrap img{width:80px;height:80px;border:1px solid #e2e8f0;border-radius:4px;}
  .cert-qr-label{font-size:7px;color:#94a3b8;margin-top:3px;letter-spacing:.5px;}
  .cert-code-wrap{text-align:center;}
  .cert-code{font-family:monospace;font-size:11px;font-weight:700;color:#0f3460;letter-spacing:2px;background:#f0f4ff;padding:4px 12px;border-radius:20px;border:1px solid #bfdbfe;}
  .cert-dates{font-size:8px;color:#94a3b8;margin-top:6px;letter-spacing:.5px;}
  .cert-status{display:inline-block;padding:2px 10px;border-radius:20px;font-size:8px;font-weight:700;margin-top:4px;}
  @media print{
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    body{background:#fff;padding:0;}
    .no-print{display:none!important;}
    .cert-page{box-shadow:none;width:100%;margin:0;}
  }
  @page{size:A4 landscape;margin:10mm;}
</style>
</head>
<body>
<div>
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Download PDF</button>
    <button class="btn-close-btn" onclick="window.close()">✕ Tutup</button>
    <span style="font-size:.8rem;opacity:.7;margin-left:8px;">{{ $certificate->certificate_code }}</span>
  </div>

  <div class="cert-page">
    <div class="cert-header">
      <div style="display:flex;flex-direction:column;justify-content:center;gap:2px;">
        <div style="color:#c9a84c;font-size:11px;font-weight:800;letter-spacing:1.5px;">SAFE &amp; STRONG</div>
        <div style="color:rgba(255,255,255,.5);font-size:7px;letter-spacing:1px;">OPERATIONAL PERFORMANCE</div>
      </div>
      <div class="cert-header-title">
        <div class="small">PT. Putra Perkasa Abadi Site Adaro Indonesia</div>
        <div class="main">SERTIFIKAT KOMPETENSI</div>
      </div>
      <div style="width:140px;text-align:right;">
        <div style="color:rgba(255,255,255,.5);font-size:8px;letter-spacing:1px;">CERTIFICATE OF<br>COMPETENCY</div>
      </div>
    </div>

    <div class="cert-body">
      <div class="cert-watermark"><img src="{{ asset('images/title.png') }}" alt=""></div>

      <div class="cert-subtitle">Dengan bangga diberikan kepada</div>
      <div class="cert-name">{{ $employee?->name ?? 'NRP '.$certificate->nrp }}</div>
      <div class="cert-nrp">NRP: {{ $certificate->nrp }} &nbsp;|&nbsp; {{ $jabatan }}</div>

      <div class="cert-divider"></div>

      <div class="cert-desc">Telah berhasil menyelesaikan evaluasi prosedur</div>
      <div class="cert-procedure">{{ $certificate->assessment?->procedure?->title }}</div>

      <div style="margin-top:12px;">
        <div class="cert-score-wrap">
          <div class="cert-score-item"><div class="val">{{ number_format((float) $certificate->score, 1) }}</div><div class="lbl">Nilai</div></div>
          <div class="cert-score-div"></div>
          <div class="cert-score-item"><div class="val">{{ $certificate->assessment?->passing_grade }}%</div><div class="lbl">Pass Grade</div></div>
          <div class="cert-score-div"></div>
          <div class="cert-score-item"><div class="val">{{ $issueDate }}</div><div class="lbl">Diterbitkan</div></div>
          @unless ($isLifetime)
            <div class="cert-score-div"></div>
            <div class="cert-score-item"><div class="val">{{ $validDate }}</div><div class="lbl">Berlaku Hingga</div></div>
          @endunless
        </div>
        @if ($isLifetime)
          <div style="margin-top:8px;">
            <span style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #86efac;border-radius:20px;padding:4px 16px;">
              <span style="font-size:14px;color:#15803d;font-weight:800;">&#x221E;</span>
              <span style="font-size:10px;color:#15803d;font-weight:700;letter-spacing:1px;text-transform:uppercase;">Berlaku Selamanya</span>
            </span>
          </div>
        @endif
      </div>
    </div>

    <div class="cert-footer">
      <div class="cert-sig">
        @if ($signerSignature)
          <div style="height:52px;display:flex;align-items:center;justify-content:center;gap:5px;">
            <img src="{{ $signerSignature }}" alt="TTD Digital" style="height:46px;width:46px;object-fit:contain;">
            <div style="text-align:left;line-height:1.3;">
              <div style="font-size:7px;font-weight:800;color:#15803d;letter-spacing:.3px;">&#10003; TERVERIFIKASI</div>
              <div style="font-size:6px;color:#94a3b8;letter-spacing:.2px;">Tanda Tangan<br>Digital OneClick</div>
            </div>
          </div>
        @else
          <div style="height:52px;display:flex;align-items:flex-end;justify-content:center;">
            <span style="font-size:8px;color:#cbd5e1;font-style:italic;">Belum ditandatangani</span>
          </div>
        @endif
        <div class="cert-sig-line"></div>
        <div class="cert-sig-name">{{ $signerName }}</div>
        <div class="cert-sig-role">{{ $signerJabatan }}</div>
      </div>

      <div class="cert-code-wrap">
        <div class="cert-code">{{ $certificate->certificate_code }}</div>
        <div class="cert-dates">
          Diterbitkan: {{ $issueDate }}{!! $isLifetime ? ' &nbsp;&bull;&nbsp; Berlaku Selamanya' : ' &mdash; '.e($validDate) !!}
        </div>
        <div class="cert-status" style="background:{{ $statusStyle[0] }};color:{{ $statusStyle[1] }};">
          {{ strtoupper(str_replace('_', ' ', $certificate->status->value)) }}
        </div>
      </div>

      <div class="cert-qr-wrap">
        <img src="{{ $qrUrl }}" alt="QR Verify" onerror="this.style.display='none'">
        <div class="cert-qr-label">SCAN TO VERIFY</div>
      </div>
    </div>
  </div>
</div>
<script>
  if (window.location.search.includes('autoprint=1')) window.onload = () => window.print();
</script>
</body>
</html>
