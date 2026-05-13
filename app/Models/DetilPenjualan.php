<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetilPenjualan extends Model
{
    protected $table = 'detil_penjualans';

    protected $fillable = [
        'id_penjualan',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'sub_total',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(PenjualanProduk::class, 'id_penjualan', 'id_penjualan');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id_produk');
    }
}
