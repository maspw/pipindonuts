<?php

namespace App\Http\Controllers;

use App\Models\ReturPembelian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReturPembelianController extends Controller
{
    /**
     * Export semua data retur ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $returs = ReturPembelian::with(['pembelian.supplier', 'bahan', 'karyawan'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('tgl_retur', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.retur_pembelian_pdf', [
            'returs'    => $returs,
            'generated' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('retur-pembelian-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export semua data retur ke CSV (Excel-compatible).
     */
    public function exportCsv(Request $request)
    {
        $returs = ReturPembelian::with(['pembelian.supplier', 'bahan', 'karyawan'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('tgl_retur', 'desc')
            ->get();

        $filename = 'retur-pembelian-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($returs) {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel bisa baca UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header kolom
            fputcsv($handle, [
                'ID', 'Supplier', 'Bahan', 'Tipe Retur',
                'Jumlah', 'Satuan', 'Status', 'Alasan',
                'Tgl Retur', 'Karyawan',
            ]);

            foreach ($returs as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->pembelian?->supplier?->nama_supplier ?? '-',
                    $r->bahan?->nama_bahan ?? '-',
                    match ($r->tipe_retur) {
                        'rusak'       => 'Rusak',
                        'salah_kirim' => 'Salah Kirim',
                        'kelebihan'   => 'Kelebihan',
                        default       => 'Lainnya',
                    },
                    $r->jumlah,
                    $r->bahan?->satuan ?? '-',
                    ucfirst($r->status),
                    $r->alasan ?? '-',
                    $r->tgl_retur?->format('d/m/Y') ?? '-',
                    $r->karyawan?->nama ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
