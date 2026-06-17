<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePembelianMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pembelian;

    public function __construct($pembelian)
    {
        $this->pembelian = $pembelian;
    }

    /**
     */
    public function build()
    {
        // Buat PDF-nya dulu
        $pdf = Pdf::loadView('pdf.pembelian', [
            'records' => [$this->pembelian]
        ]);

        // Kirim email dengan tampilan dan lampiran
        return $this->view('pdf.pembelian')
            ->with([
                'records' => [$this->pembelian]
            ])
            ->attachData($pdf->output(), "Invoice-{$this->pembelian->id_pembelian}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}