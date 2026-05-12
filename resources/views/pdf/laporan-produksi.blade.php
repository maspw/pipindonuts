<!DOCTYPE html>
<html>
<head>
    <title>Laporan Semua Produksi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>LAPORAN KESELURUHAN PRODUKSI PIPIN DONUTS</h2>
    
    @foreach($semua_produksi as $produksi)
        <div style="margin-bottom: 30px;">
            <strong>ID Produksi:</strong> {{ $produksi->id_produksi }} <br>
            <strong>Karyawan:</strong> {{ $produksi->karyawan->nama }} | 
            <strong>Tanggal:</strong> {{ $produksi->tgl_produksi }}

            <table>
                <thead>
                    <tr style="background-color: #eee;">
                        <th>Bahan Baku</th>
                        <th>Jumlah Pakai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produksi->detailBahanProduksi as $detail)
                    <tr>
                        <td>{{ $detail->bahanBaku->nama_bahan }}</td>
                        <td>{{ $detail->jumlah_dipakai }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
    @endforeach
</body>
</html>