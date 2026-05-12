<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pembelian</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Laporan Pembelian Bahan Baku</h2>
    <table>
        <thead>
            <tr>
                <th>No Faktur</th>
                <th>Tanggal</th>
                <th>Total Beli</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelian as $item)
            <tr>
                <td>{{ $item->no_faktur }}</td>
                <td>{{ $item->tgl_beli }}</td>
                <td>Rp {{ number_format($item->total_beli, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>