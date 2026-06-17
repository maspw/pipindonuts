<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ── TYPE SELECTOR ──────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4">
                Pilih Jenis Analitik
            </p>
            <div class="flex flex-wrap gap-3">
                @foreach ($this->getTypes() as $key => $type)
                    <button
                        wire:click="switchType('{{ $key }}')"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border transition-all duration-200 cursor-pointer
                            {{ $activeType === $key
                                ? 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-200 dark:shadow-amber-900/40 scale-105'
                                : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-400 hover:text-amber-600 dark:hover:text-amber-400' }}"
                    >
                        <span class="text-base leading-none">{{ $type['icon'] }}</span>
                        <span>{{ $type['label'] }}</span>
                        @if ($activeType === $key)
                            <span wire:loading wire:target="switchType" class="ml-1">
                                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ── SUMMARY CARDS ───────────────────────────────────────────── --}}
        @if (count($summary) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($summary as $i => $s)
                    <div class="rounded-2xl p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ $s['label'] }}
                        </div>
                        <div class="mt-2 flex items-end gap-1">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">
                                {{ $s['count'] }}
                            </span>
                            <span class="text-sm font-normal text-gray-400 mb-1">item</span>
                        </div>
                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                            <div class="flex justify-between">
                                <span>Ø {{ $xAxisLabel }}</span>
                                <strong>{{ number_format($s['avg_x'], 1) }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Ø {{ $yAxisLabel }}</span>
                                <strong>{{ number_format($s['avg_y'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif


        {{-- ── SCATTER CHART ────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                        {{ $this->getTypes()[$activeType]['icon'] }}
                        {{ $this->getTypes()[$activeType]['label'] }}
                    </h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        K-Means Clustering (k=3) · Normalisasi Z-Score
                    </p>
                </div>
                <span class="text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 px-3 py-1 rounded-full font-medium">
                    {{ array_sum(array_column($summary, 'count')) }} data
                </span>
            </div>

            <div wire:loading wire:target="switchType" class="flex items-center justify-center h-64 text-amber-500">
                <div class="text-center">
                    <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <p class="text-sm font-medium">Menjalankan K-Means…</p>
                </div>
            </div>

            <div wire:loading.remove wire:target="switchType" style="height: 440px;">
                <canvas id="clusterChart"></canvas>
            </div>
        </div>

        {{-- ── DETAIL LIST PER CLUSTER ──────────────────────────────────── --}}
        <div wire:loading.remove wire:target="switchType">
            @if (count(array_filter($clusterData)) > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $borderColors = [
                            'border-red-200 dark:border-red-800',
                            'border-amber-200 dark:border-amber-800',
                            'border-emerald-200 dark:border-emerald-800',
                        ];
                        $badgeStyles = [
                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                            'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                        ];
                    @endphp
                    @foreach ($clusterLabels as $i => $label)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-2 {{ $borderColors[$i] ?? '' }} overflow-hidden">
                            <div class="px-4 py-3 border-b {{ $borderColors[$i] ?? '' }} flex items-center justify-between">
                                <span class="font-semibold text-sm text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $badgeStyles[$i] ?? '' }}">
                                    {{ count($clusterData[$i] ?? []) }}
                                </span>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700 max-h-52 overflow-y-auto text-sm">
                                @forelse ($clusterData[$i] ?? [] as $pt)
                                    <li class="px-4 py-2.5 flex justify-between items-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <span class="truncate max-w-[55%] font-medium">{{ $pt['name'] }}</span>
                                        <span class="text-xs text-gray-400 text-right leading-snug shrink-0 ml-2">
                                            x: {{ number_format($pt['x'], 0, ',', '.') }}<br>
                                            y: {{ number_format($pt['y'], 0, ',', '.') }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="px-4 py-4 text-gray-400 text-xs italic text-center">Tidak ada data</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-14 text-gray-400 dark:text-gray-600">
                    <svg class="mx-auto h-14 w-14 opacity-30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm">Belum ada data untuk clustering ini.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ── CHART JS ──────────────────────────────────────────────────── --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const CLUSTER_COLORS = ['rgb(239,68,68)', 'rgb(245,158,11)', 'rgb(16,185,129)'];
        let chartInstance    = null;

        // Plugin: buat background chart jadi putih
        const whiteBackgroundPlugin = {
            id: 'customCanvasBg',
            beforeDraw(chart) {
                const { ctx, chartArea } = chart;
                if (!chartArea) return;
                ctx.save();
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(chartArea.left, chartArea.top, chartArea.width, chartArea.height);
                ctx.restore();
            }
        };

        function renderClusterChart(data, labels, xLabel, yLabel) {
            const canvas = document.getElementById('clusterChart');
            if (!canvas) return;

            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }

            const datasets = [];
            for (let i = 0; i < 3; i++) {
                if (!(data[i] || []).length) continue;
                datasets.push({
                    label              : labels[i] || `Cluster ${i + 1}`,
                    data               : data[i],
                    backgroundColor    : 'white',
                    pointRadius        : 8,
                    pointHoverRadius   : 12,
                    pointBorderColor   : CLUSTER_COLORS[i],
                    pointBorderWidth   : 3,
                    pointHoverBackgroundColor: 'white',
                    pointHoverBorderWidth    : 4,
                });
            }

            chartInstance = new Chart(canvas.getContext('2d'), {
                type   : 'scatter',
                plugins: [whiteBackgroundPlugin],
                data   : { datasets },
                options: {
                    responsive         : true,
                    maintainAspectRatio: false,
                    animation          : { duration: 400 },
                    scales: {
                        x: {
                            title  : { display: true, text: xLabel, font: { size: 12, weight: '600' }, color: '#374151' },
                            ticks  : { color: '#6b7280' },
                            grid   : { color: 'rgba(0,0,0,0.08)' },
                            beginAtZero: true,
                        },
                        y: {
                            title  : { display: true, text: yLabel, font: { size: 12, weight: '600' }, color: '#374151' },
                            ticks  : { color: '#6b7280' },
                            grid   : { color: 'rgba(0,0,0,0.08)' },
                            beginAtZero: true,
                        },
                    },
                    plugins: {
                        legend: { position: 'top', labels: { padding: 16, usePointStyle: true } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const d = ctx.raw;
                                    return ` ${d.name}  ·  x: ${Number(d.x).toLocaleString('id-ID')}  |  y: ${Number(d.y).toLocaleString('id-ID')}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // ── Render pertama kali (data dari Blade saat mount) ──────────
        document.addEventListener('DOMContentLoaded', () => {
            renderClusterChart(
                @json($clusterData),
                @json($clusterLabels),
                @json($xAxisLabel),
                @json($yAxisLabel)
            );
        });

        // ── Re-render saat Livewire mengirim event clusterDataUpdated ──
        // (event ini di-dispatch dari switchType() di PHP)
        window.addEventListener('clusterDataUpdated', (e) => {
            const payload = e.detail[0] ?? e.detail;
            renderClusterChart(
                payload.clusterData,
                payload.clusterLabels,
                payload.xAxisLabel,
                payload.yAxisLabel
            );
        });
    </script>
</x-filament-panels::page>
