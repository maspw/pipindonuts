<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian Bahan Baku Pipindonuts</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .container { padding: 30px; }
        
        /* Gaya Header ala Filament */
        .header { margin-bottom: 25px; border-bottom: 2px solid #10B981; padding-bottom: 15px; }
        .header h1 { margin: 0; color: #10B981; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 3px 0; color: #666; font-size: 10px; }
        
        /* Gaya Tabel List */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background-color: #fff; }
        
        th { 
            background-color: #f9fafb; 
            color: #6b7280; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 9px; 
            padding: 12px 10px; 
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }
        
        td { 
            padding: 12px 10px; 
            border-bottom: 1px solid #f3f4f6; 
            vertical-align: middle; 
        }

        .id-text { font-weight: 600; color: #111827; }
        .date-text { color: #6b7280; }
        .total-text { font-weight: 700; color: #10B981; }

        /* Badge LUNAS Hijau */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            background-color: #dcfce7;
            color: #166534;
            text-transform: uppercase;
        }

        .footer { margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; font-size: 9px; color: #999; text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pipindonuts</h1>
            <p>Laporan Data Pembelian Bahan Baku</p>
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID Pembelian</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td><span class="id-text">{{ $record->id_pembelian }}</span></td>
                    <td><span class="date-text">{{ \Carbon\Carbon::parse($record->tgl_beli)->format('d/m/Y H:i') }}</span></td>
                    <td>{{ $record->supplier->nama_supplier ?? '-' }}</td>
                    <td><span class="total-text">Rp {{ number_format($record->total_beli, 0, ',', '.') }}</span></td>
                    <td><span class="badge">Lunas</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Sistem Informasi Akuntansi Pipindonuts &copy; 2026
        </div>
    </div>
</body>
</html>