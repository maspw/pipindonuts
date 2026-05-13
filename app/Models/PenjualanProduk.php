<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanProduk extends Model
{
    protected $fillable = [
        'id_karyawan', 
        'tgl_jual', 
        'harga_jual', 
        'total_jual', 
        'uang_diterima', 
        'uang_kembalian'
    ];

    protected $casts = [
        'harga_jual' => 'array',
    ];

    protected static function booted()
    {
        static::created(function ($penjualan) {
            $items = $penjualan->harga_jual;
            if (is_array($items)) {
                foreach ($items as $item) {
                    $produk = \App\Models\Produk::find($item['id_produk']);
                    if ($produk) {
                        $produk->decrement('stok', (int)$item['qty']);
                    }
                }
            }
        });
    }

    // Relasi ke Karyawan (Sudah benar)
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    // --- TAMBAHKAN INI BIAR GAK PUTUS ---
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}