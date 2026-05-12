<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PengeluaranOperasionalMail extends Mailable
{
    public $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function build()
    {
        return $this
            ->subject('Pengeluaran Operasional')
            ->view('emails.pengeluaran-operasional');
    }
}