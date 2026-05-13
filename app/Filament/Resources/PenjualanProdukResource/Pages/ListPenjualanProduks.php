<?php

namespace App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Filament\Resources\PenjualanProdukResource;
use App\Models\PenjualanProduk;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenjualanProduks extends ListRecords
{
    protected static string $resource = PenjualanProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Transaksi Baru'),

            \Filament\Actions\ActionGroup::make([

                // ── Export PDF (semua data) ───────────────────
                Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->form([PenjualanProdukResource::columnForm()])
                    ->action(function (array $data) {
                        $cols      = $data['columns'];
                        $penjualans = PenjualanProduk::with(['karyawan', 'detil.produk', 'pembayaran'])
                            ->orderBy('tgl_jual', 'desc')
                            ->get();

                        $pdf = Pdf::loadView('exports.penjualan_produk_pdf', [
                            'penjualans'   => $penjualans,
                            'selectedCols' => $cols,
                            'columnLabels' => array_intersect_key(
                                PenjualanProdukResource::columnOptions(),
                                array_flip($cols)
                            ),
                            'rows'      => $penjualans->map(fn ($p) => PenjualanProdukResource::buildRow($p, $cols)),
                            'generated' => now()->format('d M Y H:i'),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'penjualan-produk-semua-' . now()->format('Y-m-d') . '.pdf',
                            ['Content-Type' => 'application/pdf'],
                        );
                    }),

                // ── Export CSV (semua data) ───────────────────
                Actions\Action::make('export_csv')
                    ->label('Export Excel / CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->form([PenjualanProdukResource::columnForm()])
                    ->action(function (array $data) {
                        $cols      = $data['columns'];
                        $labels    = array_intersect_key(
                            PenjualanProdukResource::columnOptions(),
                            array_flip($cols)
                        );
                        $penjualans = PenjualanProduk::with(['karyawan', 'detil.produk', 'pembayaran'])
                            ->orderBy('tgl_jual', 'desc')
                            ->get();

                        return response()->streamDownload(function () use ($penjualans, $cols, $labels) {
                            $handle = fopen('php://output', 'w');
                            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
                            fputcsv($handle, array_values($labels));
                            foreach ($penjualans as $p) {
                                fputcsv($handle, array_values(PenjualanProdukResource::buildRow($p, $cols)));
                            }
                            fclose($handle);
                        }, 'penjualan-produk-semua-' . now()->format('Y-m-d') . '.csv',
                            ['Content-Type' => 'text/csv; charset=UTF-8']);
                    }),

            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
        ];
    }
}
