<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Formulir DMS</title>
    <style>{!! $inlineCss !!}</style>
    <style>
        body { background: #fff; }
    </style>
</head>
<body class="font-sans text-slate-800">
    <div class="mb-2">
        <table class="mb-2 w-full border-collapse border border-black text-sm">
            <tbody>
                <tr>
                    <td class="w-32 border border-black p-1 text-center">
                        <img src="{{ $logoDataUri }}" alt="Logo PPA" class="mx-auto h-12 w-12 object-contain">
                        <div class="mt-1 text-[11px] font-extrabold text-black">PPA</div>
                    </td>
                    <td class="border border-black p-2 text-center align-middle">
                        <div class="text-sm uppercase tracking-wide text-black">
                            <span class="font-extrabold">FORMULIR</span> <span class="italic font-semibold">DRIVING MONITORING SYSTEM</span>
                        </div>
                    </td>
                    <td class="w-64 border border-black p-0 align-top text-xs text-black">
                        <table class="w-full border-collapse">
                            <tbody>
                                <tr class="border-b border-black">
                                    <td class="p-1 pl-2">No Dokumen</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">{{ $docNo }}</td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-1 pl-2">Revisi</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">{{ $docRevisi }}</td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-1 pl-2">Tgl Efektif</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">{{ $docTglEfektif }}</td>
                                </tr>
                                <tr>
                                    <td class="p-1 pl-2">Halaman</td>
                                    <td class="w-2 text-center">:</td>
                                    <td class="p-1 pr-2">{{ $docHalaman }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="flex flex-col gap-1 text-xs font-semibold text-slate-800">
            <div class="grid grid-cols-[180px_auto] items-center gap-2">
                <div>Hari, Tanggal</div>
                <div>: {{ $hariIndo }}, {{ $tanggal }}</div>
            </div>
            <div class="grid grid-cols-[180px_auto] items-center gap-2">
                <div>Shift Kerja</div>
                <div>: {{ $shiftLabel }}</div>
            </div>
            <div class="grid grid-cols-[180px_auto] items-center gap-2 mt-1">
                <div>Petugas 1 (07:00 - 12:00)</div>
                <div>: {{ $petugas1 }}</div>
            </div>
            <div class="grid grid-cols-[180px_auto] items-center gap-2">
                <div>Petugas 2 (13:00 - 18:30)</div>
                <div>: {{ $petugas2 }}</div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white shadow-sm">
        <table class="w-full border-collapse border border-slate-300 text-center text-[10px]">
            <thead>
                <tr class="bg-[#0F5132] text-white">
                    <th class="border border-slate-300 p-2 font-semibold uppercase">No</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">NAMA OPERATOR</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">NRP</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">No. Unit</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Area</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Waktu</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Jenis Deviasi</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Deviasi Ke</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Temuan</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Evidence ICC</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Follow UP Hasil Intervensi</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Follow UP Hasil Intervensi 2</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Note/Tindak Lanjut (Intervensi)</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Sumber</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Group Leader</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Status</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    <tr>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ $index + 1 }}</td>
                        <td class="border border-slate-300 p-2 align-middle font-semibold text-slate-800 text-left">{{ $row['namaOperator'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ $row['nrp'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ $row['noUnit'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ $row['area'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ $row['waktu'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-800">{{ $row['jenisDeviasi'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-center text-slate-700">{{ $row['deviasiKe'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle">{{ $row['temuan'] }}</td>
                        @foreach (['evidenceIcc', 'followUp1', 'followUp2'] as $field)
                            <td class="border border-slate-300 p-2 align-middle">
                                @if (!empty($row[$field]) && !str_starts_with($row[$field], 'blob:'))
                                    <img src="{{ $row[$field] }}" class="mx-auto h-10 w-14 overflow-hidden rounded border border-slate-200 object-cover">
                                @else
                                    <span class="mx-auto flex h-10 w-14 items-center justify-center rounded border border-dashed border-slate-200 text-slate-300">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="border border-slate-300 p-2 align-middle text-slate-700 text-left">{{ $row['note'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ $row['sumber'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700 text-left">{{ $row['groupLeader'] }}</td>
                        <td class="border border-slate-300 p-2 align-middle">
                            <span @class([
                                'rounded-full px-2 py-0.5 font-semibold text-[10px]',
                                'bg-red-100 text-red-700' => $row['status'] === 'Open',
                                'bg-orange-100 text-orange-700' => $row['status'] === 'In Progress',
                                'bg-emerald-100 text-emerald-700' => $row['status'] === 'Closed',
                            ])>{{ $row['status'] }}</span>
                        </td>
                        <td class="border border-slate-300 p-2 align-middle text-slate-700 text-left">{{ $row['remark'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="border border-slate-300 p-4 text-center text-slate-500">Tidak ada data untuk tanggal dan shift ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-2 rounded-xl border border-emerald-200 p-3" style="background:#c8e6c9; break-inside: avoid;">
        <p class="mb-1 text-sm font-bold text-emerald-900">
            {{ $tallyTitle }}
        </p>
        <p class="mb-2 text-xs text-emerald-800">
            Tanggal {{ $tanggal }} &middot; {{ $shiftLabel }}
        </p>

        <div class="mb-1 text-xs font-bold uppercase tracking-wide text-emerald-900">Jenis Deviasi:</div>
        <ul class="mb-2 grid grid-cols-1 gap-y-0.5 text-sm text-emerald-900">
            @foreach ($tally as $jenis => $count)
                <li class="flex justify-between border-b border-emerald-300/50 py-0.5">
                    <span>{{ $jenis }}</span>
                    <span class="font-bold">{{ $count }}</span>
                </li>
            @endforeach
        </ul>

        <p class="m-0 text-center text-sm font-bold italic text-emerald-900">Salam Zero Accident Mindset!!!</p>
    </div>
</body>
</html>
