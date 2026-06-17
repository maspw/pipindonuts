<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBahanProduksi extends Model
{
    protected $table = 'detail_bahan_produksi';

    protected $fillable = ['id_produksi', 'id_bahanbaku', 'jumlah_dipakai'];

    // Relasi ke tabel Bahans
    public function bahanBaku()
    {
        return $this->belongsTo(Bahan::class, 'id_bahanbaku', 'id_bahanbaku');
    }

    // LOGIC OTOMATIS POTONG STOK
    protected static function booted()
    {
        // Ketika data detail bahan dibuat (Save di Wizard)
        static::created(function ($detail) {
            $bahan = $detail->bahanBaku;
            if ($bahan) {
                // Kurangi kolom jml_stok di tabel bahans
                $bahan->decrement('jml_stok', $detail->jumlah_dipakai);
            }
        });

        // Ketika data detail dihapus, stok dikembalikan (opsional)
        static::deleted(function ($detail) {
            $bahan = $detail->bahanBaku;
            if ($bahan) {
                $bahan->increment('jml_stok', $detail->jumlah_dipakai);
            }
        });
    }
}