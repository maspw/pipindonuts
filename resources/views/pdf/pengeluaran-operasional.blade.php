<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengeluaran Operasional</title>

    <style>

        body{
            font-family: Arial, sans-serif;
        }

        h2{
            text-align: center;
            margin-bottom: 20px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td{
            border: 1px solid black;
        }

        th{
            background: #f2f2f2;
        }

        th, td{
            padding: 10px;
            font-size: 12px;
            text-align: left;
        }

    </style>

</head>

<body>

    <h2>
        Laporan Pengeluaran Operasional
    </h2>

    <table>

        <thead>

            <tr>
                <th>ID Pengeluaran</th>
                <th>ID Karyawan</th>
                <th>Nama Karyawan</th>
                <th>Tanggal</th>
                <th>Nama Pengeluaran</th>
                <th>Nominal</th>
                <th>Keterangan</th>
            </tr>

        </thead>

        <tbody>

            @foreach($data as $item)

                <tr>

                    <td>
                        {{ $item->id_pengeluaran }}
                    </td>

                    <td>
                        {{ $item->karyawan->id_karyawan }}
                    </td>

                    <td>
                        {{ $item->karyawan->nama }}
                    </td>

                    <td>
                        {{ $item->tanggal }}
                    </td>

                    <td>
                        {{ $item->nama_pengeluaran }}
                    </td>

                    <td>
                        Rp {{ number_format($item->nominal,0,',','.') }}
                    </td>

                    <td>
                        {{ $item->keterangan }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>
</html>