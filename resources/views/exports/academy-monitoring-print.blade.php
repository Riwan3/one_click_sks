<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Evaluasi Kompetensi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; padding: 16px; }
        h2 { font-size: 14px; text-align: center; margin-bottom: 4px; }
        .sub { text-align: center; font-size: 10px; color: #555; margin-bottom: 12px; }
        .stats { display: flex; gap: 10px; margin-bottom: 12px; }
        .stat { flex: 1; border: 1px solid #ddd; border-radius: 6px; padding: 8px; text-align: center; }
        .stat .n { font-size: 18px; font-weight: bold; color: #b91c1c; }
        .stat .l { font-size: 9px; color: #555; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #b91c1c; color: #fff; padding: 5px 4px; font-size: 10px; }
        td { padding: 4px; border: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .PASS { background: #dcfce7 !important; color: #15803d; font-weight: bold; }
        .FAILED { background: #fee2e2 !important; color: #b91c1c; font-weight: bold; }
        .PENDING { background: #f3f4f6 !important; color: #6b7280; font-weight: bold; }
        .IN_PROGRESS { background: #fef9c3 !important; color: #854d0e; font-weight: bold; }
        .toolbar { padding: 12px; background: #f1f5f9; margin-bottom: 16px; }
        .toolbar button { border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()" style="background:#b91c1c;color:#fff;">Cetak / Save PDF</button>
        <button onclick="window.close()" style="margin-left:8px;background:#e2e8f0;">Tutup</button>
    </div>

    <h2>MONITORING EVALUASI KOMPETENSI</h2>
    <p class="sub">Tanggal cetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>

    <div class="stats">
        @foreach (['total_assigned' => 'Assigned', 'completed' => 'Selesai', 'passed' => 'Lulus', 'failed' => 'Tidak Lulus', 'pending' => 'Pending'] as $key => $label)
            <div class="stat"><div class="n">{{ $summary[$key] ?? 0 }}</div><div class="l">{{ $label }}</div></div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th><th>NRP</th><th>Nama</th><th>Jabatan</th>
                <th>Assessment</th><th>Score</th><th>Status</th><th>Attempt</th><th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td>{{ $row['nrp'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['jabatan'] ?? '—' }}</td>
                    <td>{{ $row['assessment_title'] }}</td>
                    <td style="text-align:center;">{{ $row['score'] !== null ? number_format($row['score'], 1) : '—' }}</td>
                    <td class="{{ $row['status'] }}" style="text-align:center;">{{ $row['status_label'] }}</td>
                    <td style="text-align:center;">{{ $row['attempt'] ?? '—' }}</td>
                    <td>{{ $row['completed_at'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
