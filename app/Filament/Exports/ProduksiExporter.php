<?php

namespace App\Exports;

use App\Models\Produksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProduksiExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * Ambil data dari database
    */
    public function collection()
    {
        // Ambil data produksi beserta relasi karyawannya
        return Produksi::with('karyawan')->get();
    }

    /**
    * Atur Judul Kolom (Baris Pertama)
    */
    public function headings(): array
    {
        return [
            'ID Produksi',
            'Nama Karyawan',
            'Tanggal Produksi',
            'Status',
        ];
    }

    /**
    * Atur data mana saja yang masuk ke kolom
    */
    public function map($produksi): array
    {
        return [
            $produksi->id_produksi,
            $produksi->karyawan?->nama, // Ambil nama karyawan dari relasi
            $produksi->tgl_produksi,
            $produksi->status,
        ];
    }
}