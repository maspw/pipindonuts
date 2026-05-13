<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id_supplier';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_supplier',
        'nama_supplier',
        'no_telp',
        'alamat',
    ];
    protected static function booted()
    {
        static::deleting(function ($supplier) {
            if ($supplier->pembelian()->exists()) {
                throw new \Exception("Supplier '{$supplier->nama_supplier}' tidak bisa dihapus karena memiliki riwayat transaksi.");
            }
        });
    }
    public function pembelian(): HasMany
    {
        return $this->hasMany(PembelianBahanbaku::class, 'id_supplier', 'id_supplier');
    }
}