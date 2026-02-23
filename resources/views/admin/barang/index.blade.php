@extends('admin.layout.index')
@section('title', 'Master Barang')

@section('content')

    {{-- === STYLING MODAL === --}}
    <style type="text/tailwindcss">
        @layer components {
            .modal-overlay {
                @apply fixed inset-0 z-40 bg-black/50 backdrop-blur-sm hidden md:flex md:items-start md:justify-center overflow-y-auto;
            }

            .modal-panel {
                @apply w-full max-w-xl bg-white rounded-xl shadow-2xl ring-1 ring-black/5 md:mt-20;
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

            .btn {
                @apply inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium;
            }

            .btn-primary {
                @apply btn bg-indigo-600 text-white hover:bg-indigo-700;
            }

            .btn-secondary {
                @apply btn border border-gray-300 hover:bg-gray-50;
            }
        }
    </style>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Master Barang</h2>
            <a href="{{ route('barang.create') }}" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">+ Tambah</a>
        </div>

        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 p-3 rounded">{{ session('success') }}</div>
        @endif

        {{-- SEARCH + FILTER --}}
        <form method="GET" id="filterForm" class="flex gap-3 mb-4">
            <input type="text" name="q" id="searchInput"
                value="{{ $q }}"
                placeholder="Cari nama barang..."
                class="border rounded px-3 py-2 w-full md:w-72">

            <select name="kategori" id="kategoriFilter" class="border rounded px-3 py-2">
                <option value="">Semua Kategori</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k->id_kategori }}"
                        {{ request('kategori') == $k->id_kategori ? 'selected' : '' }}>
                        {{ $k->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border text-left">ID</th>
                        <th class="p-2 border text-left">Nama</th>
                        <th class="p-2 border text-left">Kategori</th>
                        <th class="p-2 border text-right">Qty</th>
                        <th class="p-2 border text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($data as $b)
                        <tr>
                            <td class="p-2 border">{{ $b->id_barang }}</td>
                            <td class="p-2 border">{{ $b->nama_barang }}</td>
                            <td class="p-2 border">{{ $b->kategori->nama_kategori ?? '-' }}</td>

                            <td class="p-2 border text-right {{ $b->qty <= 2 ? 'text-red-600 font-bold' : '' }}">
                                {{ $b->qty }}
                            </td>

                            <td class="p-2 border text-center">
                                {{-- RIWAYAT --}}
                                <button onclick="openRiwayat({{ $b->id_barang }}, '{{ addslashes($b->nama_barang) }}')">
                                    <img src="https://www.svgrepo.com/show/314165/history-solid.svg" class="w-5 h-5">
                                </button>

                                {{-- EDIT --}}
                                <button class="ml-2"
                                    onclick="document.getElementById('modal-{{ $b->id_barang }}').classList.remove('hidden')">
                                    <img src="https://www.svgrepo.com/show/313874/edit-solid.svg" class="w-5 h-5">
                                </button>

                                {{-- AJAX DELETE --}}
                                <button class="ml-2 deleteBtn" data-id="{{ $b->id_barang }}">
                                    <img src="https://cdn-icons-png.flaticon.com/256/215/215494.png" class="w-5 h-5">
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL EDIT --}}
                        <div id="modal-{{ $b->id_barang }}" class="modal-overlay hidden">
                            <div class="modal-panel animate-modal">

                                <div class="modal-header">
                                    <div class="modal-title">Edit Barang — {{ $b->nama_barang }}</div>
                                    <button class="modal-close"
                                        onclick="document.getElementById('modal-{{ $b->id_barang }}').classList.add('hidden')">✕</button>
                                </div>

                                <form method="POST" action="{{ route('barang.update', $b->id_barang) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-body">
                                        <div>
                                            <label class="form-label">Nama Barang</label>
                                            <input name="nama_barang" class="form-input"
                                                value="{{ $b->nama_barang }}" required>
                                        </div>

                                        <div>
                                            <label class="form-label">Kategori</label>
                                            <select name="id_kategori" class="form-input" required>
                                                @foreach ($kategori as $k)
                                                    <option value="{{ $k->id_kategori }}"
                                                        {{ $b->id_kategori == $k->id_kategori ? 'selected' : '' }}>
                                                        {{ $k->nama_kategori }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="document.getElementById('modal-{{ $b->id_barang }}').classList.add('hidden')">Batal</button>

                                        <button class="btn btn-primary">Update</button>
                                    </div>

                                </form>

                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>


    {{-- ===================== --}}
    {{-- TABEL NONAKTIF --}}
    {{-- ===================== --}}
    <div class="bg-white p-6 rounded shadow mt-6">
        <h2 class="text-lg font-semibold mb-4">Barang Nonaktif</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-red-50">
                    <tr>
                        <th class="p-2 border text-left">ID</th>
                        <th class="p-2 border text-left">Nama</th>
                        <th class="p-2 border text-left">Kategori</th>
                        <th class="p-2 border text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($nonaktif as $n)
                        <tr>
                            <td class="p-2 border">{{ $n->id_barang }}</td>
                            <td class="p-2 border">{{ $n->nama_barang }}</td>
                            <td class="p-2 border">{{ $n->kategori->nama_kategori ?? '-' }}</td>

                            <td class="p-2 border text-center">
                                {{-- AJAX RESTORE --}}
                                <button class="restoreBtn bg-green-600 text-white px-3 py-1 rounded"
                                    data-id="{{ $n->id_barang }}">
                                    Aktifkan
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">Tidak ada barang nonaktif.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $nonaktif->appends(request()->query())->links() }}
        </div>
    </div>



    {{-- ========================================================= --}}
    {{--           SCRIPT AJAX DELETE + RESTORE + FILTER           --}}
    {{-- ========================================================= --}}
    <script>
        // FILTER KATEGORI
        document.getElementById("kategoriFilter").addEventListener("change", function() {
            this.form.submit();
        });

        // DELETE BARANG (AJAX)
        document.addEventListener("click", async function(e) {
            let btn = e.target.closest(".deleteBtn");
            if (!btn) return;

            let id = btn.dataset.id;

            if (!confirm("Pindahkan barang ke nonaktif?")) return;

            let res = await fetch("{{ url('barang/ajax-delete') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ id })
            });

            let json = await res.json();
            if (json.status) {
                alert("Barang dipindahkan ke nonaktif.");
                location.reload();
            }
        });

        // RESTORE BARANG (AJAX)
        document.addEventListener("click", async function(e) {
            let btn = e.target.closest(".restoreBtn");
            if (!btn) return;

            let id = btn.dataset.id;

            let res = await fetch("{{ url('barang/ajax-restore') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ id })
            });

            let json = await res.json();
            if (json.status) {
                alert("Barang berhasil diaktifkan kembali!");
                location.reload();
            }
        });
    </script>
