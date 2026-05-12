<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawans';
    protected $primaryKey = 'id_karyawan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_karyawan',
        'nama',
        'no_telp',
        'posisi',
        'tanggal_masuk',
        'e_ktp',
    ];
}