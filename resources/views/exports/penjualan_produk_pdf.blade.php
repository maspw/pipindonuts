<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Produk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #0d6efd; }
        .header h1 { font-size: 16px; font-weight: bold; color: #0d6efd; letter-spacing: 0.5px; }
        .header p { font-size: 10px; color: #666; margin-top: 2px; }
        .company { font-size: 13px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead tr { background-color: #0d6efd; color: white; }
        thead th { padding: 7px 6px; text-align: left; font-size: 10px; font-weight: bold; }
        tbody tr:nth-child(even) { background-color: #f0f6ff; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #d0e0f8; font-size: 10px; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-lunas   { background: #d1fae5; color: #065f46; }
        .badge-hutang  { background: #fee2e2; color: #991b1b; }
        .summary { margin-top: 14px; padding: 8px 12px; background: #f0f6ff; border-left: 3px solid #0d6efd; font-size: 10px; }
        .summary strong { color: #0d6efd; }
        .col-note { font-size: 9px; color: #999; margin-bottom: 6px; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">Pipindonuts</div>
        <h1>LAPORAN PENJUALAN PRODUK</h1>
        <p>Dicetak pada: {{ $generated }}</p>
    </div>

    @if(isset($columnLabels))
    <div class="col-note">
        Kolom ditampilkan: {{ implode(', ', array_values($columnLabels)) }}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @if(isset($columnLabels))
                    @foreach($columnLabels as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                @else
                    <th>ID Penjualan</th><th>Karyawan</th><th>Tgl Jual</th>
                    <th>Total Jual</th><th>Metode Bayar</th><th>Total Bayar</th>
                    <th>Kembalian</th><th>Status Bayar</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if(isset($rows) && $rows->count())
                @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
                @endforeach
            @elseif(!isset($rows))
                @forelse($penjualans as $p)
                <tr>
                    <td>{{ $p->id_penjualan }}</td>
                    <td>{{ $p->karyawan?->nama ?? '-' }}</td>
                    <td>{{ $p->tgl_jual?->format('d/m/Y') }}</td>
                    <td>Rp {{ number_format($p->total_jual, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($p->pembayaran?->metode_bayar ?? '-') }}</td>
                    <td>Rp {{ number_format($p->pembayaran?->total_bayar ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($p->pembayaran?->kembalian ?? 0, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($p->pembayaran?->status_bayar ?? '-') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:#999;padding:20px">Tidak ada data.</td></tr>
                @endforelse
            @else
                <tr>
                    <td colspan="{{ count($columnLabels ?? []) ?: 8 }}" style="text-align:center;color:#999;padding:20px">
                        Tidak ada data.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="summary">
        <strong>Ringkasan:</strong>
        Total {{ $penjualans->count() }} transaksi &nbsp;|&nbsp;
        Lunas: {{ $penjualans->filter(fn ($p) => $p->pembayaran?->status_bayar === 'lunas')->count() }} &nbsp;|&nbsp;
        Hutang: {{ $penjualans->filter(fn ($p) => $p->pembayaran?->status_bayar === 'hutang')->count() }} &nbsp;|&nbsp;
        Total Omset: Rp {{ number_format($penjualans->sum('total_jual'), 0, ',', '.') }}
    </div>

    <div class="footer">
        Pipindonuts — Sistem Manajemen Penjualan &copy; {{ date('Y') }}
    </div>

</body>
</html>
