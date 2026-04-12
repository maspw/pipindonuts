<?php

use App\Models\PembelianBahanbaku;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pembelians = PembelianBahanbaku::with('detilPembelian.bahan')->get();

foreach ($pembelians as $p) {
    echo "=== Pembelian #{$p->id} | Supplier: {$p->supplier_id} | Total: {$p->total_beli} ===" . PHP_EOL;
    if ($p->detilPembelian->isEmpty()) {
        echo "  (tidak ada detail item)" . PHP_EOL;
    }
    foreach ($p->detilPembelian as $d) {
        $nama = $d->bahan?->nama_bahan ?? '?';
        echo "  -> {$nama} | Jumlah: {$d->jumlah} | Harga: {$d->harga_satuan} | Sub: {$d->sub_total}" . PHP_EOL;
    }
}
