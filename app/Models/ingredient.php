<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{

Protected $table = 'ingredients';
protected $fillable = [
    'nama_bahan',
    'satuan',
    'stok_qty',
];
}
