<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table      = 'pembayarans';
    protected $primaryKey = 'id_pembayaran';

    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = [
        'id_pembayaran',
        'id_penjualan',
        'metode_bayar',
        'total_bayar',
        'kembalian',
        'status_bayar',
    ];

    /**
     * Auto-generate id_pembayaran → BYR-001, BYR-002, dst.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->id_pembayaran)) {
                $model->id_pembayaran = static::generateId();
            }
        });
    }

    private static function generateId(): string
    {
        $last = static::orderBy('id_pembayaran', 'desc')->first();

        if ($last) {
            $num = (int) ltrim(substr($last->id_pembayaran, 4), '0'); // "BYR-007" → "7"
        } else {
            $num = 0;
        }

        return 'BYR-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(PenjualanProduk::class, 'id_penjualan', 'id_penjualan');
    }
}
