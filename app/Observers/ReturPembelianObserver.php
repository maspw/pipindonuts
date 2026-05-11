<?php

namespace App\Observers;

use App\Mail\ReturPembelianMail;
use App\Models\ReturPembelian;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ReturPembelianObserver
{
    /**
     * Saat retur dibuat:
     * - Jika langsung 'disetujui' → kurangi stok
     * - Kirim email notifikasi ke Admin
     */
    public function created(ReturPembelian $retur): void
    {
        if ($retur->status === 'disetujui') {
            $retur->bahan()->decrement('jml_stok', $retur->jumlah);
        }

        $this->kirimEmail($retur, 'baru');
    }

    /**
     * Saat status diupdate:
     * - pending/ditolak → disetujui : kurangi stok + email disetujui
     * - disetujui → pending/ditolak : kembalikan stok + email ditolak
     */
    public function updated(ReturPembelian $retur): void
    {
        $statusLama = $retur->getOriginal('status');
        $statusBaru = $retur->status;

        $wasApproved = $statusLama === 'disetujui';
        $isApproved  = $statusBaru === 'disetujui';

        if (!$wasApproved && $isApproved) {
            $retur->bahan()->decrement('jml_stok', $retur->jumlah);
            $this->kirimEmail($retur, 'disetujui');
        } elseif ($wasApproved && !$isApproved) {
            $retur->bahan()->increment('jml_stok', $retur->jumlah);
            if ($statusBaru === 'ditolak') {
                $this->kirimEmail($retur, 'ditolak');
            }
        }
    }

    /**
     * Saat retur dihapus dan statusnya disetujui → kembalikan stok.
     */
    public function deleted(ReturPembelian $retur): void
    {
        if ($retur->status === 'disetujui') {
            $retur->bahan()->increment('jml_stok', $retur->jumlah);
        }
    }

    /**
     * Kirim email notifikasi ke semua Admin.
     */
    private function kirimEmail(ReturPembelian $retur, string $tipe): void
    {
        try {
            $adminEmails = User::where('user_group', 'Admin')->pluck('email');

            if ($adminEmails->isEmpty()) return;

            $returLoaded = $retur->load(['bahan', 'pembelian.supplier', 'karyawan']);

            foreach ($adminEmails as $email) {
                Mail::to($email)->send(new ReturPembelianMail($returLoaded, $tipe));
            }
        } catch (\Exception $e) {
            // Log error tanpa mengganggu proses utama
            \Log::error('Gagal kirim email retur: ' . $e->getMessage());
        }
    }
}
