<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Retur Pembelian</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }

        /* Header */
        .header { padding: 28px 32px; text-align: center; }
        .header-baru      { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .header-disetujui { background: linear-gradient(135deg, #10b981, #059669); }
        .header-ditolak   { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .header .icon  { font-size: 36px; margin-bottom: 8px; }
        .header h1 { color: #fff; font-size: 20px; margin: 0; letter-spacing: 0.3px; }
        .header p  { color: rgba(255,255,255,0.85); font-size: 13px; margin: 6px 0 0; }

        /* Body */
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 16px; }

        /* Info Card */
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; }
        .card-title { font-size: 11px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.8px; margin-bottom: 12px; font-weight: bold; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6b7280; }
        .info-value { color: #111827; font-weight: 600; text-align: right; }

        /* Status Badge */
        .badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-disetujui { background: #d1fae5; color: #065f46; }
        .badge-ditolak   { background: #fee2e2; color: #991b1b; }

        /* Tipe Badge */
        .tipe-rusak       { background: #fee2e2; color: #991b1b; }
        .tipe-salah_kirim { background: #fef3c7; color: #92400e; }
        .tipe-kelebihan   { background: #dbeafe; color: #1e40af; }
        .tipe-lainnya     { background: #f3f4f6; color: #374151; }

        /* Alert Box */
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; }
        .alert-warning  { background: #fffbeb; border-left: 4px solid #f59e0b; color: #78350f; }
        .alert-success  { background: #ecfdf5; border-left: 4px solid #10b981; color: #064e3b; }
        .alert-danger   { background: #fef2f2; border-left: 4px solid #ef4444; color: #7f1d1d; }

        /* Footer */
        .footer { background: #f9fafb; padding: 18px 32px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { font-size: 11px; color: #9ca3af; margin: 0; }
        .footer strong { color: #c0392b; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ── HEADER ──────────────────────────────────── --}}
    <div class="header header-{{ $tipe }}">
        <div class="icon">
            @if($tipe === 'baru') 🔔
            @elseif($tipe === 'disetujui') ✅
            @else ❌
            @endif
        </div>
        <h1>
            @if($tipe === 'baru') Retur Baru Menunggu Persetujuan
            @elseif($tipe === 'disetujui') Retur Disetujui
            @else Retur Ditolak
            @endif
        </h1>
        <p>Retur Pembelian Bahan Baku #{{ $retur->id }}</p>
    </div>

    {{-- ── BODY ───────────────────────────────────── --}}
    <div class="body">

        <p class="greeting">Halo, Admin 👋</p>

        {{-- Alert sesuai tipe --}}
        @if($tipe === 'baru')
            <div class="alert alert-warning">
                Ada pengajuan retur baru yang memerlukan persetujuan Anda. Silakan review dan ubah statusnya di sistem.
            </div>
        @elseif($tipe === 'disetujui')
            <div class="alert alert-success">
                Retur ini telah <strong>disetujui</strong>. Stok bahan baku <strong>{{ $retur->bahan?->nama_bahan }}</strong> otomatis berkurang sebanyak <strong>{{ $retur->jumlah }} {{ $retur->bahan?->satuan }}</strong>.
            </div>
        @else
            <div class="alert alert-danger">
                Retur ini telah <strong>ditolak</strong>. Stok bahan baku tidak mengalami perubahan.
            </div>
        @endif

        {{-- Info Retur --}}
        <div class="card">
            <div class="card-title">Detail Retur</div>

            <div class="info-row">
                <span class="info-label">ID Retur</span>
                <span class="info-value">#{{ $retur->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Supplier</span>
                <span class="info-value">{{ $retur->pembelian?->supplier?->nama_supplier ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Bahan Diretur</span>
                <span class="info-value">{{ $retur->bahan?->nama_bahan ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jumlah</span>
                <span class="info-value">{{ $retur->jumlah }} {{ $retur->bahan?->satuan ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipe Retur</span>
                <span class="info-value">
                    <span class="badge tipe-{{ $retur->tipe_retur }}">
                        {{ match($retur->tipe_retur) {
                            'rusak'       => 'Rusak / Cacat',
                            'salah_kirim' => 'Salah Kirim',
                            'kelebihan'   => 'Kelebihan Stok',
                            default       => 'Lainnya'
                        } }}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="badge badge-{{ $retur->status }}">{{ ucfirst($retur->status) }}</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Retur</span>
                <span class="info-value">{{ $retur->tgl_retur?->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Diproses Oleh</span>
                <span class="info-value">{{ $retur->karyawan?->nama ?? '-' }}</span>
            </div>
            @if($retur->alasan)
            <div class="info-row">
                <span class="info-label">Alasan</span>
                <span class="info-value" style="max-width:60%">{{ $retur->alasan }}</span>
            </div>
            @endif
        </div>

    </div>

    {{-- ── FOOTER ──────────────────────────────────── --}}
    <div class="footer">
        <p>Email ini dikirim otomatis oleh sistem <strong>Pipindonuts</strong>.<br>
        Jangan balas email ini secara langsung.</p>
    </div>

</div>
</body>
</html>
