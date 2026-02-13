@extends('admin.layout.index')
@section('title', 'Master Barang')

@section('content')

    {{-- === STYLING MODAL (pakai Tailwind CDN yg sudah ada di layout) === --}}
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

            @keyframes modalIn {
                from {
                    opacity: 0;
                    transform: translateY(10px) scale(.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .animate-modal {
                animation: modalIn .18s ease-out;
            }
        }
    </style>

    <div class="bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Master Barang</h2>
            <a href="{{ route('barang.create') }}" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">+
                Tambah</a>
        </div>

        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 p-3 rounded">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-4">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama barang..."
                class="border rounded px-3 py-2 w-full md:w-72">
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border text-left">ID</th>
                        <th class="p-2 border text-left">Nama</th>
                        <th class="p-2 border text-right">Qty (Stok Sekarang)</th>
                        <th class="p-2 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $b)
                        <tr>
                            <td class="p-2 border">{{ $b->id_barang }}</td>
                            <td class="p-2 border">{{ $b->nama_barang }}</td>
                            <td class="p-2 border text-right">{{ $b->qty }}</td>
                            <td class="p-2 border text-center">
                                {{-- tombol riwayat --}}
                                <!-- <button type="button" class="text-indigo-600 hover:underline"
                                    onclick="openRiwayat({{ $b->id_barang }}, '{{ addslashes($b->nama_barang) }}')">
                                    Riwayat
                                </button> -->
                                <button type="button"
                                    class="ml-1"
                                    onclick="openRiwayat({{ $b->id_barang }}, '{{ addslashes($b->nama_barang) }}')">
                                    <img src="https://www.svgrepo.com/show/314165/history-solid.svg"
                                        class="w-5 h-5 text-indigo-600" alt="History Icon">
                                </button>

                                {{-- tombol edit --}}
                                <!-- <button type="button" class="text-blue-600 hover:underline ml-2"
                                    onclick="document.getElementById('modal-{{ $b->id_barang }}').classList.remove('hidden')">
                                    Edit
                                </button> -->
                                <button type="button"
                                    class="ml-2"
                                    onclick="document.getElementById('modal-{{ $b->id_barang }}').classList.remove('hidden')">
                                    <img src="https://www.svgrepo.com/show/313874/edit-solid.svg"
                                        class="w-5 h-5 text-blue-600" alt="Edit Icon">
                                </button>



                                {{-- tombol hapus --}}
                                <!-- <form action="{{ route('barang.destroy', $b->id_barang) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus barang ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form> -->
                            </td>
                        </tr>

                        {{-- =================== MODAL EDIT BARANG =================== --}}
                        <div id="modal-{{ $b->id_barang }}" class="modal-overlay hidden">
                            <div class="modal-panel animate-modal">
                                <div class="modal-header">
                                    <div class="modal-title">Edit Barang — {{ $b->nama_barang }}</div>
                                    <button type="button" class="modal-close"
                                        onclick="document.getElementById('modal-{{ $b->id_barang }}').classList.add('hidden')">✕</button>
                                </div>

                                <form method="POST" action="{{ route('barang.update', $b->id_barang) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-body">
                                        <div>
                                            <label class="form-label">Nama Barang</label>
                                            <input name="nama_barang" class="form-input" value="{{ $b->nama_barang }}"
                                                required>
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
                        {{-- ================= END MODAL ================= --}}
                    @empty
                        <tr>
                            <td class="p-4 text-center text-gray-500" colspan="4">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== modal riwayat global (disembunyikan default) ===== --}}
    <div id="modal-riwayat" class="fixed inset-0 z-50 bg-black/50 hidden items-start justify-center overflow-y-auto">
        <div class="w-full max-w-4xl bg-white rounded-xl shadow-2xl ring-1 ring-black/5 mt-10 animate-modal">
            <div class="flex items-center justify-between px-5 py-3 border-b">
                <div class="font-semibold text-base" id="riwayat-title">Riwayat Transaksi</div>
                <button type="button" class="p-1 rounded hover:bg-gray-100 text-gray-500"
                    onclick="closeRiwayat()">✕</button>
            </div>

            <div class="p-5">
                <div id="riwayat-loading" class="text-sm text-gray-500 mb-3 hidden">Memuat data...</div>

                {{-- ========================================= --}}
                {{--        TABEL RIWAYAT TRANSAKSI            --}}
                {{-- ========================================= --}}
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
                        <tbody id="riwayat-body">
                            {{-- diisi JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- ========================================= --}}
                {{--        TABEL RIWAYAT PEMINJAMAN           --}}
                {{-- ========================================= --}}
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
                        <tbody id="riwayat-peminjaman-body">
                            {{-- diisi JS --}}
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="px-5 py-3 border-t flex justify-end">
                <button type="button" class="btn btn-secondary" onclick="printRiwayat()">Print</button>
                <button type="button" class="btn btn-secondary" onclick="closeRiwayat()">Tutup</button>
            </div>
        </div>
    </div>


    <script>
        async function openRiwayat(idBarang, namaBarang) {
        const modal = document.getElementById('modal-riwayat');
        const loading = document.getElementById('riwayat-loading');
        const body = document.getElementById('riwayat-body');
        const bodyPinjam = document.getElementById('riwayat-peminjaman-body'); // ← TAMBAHAN
        const title = document.getElementById('riwayat-title');

        title.textContent = 'Riwayat Transaksi — ' + namaBarang;
        body.innerHTML = '';
        bodyPinjam.innerHTML = ''; // ← TAMBAHAN
        loading.classList.remove('hidden');

        // tampilkan modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // ==============================
        //       LOAD TRANSAKSI
        // ==============================
        try {
            const res = await fetch("{{ url('/barang') }}/" + idBarang + "/transaksi-json");
            const json = await res.json();
            loading.classList.add('hidden');

            if (!json.data || json.data.length === 0) {
                body.innerHTML =
                    '<tr><td colspan="6" class="text-center text-gray-500 p-3">Belum ada transaksi.</td></tr>';
            } else {
                let no = 1;
                json.data.forEach(function(row) {
                    const tr = document.createElement('tr');
                    tr.className = 'text-center hover:bg-gray-50';
                    tr.innerHTML = `
                        <td class="border p-2">${no++}</td>
                        <td class="border p-2">${formatTanggal(row.tanggal)}</td>
                        <td class="border p-2">${row.no_surat ?? '-'}</td>
                        <td class="border p-2">
                            ${row.jenis === 'masuk'
                                ? '<span class="text-green-700 font-semibold">Masuk</span>'
                                : '<span class="text-red-700 font-semibold">Keluar</span>'}
                        </td>
                        <td class="border p-2 font-semibold">${row.qty ?? '-'}</td>
                        <td class="border p-2 text-left">${row.unit_kerja ?? '-'}</td>
                    `;
                    body.appendChild(tr);
                });
            }
        } catch (err) {
            loading.classList.add('hidden');
            body.innerHTML =
                '<tr><td colspan="6" class="text-center text-red-500 p-3">Gagal memuat data.</td></tr>';
        }

        // ==============================
        //      LOAD PEMINJAMAN
        // ==============================
        bodyPinjam.innerHTML =
            '<tr><td colspan="6" class="text-center text-gray-500 p-3">Memuat...</td></tr>';

        try {
            const resPinjam = await fetch("{{ url('/barang') }}/" + idBarang + "/peminjaman-json");
            const jsonPinjam = await resPinjam.json();

            if (!jsonPinjam.data || jsonPinjam.data.length === 0) {
                bodyPinjam.innerHTML =
                    '<tr><td colspan="6" class="text-center text-gray-500 p-3">Belum ada peminjaman.</td></tr>';
            } else {
                bodyPinjam.innerHTML = '';
                let no = 1;
                jsonPinjam.data.forEach(function(row) {
                    const tr = document.createElement('tr');
                    tr.className = 'text-center hover:bg-gray-50';
                    tr.innerHTML = `
                        <td class="border p-2">${no++}</td>
                        <td class="border p-2">${row.user?.nama_user ?? '-'}</td>
                        <td class="border p-2">${formatTanggal(row.tanggal)}</td>
                        <td class="border p-2">${formatTanggal(row.tanggal_kembali)}</td>
                        <td class="border p-2 font-semibold">${row.jumlah ?? '-'}</td>
                        <td class="border p-2">${row.status ?? '-'}</td>
                    `;
                    bodyPinjam.appendChild(tr);
                });
            }
        } catch (err) {
            bodyPinjam.innerHTML =
                '<tr><td colspan="6" class="text-center text-red-500 p-3">Gagal memuat data peminjaman.</td></tr>';
        }
    }


        function closeRiwayat() {
            const modal = document.getElementById('modal-riwayat');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function formatTanggal(tgl) {
            if (!tgl) return '-';
            const d = new Date(tgl);
            if (isNaN(d)) return tgl;
            const day = String(d.getDate()).padStart(2, '0');
            const mon = String(d.getMonth() + 1).padStart(2, '0');
            const yr = d.getFullYear();
            return `${day}/${mon}/${yr}`;
        }
        function printRiwayat() {
            const title = document.getElementById('riwayat-title').innerText;
            const table = document.querySelector('#modal-riwayat table').outerHTML;

            const win = window.open('', '', 'width=900,height=650');
            win.document.write(`
                <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { font-family: Arial; padding: 20px; }
                        h2 { text-align: center; margin-bottom: 15px; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; }
                        th, td { border: 1px solid #000; padding: 6px; }
                        th { background: #f0f0f0; }
                    </style>
                </head>
                <body>
                    <h2>${title}</h2>
                    ${table}
                </body>
                </html>
            `);
            win.document.close();
            win.focus();
            win.print();
            win.close();
        }
        // TUTUP MODAL RIWAYAT JIKA KLIK DI LUAR PANEL
        document.getElementById('modal-riwayat').addEventListener('click', function (e) {
            // jika yang diklik adalah area overlay (bukan panel)
            if (e.target === this) {
                closeRiwayat();
            }
        });

    </script>

@endsection
