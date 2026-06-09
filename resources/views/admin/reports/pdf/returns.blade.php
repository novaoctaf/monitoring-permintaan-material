<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengembalian Material</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0054a6;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #0054a6;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 10px;
            color: #666;
        }
        .info-section {
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        .info-section p {
            margin: 3px 0;
            font-size: 10px;
        }
        .info-section strong {
            color: #0054a6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        table thead {
            background-color: #0054a6;
            color: white;
        }
        table th {
            padding: 6px 3px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        table td {
            padding: 5px 3px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-info {
            background-color: #4299e1;
            color: white;
        }
        .badge-success {
            background-color: #2fb344;
            color: white;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .summary-section {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .summary-section p {
            margin: 3px 0;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT MARUWA - LAPORAN PENGEMBALIAN MATERIAL</h1>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        @if($date_from && $date_to)
            <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</p>
        @else
            <p><strong>Periode:</strong> Semua Data</p>
        @endif
        
        @if($status)
            <p><strong>Status:</strong> {{ ucfirst($status) }}</p>
        @else
            <p><strong>Status:</strong> Semua Status</p>
        @endif
        
        @if($returnor)
            <p><strong>Pengembalian Oleh:</strong> {{ $returnor }}</p>
        @endif
        
        <p><strong>Total Pengembalian:</strong> {{ number_format($returns->count()) }} transaksi</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">No. Retur</th>
                <th width="10%">Tgl Retur</th>
                <th width="12%">Kode Material</th>
                <th width="18%">Nama Material</th>
                <th width="8%">Qty</th>
                <th width="6%">Satuan</th>
                <th width="12%">Pengembalian Oleh</th>
                <th width="8%">Status</th>
                <th width="12%">Disetujui Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returns as $index => $return)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $return->return_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($return->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $return->request->material->code }}</td>
                    <td>{{ $return->request->material->name }}</td>
                    <td class="text-right">{{ number_format($return->quantity, 2) }}</td>
                    <td class="text-center">{{ $return->request->material->unit }}</td>
                    <td>{{ $return->returner->name }}</td>
                    <td class="text-center">
                        @if($return->status === 'pending')
                            <span class="badge badge-info">Pending</span>
                        @elseif($return->status === 'approved')
                            <span class="badge badge-success">Disetujui</span>
                        @else
                            <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </td>
                    <td>
                        @if($return->approved_by)
                            {{ $return->approver->name }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data pengembalian</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-section">
        <p><strong>Ringkasan:</strong></p>
        <p>• Total Pengembalian: {{ number_format($returns->count()) }} transaksi</p>
        <p>• Disetujui: {{ number_format($returns->where('status', 'approved')->count()) }} transaksi</p>
        <p>• Pending: {{ number_format($returns->where('status', 'pending')->count()) }} transaksi</p>
        <p>• Ditolak: {{ number_format($returns->where('status', 'rejected')->count()) }} transaksi</p>
        <p>• Total Quantity Disetujui: {{ number_format($returns->where('status', 'approved')->sum('quantity'), 2) }}</p>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Manajemen Inventori PT Maruwa</p>
    </div>
</body>
</html>
