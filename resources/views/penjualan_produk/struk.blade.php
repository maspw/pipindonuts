<div style="width: 80mm; font-family: monospace;">
    <center>
        <strong>PIPIN DONUTS</strong><br>
        Bandung, Jawa Barat<br>
        --------------------------
    </center>
    <br>
    Kasir: {{ $record->karyawan->nama }}<br>
    Tgl: {{ $record->tgl_jual }}<br>
    --------------------------<br>
    @foreach($record->harga_jual as $item)
        @php $produk = \App\Models\Produk::find($item['id_produk']); @endphp
        {{ $produk->nama_produk }} x{{ $item['qty'] }} <br>
        @Rp {{ number_format($item['harga_satuan']) }} = Rp {{ number_format($item['subtotal']) }}<br>
    @endforeach
    --------------------------<br>
    <strong>Total: Rp {{ number_format($record->total_jual) }}</strong><br>
    Bayar: Rp {{ number_format($record->uang_diterima) }}<br>
    Kembali: Rp {{ number_format($record->uang_kembalian) }}<br>
    <br>
    <center>Terima Kasih!</center>
</div>
<script>window.print();</script>