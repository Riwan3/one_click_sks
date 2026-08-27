<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .summary-card {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daily MPP Summary Report</h1>
            <p>{{ $date->format('l, d F Y') }}</p>
        </div>

        <div class="content">
            <p>Halo {{ $user->name }},</p>

            <p>Berikut adalah ringkasan Manpower Performance (MPP) harian untuk tanggal {{ $date->format('d F Y') }}:</p>

            @if($summary->isEmpty())
                <div class="summary-card">
                    <p>Tidak ada data MPP untuk periode ini.</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Departemen</th>
                            <th>Jumlah Karyawan</th>
                            <th>Total Plan MP</th>
                            <th>Total Actual MP</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary as $item)
                            @php
                                $percentage = $item->total_plan > 0 ? round(($item->total_actual / $item->total_plan) * 100, 2) : 0;
                            @endphp
                            <tr>
                                <td>{{ $item->departemen ?? 'Unknown' }}</td>
                                <td>{{ $item->count }}</td>
                                <td>{{ $item->total_plan }}</td>
                                <td>{{ $item->total_actual }}</td>
                                <td>{{ $percentage }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p>Untuk informasi lebih detail, silakan kunjungi dashboard OneClick.</p>
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem OneClick - Manpower Performance Management</p>
            <p>Jangan balas email ini. Jika ada pertanyaan, hubungi administrator sistem.</p>
        </div>
    </div>
</body>
</html>
