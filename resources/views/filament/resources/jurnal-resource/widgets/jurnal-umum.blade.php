<x-filament-widgets::widget>
    <x-filament::section>

        <div class="overflow-x-auto">

            <!-- FILTER -->
            <form wire:submit.prevent="filterJurnal">
                <label for="periode" class="font-medium">Pilih Periode:</label>

                <input type="month"
                    wire:model="periode"
                    id="periode"
                    class="border border-blue-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-400">

                <button type="submit"
                    class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                    Filter
                </button>
            </form>

            <br><br>

            <!-- HEADER -->
            <div class="text-center bg-blue-600 text-white py-4 rounded-md shadow">

                <b>PIPIN DONUTS</b><br>
                <b>Jurnal Umum</b><br>

                <b>
                    Periode
                    {{ $periode
                        ? \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y')
                        : now()->translatedFormat('F Y')
                    }}
                </b>

            </div>

            <br>

            <!-- TABLE -->
            <table class="w-full text-sm text-left border border-blue-200">

                <thead class="bg-blue-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-2 border">ID Jurnal</th>
                        <th class="px-4 py-2 border">Tanggal</th>
                        <th class="px-4 py-2 border">Akun</th>
                        <th class="px-4 py-2 border">Reff</th>
                        <th class="px-4 py-2 border">Debet</th>
                        <th class="px-4 py-2 border">Kredit</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($jurnals as $jurnal)
                        @foreach($jurnal->jurnaldetail as $detail)
                            <tr class="hover:bg-blue-50">

                                <!-- ID & Tanggal -->
                                <td class="px-4 py-2 border">{{ $jurnal->id }}</td>
                                <td class="px-4 py-2 border">
                                    {{ \Carbon\Carbon::parse($jurnal->tgl)->format('Y-m-d') }}
                                </td>

                                <!-- AKUN -->
                                <td class="px-4 py-2 border">
                                    {{ $detail->coa->nama_akun ?? '-' }}
                                </td>

                                <!-- REFF -->
                                <td class="px-4 py-2 border">
    {{ $detail->coa->kode_akun ?? '-' }}
</td>

                                <!-- DEBIT -->
                                <td class="px-4 py-2 border text-right text-blue-700">
                                    {{ $detail->debit != 0
                                        ? 'Rp ' . number_format($detail->debit, 0, ',', '.')
                                        : ''
                                    }}
                                </td>

                                <!-- CREDIT -->
                                <td class="px-4 py-2 border text-right text-red-600">
                                    {{ $detail->credit != 0
                                        ? 'Rp ' . number_format($detail->credit, 0, ',', '.')
                                        : ''
                                    }}
                                </td>

                            </tr>
                        @endforeach
                    @endforeach
                </tbody>

                <!-- TOTAL -->
                <tfoot>
                    <tr class="font-semibold bg-blue-100">

                        <td colspan="4" class="text-right px-4 py-2 border">
                            Total
                        </td>

                        <td class="text-right px-4 py-2 border text-blue-700">
                            {{ 'Rp ' . number_format($jurnals->flatMap->jurnaldetail->sum('debit'), 0, ',', '.') }}
                        </td>

                        <td class="text-right px-4 py-2 border text-red-600">
                            {{ 'Rp ' . number_format($jurnals->flatMap->jurnaldetail->sum('credit'), 0, ',', '.') }}
                        </td>

                    </tr>
                </tfoot>

            </table>

        </div>

    </x-filament::section>
</x-filament-widgets::widget>