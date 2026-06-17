<x-filament-panels::page>
    {{-- 1. Merender Widget Grafik Bawaan di Atas --}}
    @if ($headerWidgets = $this->getHeaderWidgets())
        <x-filament-widgets::widgets
            :columns="$this->getHeaderWidgetsColumns()"
            :widgets="$headerWidgets"
            :data="$propertyData"
        />
    @endif

    {{-- 2. INJEKSI KOTAK TEKS AI: Pasti Muncul & Melar Panjang ke Samping --}}
    @php
        $trendAlasan = session('ai_trend_alasan') ?? "• ⚡ **Menunggu Analisis**: Silakan klik tombol \"Refresh AI Marketing Insights\" di atas untuk meramal perilaku pasar Pipindonuts 2026.";
        $formattedText = preg_replace('/\\*\\*(.*?)\\*\\*/', '<strong class="text-amber-500 font-semibold">$1</strong>', $trendAlasan);
    @endphp

    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 block w-full text-left my-4" style="width: 100%; min-width: 100%;">
        <div class="flex items-center gap-2 mb-3 border-b border-gray-200 dark:border-gray-800 pb-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                📊 AI Marketing & Trend Analysis — Data Tren Aktif 2026
            </span>
        </div>
        <div class="text-sm leading-relaxed text-gray-500 dark:text-gray-400 space-y-2.5" style="white-space: pre-line;">
            {!! nl2br($formattedText) !!}
        </div>
    </div>

    {{-- 3. Merender Tabel Bawaan Filament di Bawah --}}
    {{ $this->table }}
</x-filament-panels::page>