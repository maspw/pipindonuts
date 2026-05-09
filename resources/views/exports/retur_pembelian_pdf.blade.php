<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Retur Pembelian Bahan Baku</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        /* Header Laporan */
        .header {
            text-align: center;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #c0392b;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #c0392b;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        /* Logo / Nama Perusahaan */
        .company {
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        thead tr {
            background-color: #c0392b;
            color: white;
        }
        thead th {
            padding: 7px 6px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        tbody tr:nth-child(even) {
            background-color: #fef9f9;
        }
        tbody tr:hover {
            background-color: #fdecea;
        }
        tbody td {
            padding: 6px 6px;
            border-bottom: 1px solid #f0d5d5;
            font-size: 10px;
            vertical-align: top;
        }

        /* Badge Status */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-pending    { background: #fef3c7; color: #b45309; }
        .badge-disetujui  { background: #d1fae5; color: #065f46; }
        .badge-ditolak    { background: #fee2e2; color: #991b1b; }

        /* Badge Tipe */
        .tipe-rusak        { background: #fee2e2; color: #991b1b; }
        .tipe-salah_kirim  { background: #fef3c7; color: #b45309; }
        .tipe-kelebihan    { background: #dbeafe; color: #1e40af; }
        .tipe-lainnya      { background: #f3f4f6; color: #374151; }

        /* Summary */
        .summary {
            margin-top: 14px;
            padding: 8px 12px;
            background: #fef9f9;
            border-left: 3px solid #c0392b;
            font-size: 10px;
        }
        .summary strong { color: #c0392b; }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">Pipindonuts</div>
        <h1>LAPORAN RETUR PEMBELIAN BAHAN BAKU</h1>
        <p>Dicetak pada: {{ $generated }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>Bahan Diretur</th>
                <th>Tipe Retur</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Alasan</th>
                <th>Tgl Retur</th>
                <th>Karyawan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($returs as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td><strong>{{ $r->pembelian?->supplier?->nama_supplier ?? '-' }}</strong></td>
                <td>{{ $r->bahan?->nama_bahan ?? '-' }}</td>
                <td>
                    <span class="badge tipe-{{ $r->tipe_retur }}">
                        {{ match($r->tipe_retur) {
                            'rusak'       => 'Rusak',
                            'salah_kirim' => 'Salah Kirim',
                            'kelebihan'   => 'Kelebihan',
                            default       => 'Lainnya'
                        } }}
                    </span>
                </td>
                <td>{{ $r->jumlah }} {{ $r->bahan?->satuan ?? '' }}</td>
                <td>
                    <span class="badge badge-{{ $r->status }}">
                        {{ ucfirst($r->status) }}
                    </span>
                </td>
                <td>{{ $r->alasan ?? '-' }}</td>
                <td>{{ $r->tgl_retur?->format('d/m/Y') }}</td>
                <td>{{ $r->karyawan?->nama ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; color:#999; padding:20px;">
                    Tidak ada data retur.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Ringkasan:</strong>
        Total {{ $returs->count() }} data retur &nbsp;|&nbsp;
        Disetujui: {{ $returs->where('status','disetujui')->count() }} &nbsp;|&nbsp;
        Pending: {{ $returs->where('status','pending')->count() }} &nbsp;|&nbsp;
        Ditolak: {{ $returs->where('status','ditolak')->count() }}
    </div>

    <div class="footer">
        Pipindonuts — Sistem Manajemen Inventori &copy; {{ date('Y') }}
    </div>

</body>
</html>
