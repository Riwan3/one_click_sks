@php
    $statusBg = ['PASS' => '#dcfce7', 'FAILED' => '#fee2e2', 'REMEDIAL' => '#fef9c3', 'COACHING' => '#ede9fe'];
@endphp
<table border="1" style="border-collapse:collapse;">
    <tr>
        <th colspan="9" style="background:#b91c1c;color:#fff;font-size:14px;padding:8px;">MONITORING EVALUASI PROSEDUR — {{ $assessmentTitle }}</th>
    </tr>
    <tr>
        @foreach (['No', 'NRP', 'Nama', 'Jabatan', 'Assessment', 'Score', 'Status', 'Attempt', 'Tanggal Selesai'] as $header)
            <th style="background:#f1f5f9;font-weight:bold;padding:6px;">{{ $header }}</th>
        @endforeach
    </tr>
    @foreach ($rows as $row)
        <tr>
            <td style="padding:5px;text-align:center;">{{ $loop->iteration }}</td>
            <td style="padding:5px;">{{ $row['nrp'] }}</td>
            <td style="padding:5px;">{{ $row['name'] }}</td>
            <td style="padding:5px;">{{ $row['jabatan'] ?? '—' }}</td>
            <td style="padding:5px;">{{ $row['assessment_title'] }}</td>
            <td style="padding:5px;text-align:center;">{{ $row['score'] !== null ? number_format($row['score'], 1) : '—' }}</td>
            <td style="padding:5px;text-align:center;font-weight:bold;background:{{ $statusBg[$row['status']] ?? '#f3f4f6' }};">{{ $row['status_label'] }}</td>
            <td style="padding:5px;text-align:center;">{{ $row['attempt'] ?? '—' }}</td>
            <td style="padding:5px;">{{ $row['completed_at'] ?? '—' }}</td>
        </tr>
    @endforeach
</table>