@endsection
{{-- ===================== --}}
{{--  MODAL RIWAYAT GLOBAL --}}
{{-- ===================== --}}
<div id="modal-riwayat" class="fixed inset-0 z-50 bg-black/50 hidden items-start justify-center overflow-y-auto">
    <div class="w-full max-w-4xl bg-white rounded-xl shadow-2xl ring-1 ring-black/5 mt-10 animate-modal">

        <div class="flex items-center justify-between px-5 py-3 border-b">
            <div class="font-semibold text-base" id="riwayat-title"></div>
            <button type="button" class="p-1 rounded hover:bg-gray-100 text-gray-500" onclick="closeRiwayat()">✕</button>
        </div>

        <div class="p-5">

            <div id="riwayat-loading" class="text-sm text-gray-500 mb-3 hidden">Memuat data...</div>

            <h3 class="font-semibold mb-2 text-base">Riwayat Transaksi</h3>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full border text-sm">
                    <thead class="bg-gray-100">
                        <tr class="text-center">
                            <th class="border p-2 w-12">No</th>
                            <th class="border p-2">Tanggal</th>
                            <th class="border p-2">No Surat</th>
                            <th class="border p-2">Jenis</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Unit Kerja</th>
                        </tr>
                    </thead>
                    <tbody id="riwayat-body"></tbody>
                </table>
            </div>

            <h3 class="font-semibold mb-2 text-base">Riwayat Peminjaman</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full border text-sm">
                    <thead class="bg-gray-100">
                        <tr class="text-center">
                            <th class="border p-2 w-12">No</th>
                            <th class="border p-2">Peminjam</th>
                            <th class="border p-2">Tanggal Pinjam</th>
                            <th class="border p-2">Tanggal Kembali</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody id="riwayat-peminjaman-body"></tbody>
                </table>
            </div>

        </div>

        <div class="px-5 py-3 border-t flex justify-end">
            <button type="button" class="btn btn-secondary" onclick="closeRiwayat()">Tutup</button>
        </div>

    </div>
