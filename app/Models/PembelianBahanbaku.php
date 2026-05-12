<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PembelianBahanbaku extends Model
{
    protected $table = 'pembelian_bahanbaku';
    protected $guarded = [];
    
    // Primary key menggunakan no_faktur (String)
    protected $primaryKey = 'id_pembelian'; 
    protected $keyType = 'string';
    public $incrementing = false;

    public static function generateNoFaktur()
    {
        $sql = "SELECT IFNULL(MAX(id_pembelian), 'PB-0000000') as no_faktur FROM pembelian_bahanbaku";
        $kodefaktur = DB::select($sql);
        $kd = $kodefaktur[0]->no_faktur ?? 'PB-0000000';
        $noawal = substr($kd, -7);
        $noakhir = (int)$noawal + 1;
        return 'PB-' . str_pad($noakhir, 7, "0", STR_PAD_LEFT);
    }

    public function detail_pembelian(): HasMany 
    { 
        return $this->hasMany(DetailPembelian::class, 'id_pembelian', 'id_pembelian'); 
    }

    public function supplier(): BelongsTo 
    { 
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier'); 
    }

    public function karyawan(): BelongsTo 
    { 
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan'); 
    }
}