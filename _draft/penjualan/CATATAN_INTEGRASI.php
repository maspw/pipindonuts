<?php

/**
 * ============================================================
 * CATATAN INTEGRASI — Modul Penjualan Produk
 * ============================================================
 *
 * Langkah untuk menggabungkan draft ini ke project utama:
 *
 * 1. SALIN FILES
 *    Salin file-file berikut ke lokasi yang sesuai:
 *
 *    _draft/penjualan/Resources/PenjualanProdukResource.php
 *      → app/Filament/Resources/PenjualanProdukResource.php
 *
 *    _draft/penjualan/Resources/Pages/CreatePenjualanProduk.php
 *      → app/Filament/Resources/PenjualanProdukResource/Pages/CreatePenjualanProduk.php
 *
 *    _draft/penjualan/Resources/Pages/EditPenjualanProduk.php
 *      → app/Filament/Resources/PenjualanProdukResource/Pages/EditPenjualanProduk.php
 *
 *    _draft/penjualan/Resources/Pages/ListPenjualanProduks.php
 *      → app/Filament/Resources/PenjualanProdukResource/Pages/ListPenjualanProduks.php
 *
 * 2. PASTIKAN MODEL SUDAH ADA
 *    Model berikut sudah tersedia di project (tidak perlu dibuat ulang):
 *    - app/Models/PenjualanProduk.php    ✅
 *    - app/Models/DetilPenjualan.php     ✅
 *    - app/Models/Pembayaran.php         ✅
 *    - app/Models/Produk.php             ✅
 *    - app/Models/Karyawan.php           ✅
 *
 * 3. TAMBAHKAN METHOD generateIdPublic() di Model PenjualanProduk
 *    Di app/Models/PenjualanProduk.php, ubah private → public static:
 *
 *    // Sebelum:
 *    private static function generateId(): string { ... }
 *
 *    // Sesudah: (tambahkan alias public)
 *    public static function generateIdPublic(): string
 *    {
 *        return static::generateId();
 *    }
 *
 *    ATAU langsung ubah generateId() menjadi public static.
 *
 * 4. PASTIKAN RELASI PEMBAYARAN DI FORM BERFUNGSI
 *    Form menggunakan ->relationship('pembayaran') untuk HasOne.
 *    Pastikan di PenjualanProduk model ada:
 *    public function pembayaran(): HasOne { ... }  ✅ (sudah ada)
 *
 * 5. JALANKAN composer dump-autoload SETELAH MENYALIN:
 *    php artisan optimize:clear
 *
 * ============================================================
 * STRUKTUR ERD YANG DIIMPLEMENTASI
 * ============================================================
 *
 * penjualan_produks
 *   id_penjualan (PK, string) → auto: PJL001, PJL002, ...
 *   karyawan_id  (FK → karyawans.id_karyawan, nullable)
 *   tgl_jual     (date)
 *   total_jual   (bigInteger)
 *
 * detil_penjualans
 *   id           (PK, auto increment)
 *   id_penjualan (FK → penjualan_produks)
 *   produk_id    (FK → produks.id_produk)
 *   jumlah       (integer)
 *   harga_satuan (bigInteger)
 *   sub_total    (bigInteger)
 *
 * pembayarans
 *   id_pembayaran (PK, string) → auto: BYR-001, BYR-002, ...
 *   id_penjualan  (FK → penjualan_produks)
 *   metode_bayar  (tunai | transfer | qris)
 *   total_bayar   (bigInteger)
 *   kembalian     (bigInteger)
 *   status_bayar  (lunas | hutang)
 *
 * ============================================================
 */
