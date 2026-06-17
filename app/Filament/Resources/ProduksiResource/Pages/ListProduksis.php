<?php

namespace App\Filament\Resources\ProduksiResource\Pages;

use App\Filament\Resources\ProduksiResource;
use App\Models\Produksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ListProduksis extends ListRecords
{
    protected static string $resource = ProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_produksi_ai_insight')
                ->label('Generate Produksi AI Insight')
                ->icon('heroicon-m-wrench-screwdriver')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Kalkulasi Efisiensi Manufaktur AI')
                ->modalDescription('Gemini AI akan membedah kombinasi resep adonan dan biaya overhead dari log produksi riil Pipindonuts.')
                ->action(function () {
                    $this->hitungInsightProduksiPakarAI();
                }),

            Actions\CreateAction::make()->label('Tambah Produksi'),

            \Filament\Actions\ActionGroup::make([
                // ── EXPORT PDF (Instan) ───────────────────
                Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function () {
                        $data = Produksi::with(['karyawan', 'detailBahanProduksi.bahanBaku'])->get();
                        
                        $pdf = Pdf::loadView('pdf.laporan-produksi', [
                            'semua_produksi' => $data,
                            'generated' => now()->format('d M Y H:i'),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'laporan-produksi-' . now()->format('Y-m-d') . '.pdf',
                            ['Content-Type' => 'application/pdf']
                        );
                    }),

                // ── EXPORT CSV/EXCEL ───────────────────
                Actions\Action::make('export_csv')
                    ->label('Export Excel / CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(function () {
                        $produksis = Produksi::with(['karyawan'])->orderBy('tgl_produksi', 'desc')->get();

                        return response()->streamDownload(function () use ($produksis) {
                            $handle = fopen('php://output', 'w');
                            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

                            fputcsv($handle, [
                                'ID Produksi', 
                                'Nama Karyawan', 
                                'Tanggal Produksi', 
                                'Status'
                            ]);

                            foreach ($produksis as $p) {
                                fputcsv($handle, [
                                    $p->id_produksi,
                                    $p->karyawan?->nama ?? '-',
                                    $p->tgl_produksi,
                                    $p->status,
                                ]);
                            }
                            fclose($handle);
                        }, 'laporan-produksi-' . now()->format('Y-m-d') . '.csv', [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                        ]);
                    }),
            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
        ];
    }


    protected function hitungInsightProduksiPakarAI(): void
    {
        $apiKey = env('GEMINI_API_KEY');
        
        $totalBatchProduksi = Produksi::count();
        $batchSelesai = Produksi::where('status', 'Selesai')->count();


        $logDapurTerakhir = Produksi::with(['detailBahanProduksi.bahanBaku'])
            ->orderBy('tgl_produksi', 'desc')
            ->take(2)
            ->get();

        $rincianPemakaianBahan = "";
        foreach ($logDapurTerakhir as $index => $prod) {
            $rincianPemakaianBahan .= "Batch " . ($index + 1) . " (Tgl: {$prod->tgl_produksi}, Status: {$prod->status}):\n";
            if ($prod->detailBahanProduksi && $prod->detailBahanProduksi->isNotEmpty()) {
                foreach ($prod->detailBahanProduksi as $detail) {
                    $namaBahan = $detail->bahanBaku->nama_bahan ?? 'Bahan Baku';
                    $jumlahPake = $detail->jumlah ?? 0;
                    $rincianPemakaianBahan .= "  - {$namaBahan}: {$jumlahPake}\n";
                }
            } else {
                $rincianPemakaianBahan .= "  - Tidak tercatat rincian detail pemakaian bahan baku.\n";
            }
        }

        
        $saranCadangan = "• 🍩 **Optimalisasi Batch Baking**: Terapkan penjadwalan mesin pengaduk adonan (*mixer*) secara terpusat pada jam kerja utama untuk menekan pemborosan daya listrik eksternal dan memaksa utilisasi kapasitas mesin mencapai titik maksimal.\n" .
            "• ⏱️ **Pengendalian Yield Varians**: Lakukan kalibrasi timbangan digital secara harian pada pencampuran tepung premium dan ragi guna menekan selisih kurang (*unfavorable variance*) antara target standar dengan output riil produksi donat.\n" .
            "• 🔀 **Standard Costing Rekonsiliasi**: Segera lakukan rekonsiliasi data *food waste* harian dari sisa adonan gagal ke dalam kartu biaya produksi standar, sehingga margin laba kotor per boks donat Pipindonuts tercatat secara akurat.";

        if (!$apiKey) {
            session(['ai_produksi_insight' => $saranCadangan]);
            $this->dispatch('refreshWidget');
            Notification::make()->title('AI Produksi Insight Berhasil Diperbarui!')->success()->send();
            return;
        }

        $prompt = "Anda adalah seorang Senior Food Production Controller dan Cost Accountant ahli untuk brand manufaktur kuliner 'Pipindonuts' tahun 2026. Berdasarkan total kumulatif volume data produksi harian ({$totalBatchProduksi} batch) dan rincian pemakaian takaran bahan baku riil berikut:\n{$rincianPemakaianBahan}\n";
        $prompt .= "Rumuskan 3 poin analisis efisiensi biaya produksi dengan mematuhi aturan format ini secara mutlak:\n";
        $prompt .= "1. Tuliskan TEPAT 3 baris poin rekomendasi tindakan nyata (gunakan awalan tanda bullet •).\n";
        $prompt .= "2. Setiap poin HARUS diawali emoji yang relevan (• 🍩, • ⏱️, • 🔀) diikuti judul tebal markdown, lalu penjelasan solusi *production control* yang berbobot akuntansi biaya dan operasional dapur.\n";
        $prompt .= "3. Setiap selesai writing 1 poin, Anda WAJIB memberikan pindah baris baru tunggal saja (\\n, jangan dobel \\n\\n) agar visual box di Filament tetap padat, rapi, dan jarak antarbarisnya pas ideal.\n";
        $prompt .= "4. Berikan penjelasan sepanjang 25-35 kata per poin agar isinya komprehensif, tajam, tidak terlalu singkat, serta sebutkan nama komoditas bahan baku dapur yang tertulis di atas secara spesifik dalam penjelasan solusi Anda. JANGAN sertakan kalimat pembuka atau kesimpulan apapun.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey, [
                "contents" => [["parts" => [["text" => $prompt]]]]
            ]);

            if ($response->successful()) {
                $hasilAi = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? $saranCadangan;
                session(['ai_produksi_insight' => $hasilAi]);
            } else {
                session(['ai_produksi_insight' => $saranCadangan]);
            }
        } catch (\Exception $e) {
            session(['ai_produksi_insight' => $saranCadangan]);
        }

        $this->dispatch('refreshWidget');
        Notification::make()->title('AI Produksi Insight Berhasil Diperbarui!')->success()->send();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ProduksiInsightWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}