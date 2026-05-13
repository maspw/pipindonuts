<?php

namespace App\Filament\Resources\ReturPembelianResource\Pages;

use App\Filament\Resources\ReturPembelianResource;
use App\Models\ReturPembelian;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturPembelians extends ListRecords
{
    protected static string $resource = ReturPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
}
