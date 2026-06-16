<h2>Data Pengeluaran Operasional</h2>

<p>
ID Pengeluaran:
{{ $record->id_pengeluaran }}
</p>

<p>
Nama Karyawan:
{{ $record->karyawan->nama }}
</p>

<p>
Nama Pengeluaran:
{{ $record->nama_pengeluaran }}
</p>

<p>
Nominal:
Rp {{ number_format($record->nominal) }}
</p>

<p>
Keterangan:
{{ $record->keterangan }}
</p>