<?php

namespace App\Filament\Resources\ReturPembelianResource\Pages;

use App\Filament\Resources\ReturPembelianResource;
use App\Models\ReturPembelian;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ListReturPembelians extends ListRecords
{
    protected static string $resource = ReturPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL AI INSIGHT RETUR PEMBELIAN (WARNA MERAH QC)
            Actions\Action::make('generate_retur_ai_insight')
                ->label('Generate Retur AI Insight')
                ->icon('heroicon-m-archive-box-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Analisis Mutu Bahan Baku & Vendor')
                ->modalDescription('Gemini AI akan memetakan kerugian barang reject dan menyusun strategi pengetatan standar kualitas masuk gudang Pipindonuts.')
                ->action(function () {
                    $this->hitungInsightReturBahanBakuAI();
                }),

            Actions\CreateAction::make()->label('Catat Retur Baru'),

            \Filament\Actions\ActionGroup::make([

                // ── Export PDF (semua data) ───────────────────
                Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->form([ReturPembelianResource::columnForm()])
                    ->action(function (array $data) {
                        $cols   = $data['columns'];
                        $returs = ReturPembelian::with(['pembelian.supplier', 'bahan', 'karyawan'])
                            ->orderBy('tgl_retur', 'desc')->get();

                        $pdf = Pdf::loadView('exports.retur_pembelian_pdf', [
                            'returs'       => $returs,
                            'selectedCols' => $cols,
                            'columnLabels' => array_intersect_key(
                                ReturPembelianResource::columnOptions(),
                                array_flip($cols)
                            ),
                            'rows'      => $returs->map(fn ($r) => ReturPembelianResource::buildRow($r, $cols)),
                            'generated' => now()->format('d M Y H:i'),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'retur-pembelian-semua-' . now()->format('Y-m-d') . '.pdf',
                            ['Content-Type' => 'application/pdf'],
                        );
                    }),

                // ── Export CSV (semua data) ───────────────────
                Actions\Action::make('export_csv')
                    ->label('Export Excel / CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->form([ReturPembelianResource::columnForm()])
                    ->action(function (array $data) {
                        $cols   = $data['columns'];
                        $labels = array_intersect_key(
                            ReturPembelianResource::columnOptions(),
                            array_flip($cols)
                        );
                        $returs = ReturPembelian::with(['pembelian.supplier', 'bahan', 'karyawan'])
                            ->orderBy('tgl_retur', 'desc')->get();

                        return response()->streamDownload(function () use ($returs, $cols, $labels) {
                            $handle = fopen('php://output', 'w');
                            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
                            fputcsv($handle, array_values($labels));
                            foreach ($returs as $r) {
                                fputcsv($handle, array_values(ReturPembelianResource::buildRow($r, $cols)));
                            }
                            fclose($handle);
                        }, 'retur-pembelian-semua-' . now()->format('Y-m-d') . '.csv',
                            ['Content-Type' => 'text/csv; charset=UTF-8']);
                    }),

            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
        ];
    }

    
    protected function hitungInsightReturBahanBakuAI(): void
    {
        $apiKey = env('GEMINI_API_KEY');
        $totalKasusRetur = ReturPembelian::count();

        $dataLogRetur = ReturPembelian::with(['pembelian.supplier', 'bahan'])->latest()->take(3)->get();
        $rincianTeksDatabase = "";
        foreach ($dataLogRetur as $item) {
            $namaBahan = $item->bahan->nama_bahan ?? 'Bahan';
            $supplier = $item->pembelian->supplier->nama_supplier ?? 'Supplier';
            $alasan = $item->keterangan ?? 'Defect Mutu';
            $jumlah = $item->jumlah ?? '0';
            $rincianTeksDatabase .= "- Barang: {$namaBahan}, Jumlah: {$jumlah} unit, Vendor: {$supplier} (Alasan Cacat: {$alasan})\n";
        }

        $saranCadangan = "• 📦 **Evaluasi Sistem Penyimpanan**: Lakukan audit suhu berkala pada area *cold storage* guna menjaga stabilitas komoditas sensitif (seperti mentega) agar tidak mengalami kerusakan struktural sebelum masuk lini produksi Pipindonuts.\n" .
            "• 🔍 **Pengetatan Inbound QC**: Terapkan prosedur *Quality Inspection* ketat (termasuk uji sampling tepung dan ragi) langsung di area bongkar muat guna mendeteksi serta menolak barang cacat sebelum diterimanya surat jalan.\n" .
            "• 🤝 **Sanksi SLA Vendor**: Segera lakukan evaluasi *Service Level Agreement* (SLA) dan layangkan teguran tertulis atau pemotongan nota pembayaran kepada pihak supplier yang menyuplai bahan baku di bawah standar kualitas.";

        if (!$apiKey) {
            session(['ai_retur_bahan_baku_insight' => $saranCadangan]);
            $this->dispatch('refreshWidget');
            Notification::make()->title('AI Retur Insight Berhasil Diperbarui!')->success()->send();
            return;
        }

        $prompt = "Anda adalah seorang Supply Chain Quality Auditor senior dalam industri manufaktur kuliner Pipindonuts tahun 2026. Berdasarkan total kasus sebanyak {$totalKasusRetur} dan rincian log retur komoditas berikut:\n{$rincianTeksDatabase}\n";
        $prompt .= "Rumuskan 3 poin rekomendasi taktis mitigasi risiko bahan baku dengan mematuhi aturan format ini secara mutlak:\n";
        $prompt .= "1. Tuliskan TEPAT 3 baris poin rekomendasi tindakan nyata (gunakan awalan tanda bullet •).\n";
        $prompt .= "2. Setiap poin HARUS diawali emoji yang relevan (• 📦, • 🔍, • 🤝) diikuti judul tebal markdown, lalu penjelasan solusi manajemen mutu yang berbobot operasional.\n";
        $prompt .= "3. Setiap selesai menulis 1 poin, Anda WAJIB memberikan pindah baris baru tunggal saja (\\n, jangan dobel \\n\\n) agar tampilan box di Filament tetap padat dan jaraknya pas tidak kerenggangan.\n";
        $prompt .= "4. Berikan penjelasan sepanjang 25-35 kata per poin agar isinya komprehensif, mendalam, tidak terlalu singkat, dan sebutkan nama komoditas atau vendor yang bermasalah di atas dalam penjelasan Anda. JANGAN sertakan kalimat pembuka atau kesimpulan apapun.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey, [
                "contents" => [["parts" => [["text" => $prompt]]]]
            ]);

            if ($response->successful()) {
                $hasilAi = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? $saranCadangan;
                session(['ai_retur_bahan_baku_insight' => $hasilAi]);
            } else {
                session(['ai_retur_bahan_baku_insight' => $saranCadangan]);
            }
        } catch (\Exception $e) {
            session(['ai_retur_bahan_baku_insight' => $saranCadangan]);
        }

        $this->dispatch('refreshWidget');
        Notification::make()->title('AI Retur Insight Berhasil Diperbarui!')->success()->send();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ReturBahanBakuInsightWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}