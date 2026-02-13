@extends('admin.layout.index')
@section('title', 'Data Transaksi')

@section('content')

    <style type="text/tailwindcss">
        @layer components {
            .modal-overlay {
                @apply fixed inset-0 z-40 bg-black/50 backdrop-blur-sm hidden md:flex md:items-start md:justify-center overflow-y-auto;
            }

            .modal-panel {
                @apply w-full max-w-3xl bg-white rounded-xl shadow-2xl ring-1 ring-black/5 md:mt-16;
            }

            .modal-header {
                @apply flex items-center justify-between px-5 py-3 border-b;
            }

            .modal-title {
                @apply font-semibold text-base;
            }

            .modal-close {
                @apply p-1 rounded hover:bg-gray-100 text-gray-500;
            }

            .modal-body {
                @apply p-5 space-y-4;
            }

            .modal-footer {
                @apply px-5 py-3 border-t flex justify-end gap-2;
            }

            .form-label {
                @apply block text-sm font-medium text-gray-700 mb-1;
            }

            .form-input {
                @apply w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500;
            }

            .form-select {
                @apply w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500;
            }

            .btn {
                @apply inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium;
            }

            .btn-primary {
                @apply btn bg-indigo-600 text-white hover:bg-indigo-700;
            }

            .btn-secondary {
                @apply btn border border-gray-300 hover:bg-gray-50;
            }

            @keyframes modalIn {
                from {
                    opacity: 0;
                    transform: translateY(10px) scale(.98)
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1)
                }
            }

            .animate-modal {
                animation: modalIn .18s ease-out;
            }
        }
    </style>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Data Transaksi</h2>
            <div class="flex gap-2">
                <!-- <button onclick="document.getElementById('modalAset').classList.remove('hidden')"
                    class="bg-green-600 text-white px-3 py-1 rounded">
                    + Tambah Aset
                </button> -->
                <a href="{{ route('transaksi.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded">+ Tambah</a>
                <a href="{{ route('laporan.index') }}" class="border px-3 py-1 rounded">Lihat Laporan</a>
            </div>
        </div>

        {{-- FILTER TANGGAL --}}
        <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-sm font-medium mb-1 block">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}" class="border rounded px-3 py-1">
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}" class="border rounded px-3 py-1">
            </div>
            <div>
                <label class="text-sm font-medium mb-1 block">No Surat</label>
                <input type="text" name="no_surat" value="{{ request('no_surat') }}" 
                    placeholder="Cari nomor surat..."
                    class="border rounded px-3 py-1">
            </div>
            <div>
                <button class="bg-blue-600 text-white px-4 py-1 rounded">Filter</button>
                @if (request('from') || request('to'))
                    <a href="{{ route('transaksi.index') }}" class="ml-2 text-sm text-gray-600 underline">Reset</a>
                @endif
            </div>
        </form>

        @if (session('success'))
            <div class="text-green-700 bg-green-100 p-2 mb-3 rounded">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 border text-center">No</th>
                        <!-- <th class="px-3 py-2 border">No Surat</th> -->
                         <th class="px-3 py-2 border cursor-pointer">
                            <a href="{{ route('transaksi.index', [
                                'sort' => 'no_surat',
                                'order' => request('order') === 'asc' ? 'desc' : 'asc'
                            ] + request()->except(['sort','order','page'])) }}"
                            class="flex items-center gap-1 select-none">

                                <span>No Surat</span>

                                {{-- ICON SORT --}}
                                @if(request('sort') === 'no_surat')
                                    @if(request('order') === 'asc')
                                        <span class="text-blue-600">▲</span>
                                    @else
                                        <span class="text-blue-600">▼</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">⇵</span>
                                @endif

                            </a>
                        </th>

                        <th class="px-3 py-2 border">Tanggal</th>
                        <th class="px-3 py-2 border">Jenis</th>
                        <th class="px-3 py-2 border">Unit</th>
                        <th class="px-3 py-2 border">Nama Barang</th>
                        <th class="px-3 py-2 border text-center">Tanda Terima</th>
                        <th class="px-3 py-2 border text-center">Berita Acara</th>
                        <th class="px-3 py-2 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = ($transaksi->currentPage() - 1) * $transaksi->perPage() + 1;
                    @endphp
                    @foreach ($transaksi as $t)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $no++ }}</td>
                            <td class="border px-3 py-2">{{ $t->no_surat }}</td>
                            <td class="border px-3 py-2">{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
                            <td class="border px-3 py-2 capitalize">{{ $t->jenis }}</td>
                            <td class="border px-3 py-2">{{ $t->unit->unit_kerja ?? '-' }}</td>
                            <td class="border px-3 py-2">
                                @foreach ($t->details as $d)
                                    <div>
                                        {{ $d->barang->nama_barang }}
                                        <span class="text-gray-500">(Qty: {{ $d->qty }})</span>
                                    </div>
                                @endforeach
                            </td>

                            {{-- ✅ Kolom Tanda Terima --}}
                            <td class="border px-3 py-2 text-center">
                                @if ($t->tanda_terima)
                                    <a href="{{ asset('storage/' . $t->tanda_terima) }}" target="_blank"
                                        class="text-green-600 font-bold text-lg">✔️</a>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>

                            {{-- ✅ Kolom Berita Acara --}}
                            <td class="border px-3 py-2 text-center">
                                @if ($t->berita_acara)
                                    <a href="{{ asset('storage/' . $t->berita_acara) }}" target="_blank"
                                        class="text-green-600 font-bold text-lg">✔️</a>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>

                            <td class="border px-3 py-2 text-center">
                                <!-- <button class="text-blue-600 hover:underline"
                                    onclick="document.getElementById('modal-{{ $t->id_trans }}').classList.remove('hidden')">Edit</button> -->
                                    <button type="button" class="ml-1"
                                        onclick="document.getElementById('modal-{{ $t->id_trans }}').classList.remove('hidden')">
                                        <img src="https://www.svgrepo.com/show/313874/edit-solid.svg"
                                            class="w-5 h-5" alt="Edit Icon">
                                    </button>
                                <!-- <form action="{{ route('transaksi.destroy', $t->id_trans) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Hapus</button>
                                </form> -->
                            </td>
                        </tr>

                        {{-- === MODAL EDIT TRANSAKSI === --}}
                        <div id="modal-{{ $t->id_trans }}" class="modal-overlay hidden">
                            <div class="modal-panel animate-modal">
                                <div class="modal-header">
                                    <div class="modal-title">Edit Transaksi — {{ $t->no_surat }}</div>
                                    <button type="button" class="modal-close"
                                        onclick="document.getElementById('modal-{{ $t->id_trans }}').classList.add('hidden')">✕</button>
                                </div>

                                <form method="POST" action="{{ route('transaksi.update', $t->id_trans) }}"
                                    enctype="multipart/form-data">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="grid md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="form-label">No Surat</label>
                                                <input name="no_surat" value="{{ $t->no_surat }}" class="form-input"
                                                    required>
                                            </div>
                                            <div>
                                                <label class="form-label">Jenis</label>
                                                <select name="jenis" class="form-select" required>
                                                    <option value="masuk" @selected($t->jenis === 'masuk')>Masuk</option>
                                                    <option value="keluar" @selected($t->jenis === 'keluar')>Keluar</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Tanggal</label>
                                                <input type="date" name="tanggal" value="{{ $t->tanggal }}"
                                                    class="form-input" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Unit Kerja</label>
                                                <select name="id_unit" class="form-select">
                                                    <option value="">-- Pilih Unit --</option>
                                                    @foreach ($units as $u)
                                                        <option value="{{ $u->id_unit }}" @selected($t->id_unit == $u->id_unit)>
                                                            {{ $u->unit_kerja }} @if ($u->lokasi)
                                                                ({{ $u->lokasi }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="form-label">Keterangan</label>
                                                <input name="keterangan" value="{{ $t->keterangan }}" class="form-input">
                                            </div>

                                            {{-- ✅ Upload Dokumen (Gabungan TT & BA) --}}
<div class="mb-3">
    <label class="form-label">Upload Dokumen (Tanda Terima & Berita Acara)</label>

    {{-- Status file lama --}}
    <div class="text-sm mb-2 space-y-1">

        @if ($t->tanda_terima)
            <p class="text-green-600">
                ✔ Tanda Terima sudah diunggah —
                <a href="{{ asset('storage/' . $t->tanda_terima) }}"
                   target="_blank"
                   class="underline text-blue-600">
                    Lihat
                </a>
            </p>
        @endif

        @if ($t->berita_acara)
            <p class="text-green-600">
                ✔ Berita Acara sudah diunggah —
                <a href="{{ asset('storage/' . $t->berita_acara) }}"
                   target="_blank"
                   class="underline text-blue-600">
                    Lihat
                </a>
            </p>
        @endif

        @if (!$t->tanda_terima && !$t->berita_acara)
            <p class="text-gray-500">Belum ada dokumen yang diunggah.</p>
        @endif

    </div>

    {{-- Input multi file baru --}}
    <input type="file"
           name="dokumen[]"
           accept=".pdf,.doc,.docx"
           class="form-input"
           multiple>

    <p class="text-xs text-gray-600 mt-1">
        Upload MAX 2 file:
        <b>TT</b> = Tanda Terima — <b>BA</b> = Berita Acara.
        <br>Penamaan file contoh: <b>TT.pdf</b> & <b>BA.pdf</b>
    </p>
</div>

                                        </div>

                                        <hr class="my-4">

                                        <div class="flex items-center justify-between">
                                            <div class="font-medium">Detail Barang</div>
                                            <button type="button" class="btn btn-secondary"
                                                onclick="addRow{{ $t->id_trans }}()">+ Tambah Baris</button>
                                        </div>

                                        <div id="rows-{{ $t->id_trans }}" class="space-y-2">
                                            @foreach ($t->details as $i => $d)
                                            <div class="item-row grid md:grid-cols-6 gap-2">

                                                {{-- ID DETAIL (WAJIB untuk update) --}}
                                                <input type="hidden" name="items[{{ $i }}][id_detail]" value="{{ $d->id_detail }}">

                                                <div class="md:col-span-5">
                                                    <select name="items[{{ $i }}][id_barang]" class="form-select" required>
                                                        @foreach ($barang as $b)
                                                            <option value="{{ $b->id_barang }}"
                                                                @selected($b->id_barang == $d->id_barang)>
                                                                {{ $b->nama_barang }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <input type="number" min="1"
                                                        name="items[{{ $i }}][qty]"
                                                        class="form-input"
                                                        value="{{ $d->qty }}" required>
                                                </div>
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="document.getElementById('modal-{{ $t->id_trans }}').classList.add('hidden')">Batal</button>
                                        <button class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <script>
                            function addRow{{ $t->id_trans }}() {
                                const wrap = document.getElementById('rows-{{ $t->id_trans }}');
                                const idx = wrap.querySelectorAll('.item-row').length;

                                const div = document.createElement('div');
                                div.className = 'item-row grid md:grid-cols-6 gap-2';
                                div.innerHTML = `
                                    <input type="hidden" name="items[${idx}][id_detail]" value="">

                                    <div class="md:col-span-5">
                                        <select name="items[${idx}][id_barang]" class="form-select" required>
                                            @foreach ($barang as $b)
                                                <option value="{{ $b->id_barang }}">{{ $b->nama_barang }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <input type="number" min="1"
                                            name="items[${idx}][qty]"
                                            class="form-input"
                                            placeholder="Qty" required>
                                    </div>
                                `;
                                wrap.appendChild(div);
                            }

                            function reindexEdit{{ $t->id_trans }}() {
                                const rows = document.querySelectorAll('#rows-{{ $t->id_trans }} .item-row');
                                rows.forEach((row, i) => {
                                    row.querySelectorAll('select, input').forEach(el => {
                                        el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
                                    });
                                });
                            }

                            // sebelum submit EDIT
                            document
                            .querySelector('#modal-{{ $t->id_trans }} form')
                            .addEventListener('submit', function () {
                                reindexEdit{{ $t->id_trans }}();
                            });
                        </script>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if (method_exists($transaksi, 'links'))
            <div class="mt-4">
                {{ $transaksi->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
@endsection
