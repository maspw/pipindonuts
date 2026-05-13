<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk #{{ $penjualan->id }} — Pipindonuts</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#0d0f14;color:#e8eaf0;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:30px 16px}

.receipt-wrapper{display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;justify-content:center}

/* ── STRUK CARD ── */
.receipt{background:#161b27;border:1px solid #2a3145;border-radius:16px;width:360px;overflow:hidden}
.receipt-header{background:linear-gradient(135deg,#f97316,#ea580c);padding:24px;text-align:center}
.receipt-header .logo{font-size:32px;margin-bottom:8px}
.receipt-header h2{font-size:18px;font-weight:800;color:#fff;letter-spacing:.5px}
.receipt-header p{font-size:12px;color:rgba(255,255,255,.8);margin-top:4px}

.receipt-body{padding:20px}
.receipt-meta{display:flex;justify-content:space-between;font-size:12px;color:#8892a4;margin-bottom:16px;padding-bottom:12px;border-bottom:1px dashed #2a3145}

.receipt-items{}
.receipt-item{display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid rgba(42,49,69,.5);font-size:13px}
.receipt-item:last-child{border-bottom:none}
.receipt-item .item-name{font-weight:600;color:#e8eaf0;flex:1}
.receipt-item .item-qty{color:#8892a4;font-size:12px;margin-top:2px}
.receipt-item .item-price{font-weight:700;color:#f97316;text-align:right}

.receipt-divider{border:none;border-top:2px dashed #2a3145;margin:14px 0}

.receipt-summary .row{display:flex;justify-content:space-between;font-size:13px;color:#8892a4;margin-bottom:8px}
.receipt-summary .row.total{font-size:17px;font-weight:800;color:#e8eaf0;margin-top:8px}
.receipt-summary .row.total span:last-child{color:#f97316}
.receipt-summary .row.kembalian span:last-child{color:#22c55e;font-weight:700}

.metode-badge{display:inline-flex;align-items:center;gap:6px;background:#1e2535;border:1px solid #2a3145;border-radius:20px;padding:6px 14px;font-size:12px;font-weight:600;margin-top:14px}

.receipt-footer{padding:16px 20px;background:#1e2535;text-align:center;border-top:1px solid #2a3145}
.receipt-footer p{font-size:11px;color:#8892a4;line-height:1.6}
.receipt-footer strong{color:#f97316}

/* ── ACTION PANEL ── */
.actions{display:flex;flex-direction:column;gap:12px;width:220px}
.actions h3{font-size:14px;font-weight:700;color:#e8eaf0;margin-bottom:4px}
.btn{display:flex;align-items:center;gap:10px;padding:13px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:.15s;font-family:inherit;width:100%;justify-content:center}
.btn-primary{background:#f97316;color:#fff}
.btn-primary:hover{background:#ea6c0a;transform:translateY(-1px)}
.btn-secondary{background:#1e2535;color:#e8eaf0;border:1px solid #2a3145}
.btn-secondary:hover{border-color:#f97316;color:#f97316}
.btn-ghost{background:transparent;color:#8892a4;border:1px solid #2a3145}
.btn-ghost:hover{border-color:#ef4444;color:#ef4444}

/* Success badge */
.success-badge{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#22c55e;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:12px}

@media print {
    body{background:#fff;color:#000;padding:0}
    .receipt{border:none;background:#fff;color:#000;width:100%;max-width:320px}
    .receipt-header{background:#f97316!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .actions{display:none}
    .receipt-body,.receipt-meta,.receipt-item .item-name,.receipt-summary .row{color:#000!important}
}
</style>
</head>
<body>

<div class="receipt-wrapper">
    {{-- ── STRUK ── --}}
    <div class="receipt">
        <div class="receipt-header">
            <div class="logo">🍩</div>
            <h2>PIPINDONUTS</h2>
            <p>Struk Pembelian</p>
        </div>

        <div class="receipt-body">
            <div class="receipt-meta">
                <div>
                    <div style="font-weight:600;color:#e8eaf0;margin-bottom:2px">{{ $penjualan->id_penjualan }}</div>
                    <div>{{ $penjualan->tgl_jual->format('d M Y') }}</div>
                </div>
                <div style="text-align:right">
                    <div>{{ now()->format('H:i') }} WIB</div>
                    <div>{{ $penjualan->karyawan?->nama ?? auth()->user()->name }}</div>
                </div>
            </div>

            <div class="receipt-items">
                @foreach ($penjualan->detil as $d)
                <div class="receipt-item">
                    <div>
                        <div class="item-name">{{ $d->produk?->nama_produk ?? '-' }}</div>
                        <div class="item-qty">{{ $d->jumlah }} × Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</div>
                    </div>
                    <div class="item-price">Rp {{ number_format($d->sub_total, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>

            <hr class="receipt-divider">

            @php $bayar = $penjualan->pembayaran; @endphp

            <div class="receipt-summary">
                <div class="row">
                    <span>Subtotal ({{ $penjualan->detil->sum('jumlah') }} item)</span>
                    <span>Rp {{ number_format($penjualan->total_jual, 0, ',', '.') }}</span>
                </div>
                <div class="row total">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($penjualan->total_jual, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span>Dibayar</span>
                    <span>Rp {{ number_format($bayar?->total_bayar ?? $penjualan->total_jual, 0, ',', '.') }}</span>
                </div>
                <div class="row kembalian">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($bayar?->kembalian ?? 0, 0, ',', '.') }}</span>
                </div>

                <div style="margin-top:10px">
                    <div class="metode-badge">
                        @php $metode = $bayar?->metode_bayar ?? 'tunai'; @endphp
                        @if($metode === 'tunai') 💵 Tunai
                        @elseif($metode === 'transfer') 🏦 Transfer
                        @else 📱 QRIS
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Terima kasih telah berbelanja di<br><strong>Pipindonuts</strong> 🍩<br>Selamat menikmati!</p>
        </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="actions">
        <div class="success-badge">
            ✅ Transaksi Berhasil!
        </div>

        <button class="btn btn-primary" onclick="window.print()">
            🖨️ Cetak Struk
        </button>
        <a href="{{ route('kasir.index') }}" class="btn btn-secondary">
            🍩 Transaksi Baru
        </a>
    </div>
</div>

</body>
</html>
