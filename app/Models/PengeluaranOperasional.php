<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranOperasional extends Model
{
    protected $table = 'pengeluaran_operasionals';

    protected $guarded = [];
    protected $fillable = [
    'id_pengeluaran',
    'tanggal',
    'id_karyawan',
    'nama_pengeluaran',
    'nominal',
    'keterangan',
    'status',
];

    // Relasi ke Karyawan
    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class,
            'id_karyawan',
            'id_karyawan'
        );
    }
}