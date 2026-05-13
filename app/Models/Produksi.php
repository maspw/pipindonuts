<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    // Nama tabel di database
    protected $table = 'produksi';
    
    // Primary Key kustom
    protected $primaryKey = 'id_produksi';

    // Karena id_produksi adalah string (PRD000...), nonaktifkan auto increment
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang boleh diisi
    protected $fillable = [
        'id_produksi',
        'id_karyawan',
        'tgl_produksi',
        'status',
        'catatan'
    ];

    /**
     * Logika untuk membuat ID otomatis PRD0000001
     */
    public static function generateId()
    {
        $lastId = self::select('id_produksi')
            ->orderBy('id_produksi', 'desc')
            ->first();

        if (!$lastId) {
            return 'PRD0000001';
        }

        // Ambil angkanya saja (mulai dari karakter ke-4), tambah 1
        $number = intval(substr($lastId->id_produksi, 3)) + 1;
        
        // Gabungkan kembali dengan prefix PRD dan pad nol sebanyak 7 digit
        return 'PRD' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke model Karyawan
     */
    public function karyawan()
    {
        return $this->belongsTo(
            Karyawan::class, 
            'id_karyawan', // Foreign Key di tabel produksi
            'id_karyawan'  // Primary Key di tabel karyawan
        );
    }
    // Tambahkan ini di dalam class Produksi
public function detailBahanProduksi()
{
    // Sesuaikan nama class Model detailmu
    return $this->hasMany(DetailBahanProduksi::class, 'id_produksi', 'id_produksi');
}
}