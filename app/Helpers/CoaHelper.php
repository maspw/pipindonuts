<?php

namespace App\Helpers;

use App\Models\Coa;

class CoaHelper
{
    public static function get($kode)
    {
        return Coa::where('kode_akun', $kode)->first();
    }
}