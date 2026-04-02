<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $fillable = ['nama_bahan', 'satuan', 'stok_qty', 'dokumen'];
}
