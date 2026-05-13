<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bahan extends Model
{
    protected $table = 'bahans';

    protected $fillable = [
        'nama_bahan',
        'satuan',
        'stok_qty',
        'stok_minimum',
        'tgl_exp',
    ];

    protected $casts = [
        'tgl_exp'       => 'date',
        'stok_qty'      => 'integer',
        'stok_minimum'  => 'integer',
    ];

    /**
     * Cek apakah stok di bawah minimum.
     */
    public function getIsBelowMinimumAttribute(): bool
    {
        return $this->stok_qty <= $this->stok_minimum;
    }

    /**
     * Cek apakah bahan akan kadaluarsa dalam N hari ke depan.
     */
    public function willExpireIn(int $days = 7): bool
    {
        if (!$this->tgl_exp) return false;
        return $this->tgl_exp->isPast() || $this->tgl_exp->diffInDays(now(), true) <= $days;
    }

    public function detilPembelian(): HasMany
    {
        return $this->hasMany(DetilPembelian::class, 'bahan_id');
    }
}
