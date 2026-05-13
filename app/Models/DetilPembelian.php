<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetilPembelian extends Model
{
    protected $table = 'detil_pembelian';

    protected $fillable = [
        'pembelian_id',
        'bahan_id',
        'jumlah',
        'harga_satuan',
        'sub_total',
        'tgl_kadaluarsa',
    ];

    protected $casts = [
        'tgl_kadaluarsa' => 'date',
        'jumlah'         => 'integer',
        'harga_satuan'   => 'integer',
        'sub_total'      => 'integer',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianBahanbaku::class, 'pembelian_id');
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'bahan_id', 'id_bahanbaku');
    }
}
