@extends('admin.layout.index')
@section('title', 'Laporan Transaksi')

@section('content')
    <div class="bg-white p-6 rounded shadow print:p-0 print:shadow-none">
        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-4 print:hidden">
            <h2 class="text-xl font-semibold">Laporan Transaksi</h2>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded flex items-center gap-2">
                    🖨 <span>Print</span>
                </button>
                    <a href="{{ route('laporan.export', ['from' => $from, 'to' => $to]) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Export Excel
                </a>

            </div>
        </div>

        {{-- FILTER TANGGAL + BARANG --}}
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-4 print:hidden">

            {{-- Dari tanggal --}}
            <div>
                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="border rounded px-3 py-2">
            </div>

            {{-- Sampai tanggal --}}
            <div>
                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="border rounded px-3 py-2">
            </div>

            {{-- PILIH BARANG --}}
            <div>
                <label class="block text-sm font-medium mb-1">Nama Barang</label>
                <select name="barang" class="border rounded px-3 py-2 w-56">
                    <option value="">-- Semua Barang --</option>

                    @foreach ($barang as $b)
                        <option value="{{ $b->nama_barang }}"
                            {{ request('barang') == $b->nama_barang ? 'selected' : '' }}>
                            {{ $b->nama_barang }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Filter + Export --}}
            <div class="flex gap-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Filter
                </button>

                @if ($from && $to)
                    <a href="{{ route('laporan.export', [
                        'from' => $from,
                        'to' => $to,
                        'barang' => request('barang')
                    ]) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Export Excel
                    </a>
                @endif
            </div>

        </form>


        {{-- HEADER CETAK --}}
        <div class="hidden print:block text-center mb-6">
            <h1 class="text-xl font-bold uppercase">Laporan Transaksi Barang</h1>
            @if ($from && $to)
                <p class="text-sm">
                    Periode:
                    <strong>{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</strong> -
                    <strong>{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
                </p>
            @endif
            <p class="text-sm mt-1">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
            <hr class="my-3 border-gray-400">
        </div>

        {{-- === PRE-COMPUTE SALDO & PREPARE ROWS === --}}
        @php
            // flatten rows: setiap detail jadi satu baris
            $rows = [];
            foreach ($query as $h) {
                foreach ($h->details as $d) {
                    $rows[] = ['h' => $h, 'd' => $d];
                }
            }

            // hitung delta_all dan current per barang (sama seperti sebelumnya)
            $acc = [];
            foreach ($rows as $row) {
                $h = $row['h'];
                $d = $row['d'];
                $id = $d->id_barang;
                $delta = $h->jenis === 'masuk' ? (int) $d->qty : -(int) $d->qty;

                if (!isset($acc[$id])) {
                    $acc[$id] = [
                        'delta_all' => 0,
                        'current' => (int) ($d->barang->qty ?? 0),
                    ];
                }
                $acc[$id]['delta_all'] += $delta;
            }

            foreach ($acc as $id => $v) {
                $opening = $v['current'] - $v['delta_all'];
                $acc[$id]['opening'] = $opening;
                $acc[$id]['running'] = $opening;
            }

            // SORT rows: untuk barang yang sama => jenis 'masuk' dulu, lalu 'keluar', lalu tanggal
            usort($rows, function ($a, $b) {
                $dA = $a['d']->barang->nama_barang ?? '';
                $dB = $b['d']->barang->nama_barang ?? '';

                // jika barang sama, urutkan: masuk dulu lalu keluar, lalu tanggal
                if ($a['d']->id_barang == $b['d']->id_barang) {
                    if ($a['h']->jenis !== $b['h']->jenis) {
                        return $a['h']->jenis === 'masuk' ? -1 : 1;
                    }
                    // sama jenis -> urut tanggal ascending
                    return strcmp($a['h']->tanggal, $b['h']->tanggal);
                }

                // beda barang -> urut berdasarkan tanggal agar tetap kronologis antar barang
                return strcmp($a['h']->tanggal, $b['h']->tanggal);
            });

            // nomor awal (jaga kompatibilitas pagination jika $query adalah paginator)
            if (is_object($query) && method_exists($query, 'currentPage')) {
                $no = ($query->currentPage() - 1) * $query->perPage() + 1;
            } else {
                $no = 1;
            }
        @endphp

        {{-- === TABEL LAPORAN === --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm border-gray-300">
                <thead class="bg-gray-100">
                    <tr class="text-center">
                        <th class="border px-3 py-2 w-12">No</th>
                        <th class="border px-3 py-2">Nama Barang</th>
                        <th class="border px-3 py-2">No Surat</th>
                        <th class="border px-3 py-2">Tanggal</th>
                        <th class="border px-3 py-2">Jenis</th>
                        <th class="border px-3 py-2">Masuk</th>
                        <th class="border px-3 py-2">Keluar</th>
                        <th class="border px-3 py-2">Unit Kerja</th>
                        <th class="border px-3 py-2">TT</th>
                        <th class="border px-3 py-2">BA</th>
                        <th class="border px-3 py-2">Keterangan</th>
                        <th class="border px-3 py-2">Total Persediaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $h = $row['h'];
                            $d = $row['d'];

                            $id = $d->id_barang;
                            $delta = $h->jenis === 'masuk' ? (int) $d->qty : -(int) $d->qty;

                            // update running berdasarkan urutan baru
                            $acc[$id]['running'] += $delta;
                            $saldoBaris = $acc[$id]['running'];
                        @endphp

                        <tr class="hover:bg-gray-50 text-center">
                            <td class="border px-3 py-2">{{ $no++ }}</td>
                            <td class="border px-3 py-2 text-left">{{ $d->barang->nama_barang ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $h->no_surat }}</td>
                            <td class="border px-3 py-2">{{ \Carbon\Carbon::parse($h->tanggal)->format('d/m/Y') }}</td>
                            <td class="border px-3 py-2 capitalize">{{ $h->jenis }}</td>
                            <td class="border px-3 py-2 text-green-700 font-semibold">
                                {{ $h->jenis === 'masuk' ? $d->qty : '-' }}
                            </td>
                            <td class="border px-3 py-2 text-red-700 font-semibold">
                                {{ $h->jenis === 'keluar' ? $d->qty : '-' }}
                            </td>
                            <td class="border px-3 py-2 text-left">{{ $h->unit->unit_kerja ?? '-' }}</td>

                            {{-- ✅ Kolom Tanda Terima --}}
                            <td class="border px-3 py-2 text-center">
                                @if ($h->tanda_terima)
                                    <span class="text-green-600 font-bold">✔️</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- ✅ Kolom Berita Acara --}}
                            <td class="border px-3 py-2 text-center">
                                @if ($h->berita_acara)
                                    <span class="text-green-600 font-bold">✔️</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>

                             <td class="border px-3 py-2 text-left">
                                {{ $h->keterangan ?? '-' }}
                            </td>
                            <td class="border px-3 py-2 font-bold">{{ $saldoBaris }}</td>
                           
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="border px-3 py-4 text-center text-gray-500">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if (method_exists($query, 'links'))
            <div class="mt-4 print:hidden">
                {{ $query->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

    {{-- === CSS PRINT === --}}
    <style>
        @media print {
            body {
                background: white !important;
                font-size: 12px;
                color: #000;
                margin: 10mm;
            }

            .print\:hidden {
                display: none !important;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                font-size: 11px;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 6px 8px !important;
            }

            th {
                background-color: #e5e5e5 !important;
                font-weight: bold;
                text-align: center;
            }

            h1,
            h2,
            h3 {
                margin: 0;
                padding: 0;
            }

            hr {
                border-top: 1px solid #000;
                margin-top: 4px;
                margin-bottom: 10px;
            }

            .bg-white {
                background: white !important;
                box-shadow: none !important;
            }
        }
    </style>
@endsection
