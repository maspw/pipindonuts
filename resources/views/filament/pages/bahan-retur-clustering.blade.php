<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Chart --}}
        <div class="bg-white p-6 rounded-xl shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                Scatter Plot K-Means Clustering Bahan Retur
            </h2>
            <div style="width: 100%; height: 500px;">
                <canvas id="returBahanChart"></canvas>
            </div>
        </div>

        {{-- Legend / Tabel Keterangan --}}
        <div class="bg-white p-6 rounded-xl shadow dark:bg-gray-800">
            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-3">Keterangan Cluster</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $colors = ['bg-red-100 text-red-700 border-red-300', 'bg-yellow-100 text-yellow-700 border-yellow-300', 'bg-green-100 text-green-700 border-green-300'];
                    $desc   = [
                        'Bahan dengan frekuensi retur tinggi. Perlu perhatian khusus pada kualitas atau supplier.',
                        'Bahan dengan retur sedang. Perlu dimonitor secara berkala.',
                        'Bahan dengan retur rendah. Kondisi aman.',
                    ];
                @endphp
                @foreach ($clusterLabels as $i => $label)
                    <div class="border rounded-lg p-4 {{ $colors[$i] ?? 'bg-gray-100 text-gray-700 border-gray-300' }}">
                        <div class="font-bold text-lg">{{ $label }}</div>
                        <div class="text-sm mt-1">{{ $desc[$i] ?? '' }}</div>
                        <div class="text-sm mt-2 font-medium">
                            {{ count($clusterData[$i] ?? []) }} bahan
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx         = document.getElementById('returBahanChart').getContext('2d');
            const backendData = @json($clusterData);
            const labelNames  = @json($clusterLabels);

            const colors = [
                'rgb(244, 63, 94)',   // merah  - high risk
                'rgb(234, 179, 8)',   // kuning - medium risk
                'rgb(16, 185, 129)',  // hijau  - low risk
            ];

            const datasets = [];
            for (let i = 0; i < 3; i++) {
                if ((backendData[i] || []).length === 0) continue;
                datasets.push({
                    label           : labelNames[i] || `Cluster ${i + 1}`,
                    data            : backendData[i] || [],
                    backgroundColor : colors[i],
                    pointRadius     : 9,
                    pointHoverRadius: 12,
                });
            }

            new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive         : true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: { display: true, text: 'Jumlah Stok Aktual Bahan', font: { size: 13 } },
                            beginAtZero: true,
                        },
                        y: {
                            title: { display: true, text: 'Total Jumlah Retur Pembelian', font: { size: 13 } },
                            beginAtZero: true,
                        },
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const d = ctx.raw;
                                    return `${d.name} → Stok: ${d.x}, Retur: ${d.y}`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-filament-panels::page>