</div>

{{-- ========================= --}}
{{-- SCRIPT RIWAYAT --}}
{{-- ========================= --}}
<script>
    async function openRiwayat(idBarang, namaBarang) {
        const modal = document.getElementById('modal-riwayat');
        const loading = document.getElementById('riwayat-loading');
        const body = document.getElementById('riwayat-body');
        const bodyPinjam = document.getElementById('riwayat-peminjaman-body');
        const title = document.getElementById('riwayat-title');

        title.textContent = 'Riwayat - ' + namaBarang;
        body.innerHTML = '';
        bodyPinjam.innerHTML = '';
        loading.classList.remove('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        try {
            const res = await fetch("{{ url('/barang') }}/" + idBarang + "/transaksi-json");
            const json = await res.json();
            loading.classList.add('hidden');

            if (!json.data?.length) {
                body.innerHTML = `<tr><td colspan="6" class="p-3 text-center text-gray-500">Tidak ada transaksi.</td></tr>`;
            } else {
                let no = 1;
                json.data.forEach(t => {
                    body.innerHTML += `
                        <tr class="text-center">
                            <td class="border p-2">${no++}</td>
                            <td class="border p-2">${formatTanggal(t.tanggal)}</td>
                            <td class="border p-2">${t.no_surat ?? '-'}</td>
                            <td class="border p-2">${t.jenis}</td>
                            <td class="border p-2">${t.qty}</td>
                            <td class="border p-2">${t.unit_kerja ?? '-'}</td>
                        </tr>`;
                });
            }
        } catch {
            body.innerHTML = `<tr><td colspan="6" class="p-3 text-center text-red-500">Gagal memuat transaksi.</td></tr>`;
        }

        try {
            const res2 = await fetch("{{ url('/barang') }}/" + idBarang + "/peminjaman-json");
            const json2 = await res2.json();

            if (!json2.data?.length) {
                bodyPinjam.innerHTML =
                    `<tr><td colspan="6" class="p-3 text-center text-gray-500">Tidak ada peminjaman.</td></tr>`;
            } else {
                let no = 1;
                bodyPinjam.innerHTML = "";
                json2.data.forEach(p => {
                    bodyPinjam.innerHTML += `
                        <tr class="text-center">
                            <td class="border p-2">${no++}</td>
                            <td class="border p-2">${p.user?.nama_user ?? '-'}</td>
                            <td class="border p-2">${formatTanggal(p.tanggal)}</td>
                            <td class="border p-2">${formatTanggal(p.tanggal_kembali)}</td>
                            <td class="border p-2">${p.jumlah}</td>
                            <td class="border p-2">${p.status}</td>
                        </tr>`;
                });
            }
        } catch {
            bodyPinjam.innerHTML =
                `<tr><td colspan="6" class="p-3 text-center text-red-500">Gagal memuat peminjaman.</td></tr>`;
        }
    }

    function closeRiwayat() {
        const modal = document.getElementById('modal-riwayat');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function formatTanggal(tgl) {
        if (!tgl) return "-";
        const d = new Date(tgl);
        return d.toLocaleDateString("id-ID");
    }
</script>