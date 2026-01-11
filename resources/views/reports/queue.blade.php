<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Queue Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; }
        .info { margin-bottom: 15px; }
        .stats { margin: 15px 0; }
        .stats table { width: 100%; border-collapse: collapse; }
        .stats td { padding: 5px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f4f4f4; }
        .footer { margin-top: 20px; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ANTRIAN KLINIK</h2>
        <p>Periode: {{ $date_from }} s/d {{ $date_to }}</p>
    </div>

    <div class="stats">
        <h3>Ringkasan</h3>
        <table>
            <tr>
                <td><strong>Total Antrian:</strong></td>
                <td>{{ $stats['total'] }}</td>
                <td><strong>Menunggu:</strong></td>
                <td>{{ $stats['waiting'] }}</td>
            </tr>
            <tr>
                <td><strong>Dipanggil:</strong></td>
                <td>{{ $stats['called'] }}</td>
                <td><strong>Selesai:</strong></td>
                <td>{{ $stats['done'] }}</td>
            </tr>
            <tr>
                <td><strong>Dibatalkan:</strong></td>
                <td>{{ $stats['cancelled'] }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <h3>Detail Antrian</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No. Antrian</th>
                <th>Pasien</th>
                <th>Poli</th>
                <th>Dokter</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($queues as $index => $queue)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $queue->queue_date }}</td>
                <td>{{ $queue->queue_number }}</td>
                <td>{{ $queue->patient->user->name ?? '-' }}</td>
                <td>{{ $queue->department->name ?? '-' }}</td>
                <td>{{ $queue->doctor->name ?? '-' }}</td>
                <td>{{ ucfirst($queue->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>
</body>
</html>
