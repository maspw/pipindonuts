<?php

namespace App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Filament\Resources\PenjualanProdukResource;
use App\Models\Produk;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class ListPenjualanProduks extends ListRecords
{
    protected static string $resource = PenjualanProdukResource::class;

    public function mount(): void
    {
        parent::mount();

        if (!session()->has('ai_top_10_rasa')) {
            $this->jalankanRisetPasarAI(true);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh_ai_sales')
                ->label('Refresh AI Marketing Insights')
                ->icon('heroicon-m-sparkles')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Riset Pasar AI & Injeksi Grafik')
                ->modalDescription('Gemini AI akan meramal Top 10 rasa terpopuler 2026, meramaikan grafik horizontal, dan memperbarui alasan tren.')
                ->action(function () {
                    $this->jalankanRisetPasarAI(false);
                }),

            Actions\CreateAction::make()->label('New Penjualan'),
        ];
    }

    protected function jalankanRisetPasarAI(bool $isSilent = false): void
    {
        $apiKey = env('GEMINI_API_KEY');

        $donatCadangan = [
            "Pistachio Dubai Kunafa", "Salted Caramel Biscoff", "Matcha Strawberry Crunch",
            "Smoked Beef Cheese Injection", "Peach Earl Grey Glaze", "Lotus Biscoff Supreme",
            "Taro Velvet Crumble", "Dark Choco Hazelnut Shell", "Mango Coconut Panna Cotta",
            "Tiramisu Almond Toast"
        ];
        
        $alasanCadangan = "• ⚡ **Premiumization Trend**: Konsumen urban harian berani membayar harga tinggi (*premium pricing*) demi sensasi tekstur renyah kunafa dan paduan rasa gurih-manis yang otentik.\n" .
            "• 📦 **Visual Marketing Wave**: Dominasi warna kontras tinggi seperti matcha-strawberry dan taro velvet memicu interaksi organik tinggi (*user-generated content*) di platform media sosial.\n" .
            "• 📊 **Menu Engineering Taktis**: Segera alokasikan kapasitas bahan baku premium ke dalam lini produksi harian guna memaksimalkan *contribution margin* per unit donat Pipindonuts.";

        if (!$apiKey) {
            session(['ai_top_10_rasa' => $donatCadangan, 'ai_trend_alasan' => $alasanCadangan]);
            $this->dispatch('refreshWidget');
            return;
        }

        $prompt = "Anda adalah Chief Marketing Officer (CMO) dan analis tren bisnis kuliner senior Pipindonuts tahun 2026.\n";
        $prompt .= "Tolong ramal 10 nama varian rasa donat premium yang paling viral dan paling banyak diminati konsumen secara global saat ini untuk dimasukkan ke dalam grafik.\n";
        $prompt .= "Selain itu, susun analisis alasan tren strategis yang diisikan ke properti 'alasan_tren'.\n\n";
        $prompt .= "ATURAN KETAT UNTUK PROPERTI 'alasan_tren':\n";
        $prompt .= "1. Tuliskan TEPAT 3 baris poin analisis tindakan (gunakan awalan tanda bullet •).\n";
        $prompt .= "2. Setiap poin HARUS diawali emoji (• ⚡, • 📦, • 📊), diikuti judul tebal markdown, lalu penjelasan solusi pemasaran taktis sepanjang 25-35 kata per poin agar isinya komprehensif dan tidak terlalu singkat.\n";
        $prompt .= "3. Setiap selesai menulis 1 poin, wajib pindah baris baru tunggal saja (\\n, jangan dobel \\n\\n) agar jaraknya pas rapat ideal.\n";
        $prompt .= "4. JANGAN gunakan kalimat pembuka atau penutup di dalam 'alasan_tren'. Langsung ke poin-poin tersebut.\n\n";
        $prompt .= "Anda HARUS menjawab hanya dalam format JSON mentah berstruktur seperti ini tanpa teks markdown tambahan di luar JSON: {\"tren_rasa\":[\"Rasa 1\",\"Rasa 2\"],\"alasan_tren\":\"• ⚡ **Judul**: Penjelasan panjang 25-35 kata...\\n• 📦 **Judul**: Penjelasan panjang...\\n• 📊 **Judul**: Penjelasan panjang...\"}";

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey, [
                    "contents" => [["parts" => [["text" => $prompt]]]]
                ]);

            if ($response->successful()) {
                $rawText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $cleanJson = trim(str_replace(['```json', '```'], '', $rawText));
                $dataAi = json_decode($cleanJson, true);

                if (isset($dataAi['tren_rasa']) && is_array($dataAi['tren_rasa'])) {
                    session([
                        'ai_top_10_rasa' => $dataAi['tren_rasa'],
                        'ai_trend_alasan' => $dataAi['alasan_tren'] ?? $alasanCadangan
                    ]);
                } else {
                    session(['ai_top_10_rasa' => $donatCadangan, 'ai_trend_alasan' => $alasanCadangan]);
                }
            } else {
                session(['ai_top_10_rasa' => $donatCadangan, 'ai_trend_alasan' => $alasanCadangan]);
            }
        } catch (\Exception $e) {
            session(['ai_top_10_rasa' => $donatCadangan, 'ai_trend_alasan' => $alasanCadangan]);
        }

        $this->dispatch('refreshWidget');
        if (!$isSilent) {
            Notification::make()->title('Top 10 Tren Rasa AI 2026 Berhasil Diperbarui!')->success()->send();
        }
    }

    /**
     * RENDER HOOK SAKTI: Memaksa Box Teks AI melar panjang 100% horizontal tepat di bawah grafik!
     */
    protected function getRenderHookContent(string $hook): string|\Illuminate\Contracts\Support\Htmlable
    {
        if ($hook === 'panels::resource.pages.list-records.table.before') {
            $trendAlasan = session('ai_trend_alasan') ?? "• ⚡ **Menunggu Analisis**: Silakan klik tombol \"Refresh AI Marketing Insights\" di atas untuk meramal perilaku pasar Pipindonuts 2026.";
            
            // Otomatis mengubah format markdown **Text** menjadi HTML bold warna emas
            $formattedText = preg_replace('/\\*\\*(.*?)\\*\\*/', '<strong class="text-amber-500 font-semibold">$1</strong>', $trendAlasan);

            return new HtmlString("
                <div class='mb-4 p-5 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 w-full text-left shadow-sm block' style='clear: both; width: 100%; min-width: 100%;'>
                    <div class='flex items-center gap-2 mb-3 border-b border-gray-200 dark:border-gray-800 pb-2'>
                        <span class='text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2'>
                            📊 AI Marketing & Trend Analysis — Data Tren Aktif 2026
                        </span>
                    </div>
                    <div class='text-sm leading-relaxed text-gray-500 dark:text-gray-400 space-y-2' style='white-space: pre-line;'>
                        " . nl2br($formattedText) . "
                    </div>
                </div>
            ");
        }

        return '';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // SISA 1 WIDGET GRAFIK SAJA: Widget teks yang bikin ciut tadi udah kita tendang!
            \App\Filament\Resources\PenjualanProdukResource\Widgets\PenjualanChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1; // Grafik nangkring full lebar di atas
    }
}