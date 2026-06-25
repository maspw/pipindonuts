<?php

namespace App\Filament\Resources\PengeluaranOperasionalResource\Pages;

use App\Filament\Resources\PengeluaranOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\PengeluaranOperasional;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ListPengeluaranOperasionals extends ListRecords
{
    protected static string $resource = PengeluaranOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_operasional_ai_insight')
                ->label('Generate Operasional AI Insight')
                ->icon('heroicon-m-presentation-chart-line')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Kalkulasi Efisiensi Pengeluaran AI')
                ->modalDescription('Gemini AI akan membedah beban anggaran Pipindonuts dan menyusun rekomendasi akuntansi biaya secara otomatis.')
                ->action(function () {
                    $this->hitungInsightOperasionalAI();
                }),

            Actions\CreateAction::make()->label('New pengeluaran operasional'),
        ];
    }

    protected function hitungInsightOperasionalAI(): void
    {
        $apiKey = env('GEMINI_API_KEY');

        $totalBiaya     = PengeluaranOperasional::exists() ? PengeluaranOperasional::sum('nominal') : 0;
        $totalTransaksi = PengeluaranOperasional::count();

        $saranCadangan = "• ⚡ **Overhead Listrik**: Lakukan penjadwalan terpusat (*batch baking*) pada mesin oven dan *proofer* besar Pipindonuts guna memotong lonjakan beban biaya utilitas listrik bulanan secara signifikan.\n" .
            "• 📦 **Wastage Control**: Terapkan strategi *Demand Forecasting* berbasis data historis transaksi agar volume adonan tepung premium harian sinkron dengan tingkat kelarisan produk harian.\n" .
            "• 📊 **Standard Costing**: Segera lakukan pencatatan taktis mengenai biaya penyusutan aset mesin mixer/oven serta alokasi pemakaian gas untuk menghindari risiko *under-reporting* pada laporan laba rugi.";

        if (!$apiKey) {
            session(['ai_operational_insight' => $saranCadangan]);
            $this->dispatch('refreshWidget');
            Notification::make()->title('AI Operational Insight Berhasil Diperbarui!')->success()->send();
            return;
        }

        $prompt  = "Anda adalah Chief Financial Officer (CFO) senior dan pakar bisnis kuliner untuk brand donat premium 'Pipindonuts' tahun 2026. Berdasarkan total pengeluaran operasional saat ini sebesar Rp " . number_format($totalBiaya, 0, ',', '.') . " dari {$totalTransaksi} transaksi, berikan analisis efisiensi yang sangat tajam.\n";
        $prompt .= "WAJIB patuhi aturan format dan gaya bahasa ini secara mutlak:\n";
        $prompt .= "1. Tuliskan TEPAT 3 baris poin rekomendasi tindakan (gunakan awalan tanda bullet •).\n";
        $prompt .= "2. Setiap poin WAJIB diawali emoji yang pas (• ⚡, • 📦, • 📊) diikuti judul tebal markdown yang menjual, lalu penjelasan solusi operasional yang kritis dan solutif.\n";
        $prompt .= "3. Setiap selesai menulis 1 poin, Anda WAJIB memberikan pindah baris baru tunggal saja (\\n, jangan dobel \\n\\n) agar jarak visualnya rapat dan estetik.\n";
        $prompt .= "4. Gunakan gaya bahasa profesional, tegas, berbobot finansial, namun tetap mengalir (tidak kaku seperti textbook). Batasi sekitar 25-35 kata per poin agar penjelasannya padat-berisi. LANGSUNG ke poin solusi tanpa kalimat basa-basi di awal/akhir!";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey, [
                "contents" => [["parts" => [["text" => $prompt]]]]
            ]);

            if ($response->successful()) {
                $hasilAi = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? $saranCadangan;
                session(['ai_operational_insight' => $hasilAi]);
            } else {
                session(['ai_operational_insight' => $saranCadangan]);
            }
        } catch (\Exception $e) {
            session(['ai_operational_insight' => $saranCadangan]);
        }

        $this->dispatch('refreshWidget');
        Notification::make()->title('AI Operational Insight Berhasil Diperbarui!')->success()->send();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\OperasionalInsightWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
