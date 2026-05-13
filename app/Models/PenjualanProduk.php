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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PenjualanProduk extends Model
{
    protected $table      = 'penjualan_produks';
    protected $primaryKey = 'id_penjualan';

    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = [
        'id_penjualan',
        'karyawan_id',
        'tgl_jual',
        'total_jual',
    ];

    protected $casts = [
        'tgl_jual' => 'date',
    ];

    /**
     * Auto-generate id_penjualan → PJL001, PJL002, dst.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id_penjualan)) {
                $model->id_penjualan = static::generateId();
            }
        });
    }

    public static function generateId(): string
    {
        $last = static::orderBy('id_penjualan', 'desc')->first();

        if ($last) {
            $num = (int) ltrim(substr($last->id_penjualan, 3), '0'); // "PJL007" → "7"
        } else {
            $num = 0;
        }

        return 'PJL' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    /** Alias public untuk dipanggil dari Filament Resource (form default). */
    public static function generateIdPublic(): string
    {
        return static::generateId();
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id_karyawan');
    }

    public function detil(): HasMany
    {
        return $this->hasMany(DetilPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_penjualan', 'id_penjualan');
    }
}
