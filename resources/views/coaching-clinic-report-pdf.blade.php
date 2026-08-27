<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Coaching Clinic Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #f0f0f0;
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Coaching Clinic Report</h1>
        <p>Period: {{ $dateFrom }} to {{ $dateTo }}</p>
        <p>Generated: {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>NRP</th>
                <th>Name</th>
                <th>Date</th>
                <th>Coach</th>
                <th>Materials</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clinics as $clinic)
                <tr>
                    <td>{{ $clinic->employee?->nrp ?? '-' }}</td>
                    <td>{{ $clinic->employee?->nama ?? '-' }}</td>
                    <td>{{ $clinic->tanggal?->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $clinic->coach?->name ?? '-' }}</td>
                    <td>
                        @php
                            $materials = array_filter([
                                $clinic->materi_pribadi ? 'Pribadi' : '',
                                $clinic->materi_keluarga ? 'Keluarga' : '',
                                $clinic->materi_lingkungan ? 'Lingkungan' : '',
                                $clinic->materi_pekerjaan ? 'Pekerjaan' : '',
                            ]);
                        @endphp
                        {{ implode(', ', $materials) ?: '-' }}
                    </td>
                    <td>{{ ucfirst($clinic->status ?? 'Published') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">No data found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Report generated on {{ date('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>
