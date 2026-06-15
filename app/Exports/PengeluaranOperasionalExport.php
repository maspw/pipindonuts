<?php

namespace App\Exports;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;

class PengeluaranOperasionalExport implements FromCollection
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return collect($this->records)->map(function ($item) {

            return [

                'ID Pengeluaran' => $item->id_pengeluaran,

                'Tanggal' => $item->tanggal,

                'ID Karyawan' => $item->karyawan->id_karyawan ?? '-',

                'Nama Karyawan' => $item->karyawan->nama ?? '-',

                'Nama Pengeluaran' => $item->nama_pengeluaran,

                'Nominal' => $item->nominal,

                'Status' => $item->status,

                'Keterangan' => $item->keterangan,

            ];

        });
    }
}