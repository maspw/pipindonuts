<?php

namespace App\Mail;

use App\Models\ReturPembelian;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturPembelianMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param ReturPembelian $retur
     * @param string $tipe  'baru' | 'disetujui' | 'ditolak'
     */
    public function __construct(
        public ReturPembelian $retur,
        public string $tipe = 'baru',
    ) {}

    public function envelope(): Envelope
    {
        $nama  = $this->retur->bahan?->nama_bahan ?? 'Bahan';
        $id    = $this->retur->id;

        $subject = match ($this->tipe) {
            'baru'      => "[Pipindonuts] 🔔 Retur Baru #{$id} — {$nama} Menunggu Persetujuan",
            'disetujui' => "[Pipindonuts] ✅ Retur #{$id} — {$nama} Telah Disetujui",
            'ditolak'   => "[Pipindonuts] ❌ Retur #{$id} — {$nama} Ditolak",
            default     => "[Pipindonuts] Update Retur #{$id}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.retur_pembelian');
    }
}
