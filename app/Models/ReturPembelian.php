<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPembelian extends Model
{
    protected $table = 'retur_pembelians';

    protected $fillable = [
        'pembelian_id',
        'bahan_id',
        'karyawan_id',
        'tipe_retur',
        'jumlah',
        'status',
        'alasan',
        'tgl_retur',
    ];

    protected $casts = [
        'tgl_retur' => 'date',
        'jumlah'    => 'integer',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianBahanbaku::class, 'pembelian_id');
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id_karyawan');
    }

    public function isDiSetujui(): bool
    {
        return $this->status === 'disetujui';
    }
}
