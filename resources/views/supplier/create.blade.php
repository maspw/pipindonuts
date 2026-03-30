<h2>Tambah Supplier</h2>

<form method="POST" action="/supplier">
    @csrf

    <input type="text" name="nama_supplier" placeholder="Nama">
    <input type="text" name="alamat" placeholder="Alamat">
    <input type="text" name="no_telp" placeholder="No Telp">

    <button type="submit">Simpan</button>
</form>