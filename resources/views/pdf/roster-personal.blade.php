<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roster - {{ $employee['nrp'] ?? '' }} {{ $employee['nama'] ?? '' }} - {{ $monthLabel }} {{ $tahun }}</title>
    <style>
        {!! $inlineCss !!}
    </style>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        body {
            background-color: #ffffff;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="p-6">
    <!-- Action Toolbar (Hidden during print) -->
    <div class="no-print mx-auto mb-6 flex max-w-2xl items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 shadow-sm">
        <div class="text-xs text-slate-600">
            Pratinjau Cetak Roster <strong>{{ $employee['nrp'] ?? '' }} - {{ $employee['nama'] ?? '' }}</strong> ({{ $monthLabel }} {{ $tahun }})
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="flex items-center gap-1.5 rounded-lg bg-red-500 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-red-600 transition shadow"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak Sekarang
            </button>
            <button
                type="button"
                onclick="window.close()"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
            >
                Tutup
            </button>
        </div>
    </div>

    <!-- Printable Card Container -->
    <div class="mx-auto max-w-2xl border border-slate-300 bg-white p-6 shadow-sm print:border-0 print:p-0 print:shadow-none">
        
        <!-- Header Logos & Title -->
        <div class="mb-4 flex items-center justify-between gap-4 border-b pb-3">
            <div class="flex items-center gap-2.5">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Safe & Strong" style="height: 42px; width: auto; object-fit: contain;">
                @else
                    <img src="/images/images.png" alt="Safe & Strong" style="height: 42px; width: auto; object-fit: contain;">
                @endif
                <div class="leading-tight">
                    <div style="font-weight: 900; font-size: 13px; color: #0f172a; letter-spacing: -0.025em; line-height: 1.1;">SAFE & STRONG</div>
                    <div style="font-weight: 700; font-size: 9px; color: #475569; letter-spacing: 0.05em; margin-top: 2px;">OPERATIONAL PERFORMANCE</div>
                </div>
            </div>
            <div>
                @if(!empty($synergyDataUri))
                    <img src="{{ $synergyDataUri }}" alt="Synergy" style="height: 44px; width: auto; object-fit: contain;">
                @else
                    <img src="/images/logo_synergy.png" alt="Synergy" style="height: 44px; width: auto; object-fit: contain;">
                @endif
            </div>
        </div>

        <div class="mb-3 border-y-2 border-slate-900 py-1.5 text-center text-xs font-black uppercase tracking-wider text-slate-900">
            ROSTER KARYAWAN DEPT. PRODUKSI - PPA JOBSITE ADARO INDONESIA
        </div>

        <!-- Info + Unit Box -->
        <div class="mb-3 flex items-start justify-between gap-4">
            <div class="space-y-1 text-xs">
                <div class="flex">
                    <span class="w-20 font-bold text-slate-800">NAMA</span>
                    <span class="w-4 font-bold text-slate-800">:</span>
                    <span class="font-bold text-slate-900">{{ $employee['nama'] ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-20 font-bold text-slate-800">NRP</span>
                    <span class="w-4 font-bold text-slate-800">:</span>
                    <span class="font-bold text-slate-900">{{ $employee['nrp'] ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-20 font-bold text-slate-800">JABATAN</span>
                    <span class="w-4 font-bold text-slate-800">:</span>
                    <span class="font-semibold text-slate-800">{{ $employee['jabatan'] ?? '-' }}</span>
                </div>
            </div>
            <div class="min-w-36 border-2 border-slate-900 text-center rounded-xs overflow-hidden">
                <div class="border-b-2 border-slate-900 bg-white px-4 py-0.5 text-xs font-black text-slate-900">
                    UNIT
                </div>
                <div class="flex min-h-10 items-center justify-center px-4 py-1 text-base font-black text-slate-900">
                    {{ $employee['unit'] ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Periode Header -->
        <div class="mb-3 border border-slate-900 bg-slate-50 py-1 text-center text-xs font-extrabold text-slate-900 uppercase">
            PERIODE {{ strtoupper($monthLabel) }} {{ $tahun }}
        </div>

        <!-- Roster Table -->
        <table class="w-full border-collapse text-xs border border-slate-900">
            <thead>
                <tr class="bg-slate-200 text-slate-900">
                    <th class="border border-slate-900 px-1 py-1 font-bold text-center w-10">NO</th>
                    <th class="border border-slate-900 px-2 py-1 font-bold text-center w-24">HARI</th>
                    <th class="border border-slate-900 px-2 py-1 font-bold text-center w-40">TANGGAL</th>
                    <th class="border border-slate-900 px-2 py-1 font-bold text-center w-20">ROSTER</th>
                    <th class="border border-slate-900 px-2 py-1 font-bold text-center">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                @endphp
                @foreach ($days as $d)
                    @php
                        $dow = $d['dayOfWeek'] ?? 0;
                        $isMinggu = ($dow === 0);
                    @endphp
                    @php
                        $kodeUpper = strtoupper(trim($d['kode'] ?? ''));
                        $kodeColor = match($kodeUpper) {
                            'N' => 'color: #2563eb;',
                            'D' => 'color: #16a34a;',
                            'OFF' => 'color: #dc2626;',
                            'CT' => 'color: #d97706;',
                            default => 'color: #0f172a;',
                        };
                    @endphp
                    <tr class="{{ $isMinggu ? 'bg-[#fef9c3]' : 'bg-white' }}" style="{{ $isMinggu ? 'background-color: #fef9c3;' : '' }}">
                        <td class="border border-slate-900 px-1 py-0.5 text-center font-medium">{{ $d['day'] }}</td>
                        <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">{{ $dayNames[$dow] ?? '-' }}</td>
                        <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">
                            {{ sprintf('%02d', $d['day']) }} {{ $monthLabel }} {{ $tahun }}
                        </td>
                        <td class="border border-slate-900 px-1 py-0.5 text-center font-extrabold" style="{{ $kodeColor }}">
                            {{ $d['kode'] ?? '' }}
                        </td>
                        <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">
                            {{ $d['keterangan'] ?? '' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer Section (V1 Note, Slogans & Signatures) -->
        <div class="mt-4 pt-2 text-xs">
            <div class="flex items-start justify-between gap-4">
                <!-- Left notes & slogan -->
                <div class="space-y-2">
                    <div>
                        <div class="font-extrabold text-red-600">**Note</div>
                        <div class="text-[11px] text-slate-800 font-medium">
                            - Update roster per hari harap menyesuaikan dengan SS6.
                        </div>
                    </div>
                    <div class="pt-1 text-xs font-black leading-tight">
                        <div style="color: #16a34a;">Saya Sehat, Saya Selamat,</div>
                        <div style="color: #dc2626;">Saya Semangat, Saya Manfaat!!!!!</div>
                    </div>
                    <div class="pt-2">
                        @if(!empty($safeStrongDataUri))
                            <img src="{{ $safeStrongDataUri }}" alt="SAFE & STRONG" style="height: 38px; width: auto; object-fit: contain;">
                        @else
                            <img src="/images/logo_safe_strong.png" alt="SAFE & STRONG" style="height: 38px; width: auto; object-fit: contain;">
                        @endif
                    </div>
                </div>

                <!-- Right signature block -->
                <div class="text-right shrink-0 min-w-[200px]">
                    <div class="text-slate-900 font-medium">Mengetahui,</div>
                    <div class="text-slate-900 font-medium mt-0.5">Tanjung, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="height: 55px;"></div>
                    <div class="font-bold text-slate-900 text-sm" contenteditable="true" title="Klik untuk edit">{{ $signerName ?? 'Radhitya HS' }}</div>
                    <div class="text-xs italic text-slate-800" contenteditable="true" title="Klik untuk edit">{{ $signerTitle ?? 'Group Leader Manpower' }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto trigger print dialog if opened in standalone browser view
        window.addEventListener('DOMContentLoaded', () => {
            if (!window.location.search.includes('no_auto=1')) {
                setTimeout(() => {
                    window.print();
                }, 400);
            }
        });
    </script>
</body>
</html>
