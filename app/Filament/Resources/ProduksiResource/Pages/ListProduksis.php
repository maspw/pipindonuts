<?php

namespace App\Filament\Resources\ProduksiResource\Pages;

use App\Filament\Resources\ProduksiResource;
use App\Models\Produksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProduksis extends ListRecords
{
    protected static string $resource = ProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
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

                // ── EXPORT CSV/EXCEL (Instan Tanpa Library Tambahan) ───────────────────
                Actions\Action::make('export_csv')
                    ->label('Export Excel / CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->action(function () {
                        $produksis = Produksi::with(['karyawan'])->orderBy('tgl_produksi', 'desc')->get();

                        return response()->streamDownload(function () use ($produksis) {
                            $handle = fopen('php://output', 'w');
                            
                            // Tambahkan BOM agar Excel bisa baca karakter spesial/UTF-8 dengan benar
                            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

                            // Judul Kolom (Header)
                            fputcsv($handle, [
                                'ID Produksi', 
                                'Nama Karyawan', 
                                'Tanggal Produksi', 
                                'Status'
                            ]);

                            // Isi Data
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
}