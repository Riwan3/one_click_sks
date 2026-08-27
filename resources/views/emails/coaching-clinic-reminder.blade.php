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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #e74c3c;
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
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .details p {
            margin: 8px 0;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Coaching Clinic Reminder</h1>
            <p>Diperlukan Tindakan Segera</p>
        </div>

        <div class="content">
            <div class="alert">
                <strong>Reminder:</strong> Coaching Clinic berikut masih dalam status draft dan menunggu finalisasi Anda.
            </div>

            <div class="details">
                <p><strong>Topik:</strong> {{ $clinic->topik ?? 'N/A' }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $clinic->created_at->format('d F Y H:i') }}</p>
                <p><strong>Status:</strong> <strong style="color: #e74c3c;">Draft</strong></p>
                <p><strong>Durasi Pending:</strong> {{ $clinic->created_at->diffInDays(now()) }} hari</p>
            </div>

            <p>Silakan segera selesaikan dan finalisasi Coaching Clinic ini agar dapat diproses lebih lanjut.</p>

            <p style="margin-top: 20px;">Jika sudah menyelesaikan, abaikan email ini.</p>

            <a href="#" class="button">Akses OneClick Dashboard</a>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem OneClick - Coaching & Mentoring Management</p>
            <p>Jangan balas email ini. Jika ada pertanyaan, hubungi administrator sistem.</p>
        </div>
    </div>
</body>
</html>
