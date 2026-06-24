<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuBesar extends Model
{
    use HasFactory;

    
    protected $table = 'jurnal'; // Nama tabel eksplisit

    
    public function jurnaldetail()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id');
    }
}