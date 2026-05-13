<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public function sendMessage($target, $message)
    {
        $token = env('FONNTE_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $target,
            'message' => $message,
            'countryCode' => '62',
        ]);

        return $response->json();
    }
}