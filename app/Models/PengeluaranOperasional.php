<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranOperasional extends Model
{
    protected $table = 'pengeluaran_operasionals';

    protected $guarded = [];//semua field boleh diisi
    protected $fillable = [//field yang disimpan apa aja
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
        return $this->belongsTo(//Relasi pengeluaran → karyawan, 1 pengeluaran dimiliki 1 karyawan.
            Karyawan::class,
            'id_karyawan',
            'id_karyawan'
        );
    }
}