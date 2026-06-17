<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:bg-gray-900 dark:border-white/10">
    <div class="mb-4 border-b pb-2 font-bold text-lg text-gray-800 dark:text-white">
        Ringkasan Pembayaran
    </div>
    <div class="space-y-3">
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">ID Pembelian</span>
            <span class="font-bold">{{ $no_faktur }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">Tanggal</span>
            <span>{{ $tgl }}</span>
        </div>
        <div class="pt-4 border-t border-dashed flex justify-between items-center">
            <span class="font-bold text-gray-800 dark:text-white">Total Bayar</span>
            <span class="text-2xl font-black text-success-600">
                Rp {{ number_format($total, 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>