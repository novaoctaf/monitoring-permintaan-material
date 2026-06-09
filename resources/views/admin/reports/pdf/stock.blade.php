<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Material</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
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
        }
        table thead {
            background-color: #0054a6;
            color: white;
        }
        table th {
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        table td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
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
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #2fb344;
            color: white;
        }
        .badge-warning {
            background-color: #f59f00;
            color: white;
        }
        .badge-danger {
            background-color: #d63939;
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
        <h1>PT MARUWA - LAPORAN STOK MATERIAL</h1>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        @if($category)
            <p><strong>Kategori:</strong> {{ $category }}</p>
        @else
            <p><strong>Kategori:</strong> Semua Kategori</p>
        @endif
        
        @if($search)
            <p><strong>Pencarian:</strong> "{{ $search }}"</p>
        @endif
        
        <p><strong>Total Material:</strong> {{ number_format($stocks->count()) }} item</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Material</th>
                <th width="25%">Nama Material</th>
                <th width="15%">Kategori</th>
                <th width="10%" class="text-center">Satuan</th>
                <th width="10%" class="text-right">Stok Saat Ini</th>
                <th width="10%" class="text-right">Stok Minimum</th>
                <th width="10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $index => $stock)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $stock->material->code }}</td>
                    <td>{{ $stock->material->name }}</td>
                    <td>{{ $stock->material->category->name }}</td>
                    <td class="text-center">{{ $stock->material->unit }}</td>
                    <td class="text-right">{{ number_format($stock->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($stock->material->min_stock, 2) }}</td>
                    <td class="text-center">
                        @if($stock->quantity > $stock->material->min_stock)
                            <span class="badge badge-success">Aman</span>
                        @elseif($stock->quantity == $stock->material->min_stock)
                            <span class="badge badge-warning">Batas</span>
                        @else
                            <span class="badge badge-danger">Kurang</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data stok</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-section">
        <p><strong>Ringkasan:</strong></p>
        <p>• Total Material: {{ number_format($stocks->count()) }} item</p>
        <p>• Stok Aman: {{ number_format($stocks->filter(function($s) { return $s->quantity > $s->material->min_stock; })->count()) }} item</p>
        <p>• Stok Kurang: {{ number_format($stocks->filter(function($s) { return $s->quantity < $s->material->min_stock; })->count()) }} item</p>
    </div>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Manajemen Inventori PT Maruwa</p>
    </div>
</body>
</html>
