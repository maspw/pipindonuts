<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; //testing/dummy
use Illuminate\Database\Eloquent\Model;

class Coa extends Model //menghubungkan laravel dengan tabel database coa
{
    // use HasFactory;
    // karena kita merubah tabelnya dari coas menjadi coa
    protected $table = 'coa'; //memberitahu laravel model coa memakai tabel

    // seluruh kolom dapat dimodifikasi
    protected $guarded = []; //semua tabel boleh diisi atau diubah
}