<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model DetilPembelian — alias untuk tabel detail_pembelian.
 * Digunakan oleh ReturPembelianResource untuk melihat bahan
 * yang ada dalam suatu transaksi pembelian.
 */
class DetilPembelian extends Model
{
    protected $table = 'detail_pembelian';

    protected $fillable = [
        'pembelian_id',
        'id_bahanbaku',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'id_bahanbaku', 'id_bahanbaku');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianBahanbaku::class, 'pembelian_id', 'id_pembelian');
    }
}
