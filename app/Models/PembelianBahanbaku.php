<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianBahanbaku extends Model
{
    protected $table = 'pembelian_bahanbaku';

    protected $fillable = [
        'supplier_id',
        'karyawan_id',
        'tgl_beli',
        'total_beli',
        'dokumen',
    ];

    protected $casts = [
        'tgl_beli'   => 'date',
        'total_beli' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id_karyawan');
    }

    public function detilPembelian(): HasMany
    {
        return $this->hasMany(DetilPembelian::class, 'pembelian_id');
    }
}
