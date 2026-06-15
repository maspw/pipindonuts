<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table      = 'suppliers';
    protected $primaryKey = 'id_supplier';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_supplier',
        'nama_supplier',
        'no_telp',
        'alamat',
    ];

    // ── Auto-generate id_supplier: SPL-001, SPL-002, dst. ─────────────
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id_supplier)) {
                $model->id_supplier = static::generateId();
            }
        });

        static::deleting(function ($supplier) {
            if ($supplier->pembelian()->exists()) {
                throw new \Exception(
                    "Supplier '{$supplier->nama_supplier}' tidak bisa dihapus karena memiliki riwayat transaksi."
                );
            }
        });
    }

    private static function generateId(): string
    {
        $last = static::orderByRaw("CAST(SUBSTRING(id_supplier, 5) AS UNSIGNED) DESC")->first();

        if ($last) {
            $num = (int) substr($last->id_supplier, 4); // "SPL-001" → "001" → 1
        } else {
            $num = 0;
        }

        return 'SPL-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(PembelianBahanbaku::class, 'id_supplier', 'id_supplier');
    }
}