<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;//class email laravel

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
            ->subject('Pengeluaran Operasional')//mgnkn subect email
            ->view('emails.pengeluaran-operasional');//mgnkn blade email
    }
}