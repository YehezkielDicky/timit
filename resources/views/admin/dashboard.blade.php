@extends('admin.layout.index')
@section('title', 'Dashboard Admin')

@section('content')
    <div class="space-y-6">

        {{-- Cards ringkasan --}}
        <div class="grid gap-4 md:grid-cols-5">
            <div class="bg-white p-4 rounded shadow">
                <div class="text-xs text-gray-500">Barang Keluar (bulan ini)</div>
                <div class="text-2xl font-semibold mt-1">{{ number_format($sumKeluarBulanIni) }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-xs text-gray-500">Barang Masuk (bulan ini)</div>
                <div class="text-2xl font-semibold mt-1">{{ number_format($sumMasukBulanIni) }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-xs text-gray-500">Total Transaksi (bulan ini)</div>
                <div class="text-2xl font-semibold mt-1">{{ number_format($countTransaksiBulanIni) }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-xs text-gray-500">Unit Aktif (bulan ini)</div>
                <div class="text-2xl font-semibold mt-1">{{ number_format($countUnitAktifBulanIni) }}</div>
            </div>

            {{-- 🔴 Tambahan: stok minimum --}}
            <div class="bg-white p-4 rounded shadow border-l-4 border-red-500">
                <div class="text-xs text-gray-500">Barang Stok Menipis (≤ 2)</div>
                <div class="text-2xl font-semibold mt-1 text-red-600">
                    {{ $lowStockCount }}
                </div>
            </div>
        </div>

        {{-- Grid grafik --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="bg-white p-4 rounded shadow lg:col-span-2">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold">Total Transaksi / Bulan (12 bulan)</h3>
                </div>
                <canvas id="chartMonthly" height="100"></canvas>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold">Top Unit Peminta (bulan ini)</h3>
                </div>
                <canvas id="chartUnit" height="100"></canvas>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold">Top Barang Keluar (bulan ini)</h3>
                </div>
                <canvas id="chartBarang" height="160"></canvas>
            </div>

            {{-- Kotak aktivitas sederhana --}}
            <div class="bg-white p-4 rounded shadow lg:col-span-2">
                <h3 class="font-semibold mb-2">Catatan</h3>
                <ul class="text-sm text-gray-600 list-disc pl-5">
                    <li>Grafik kiri: jumlah transaksi (masuk+keluar) per bulan.</li>
                    <li>Grafik kanan atas: 5 unit kerja dengan permintaan terbanyak bulan ini.</li>
                    <li>Grafik bawah: 5 barang paling sering keluar bulan ini (berdasarkan qty).</li>
                </ul>
            </div>
        </div>

        {{-- 🔴 Tabel barang stok minimum --}}
        @if($lowStockCount > 0)
            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-semibold mb-3 text-red-600">
                    ⚠️ Barang dengan Stok Menipis (≤ 2)
                </h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-2 border">ID</th>
                                <th class="p-2 border">Nama Barang</th>
                                <th class="p-2 border text-center">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockItems as $item) 
                                <tr class="hover:bg-red-50">
                                    <td class="p-2 border">{{ $item->id_barang }}</td>
                                    <td class="p-2 border">{{ $item->nama_barang }}</td>
                                    <td class="p-2 border text-center font-semibold text-red-600">
                                        {{ $item->qty }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const monthlyLabels = @json($monthlyLabels);
        const monthlyData = @json($monthlyData);

        const topBarangLabels = @json($topBarangLabels);
        const topBarangData = @json($topBarangData);

        const topUnitLabels = @json($topUnitLabels);
        const topUnitData = @json($topUnitData);

        new Chart(document.getElementById('chartMonthly'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Transaksi',
                    data: monthlyData,
                    tension: .3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        new Chart(document.getElementById('chartUnit'), {
            type: 'doughnut',
            data: {
                labels: topUnitLabels,
                datasets: [{ data: topUnitData }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        new Chart(document.getElementById('chartBarang'), {
            type: 'bar',
            data: {
                labels: topBarangLabels,
                datasets: [{
                    label: 'Qty Keluar',
                    data: topBarangData
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
@endsection
