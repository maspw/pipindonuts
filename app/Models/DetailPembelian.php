<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelian extends Model
{
    protected $table = 'detail_pembelian';
    protected $guarded = [];

    public function bahanbaku(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'id_bahanbaku', 'id');
    }
}