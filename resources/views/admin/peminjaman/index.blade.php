@extends('admin.layout.index')
@section('title', 'Peminjaman Barang')

@section('content')

    <style type="text/tailwindcss">
        @layer components {
            .modal-overlay {
                @apply fixed inset-0 z-40 bg-black/50 backdrop-blur-sm hidden md:flex md:items-start md:justify-center overflow-y-auto;
            }

            .modal-panel {
                @apply w-full max-w-2xl bg-white rounded-xl shadow-2xl ring-1 ring-black/5 md:mt-16;
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

            .badge {
                @apply text-xs px-2 py-0.5 rounded;
            }

            .badge-green {
                @apply badge bg-green-100 text-green-800;
            }

            .badge-yellow {
                @apply badge bg-yellow-100 text-yellow-800;
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
            <h2 class="text-lg font-semibold">Peminjaman Barang</h2>
            <button class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700"
                onclick="document.getElementById('modal-create').classList.remove('hidden')">
                + Tambah
            </button>
        </div>

        @if (session('success'))
            <div class="text-green-700 bg-green-100 p-2 mb-3 rounded">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="text-red-700 bg-red-100 p-2 mb-3 rounded">{{ $errors->first() }}</div>
        @endif

        <form method="GET" class="mb-4">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama barang / peminjam..."
                class="border rounded px-3 py-2 w-full md:w-80">
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 border">No</th>
                        <th class="px-3 py-2 border text-left">Barang</th>
                        <th class="px-3 py-2 border text-left">Peminjam</th>
                        <th class="px-3 py-2 border text-center">Jumlah</th>
                        <th class="px-3 py-2 border text-center">Tanggal Pinjam</th>
                        <th class="px-3 py-2 border text-center">Tanggal Kembali</th>
                        <th class="px-3 py-2 border text-center">Status</th>
                        <th class="px-3 py-2 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $i => $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border text-center">{{ $i + 1 }}</td>
                            <td class="px-3 py-2 border">{{ $r->barang->nama_barang ?? '-' }}</td>
                            <td class="px-3 py-2 border">{{ $r->user->nama_user ?? '-' }}</td>
                            <td class="px-3 py-2 border text-center">{{ $r->jumlah }}</td>
                            <td class="px-3 py-2 border text-center">
                                {{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 border text-center">
                                {{ $r->tanggal_kembali ? \Carbon\Carbon::parse($r->tanggal_kembali)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-3 py-2 border text-center">
                                @if ($r->status === 'dikembalikan')
                                    <span class="badge-green">Dikembalikan</span>
                                @else
                                    <span class="badge-yellow">Dipinjam</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 border text-center">
                                @if ($r->status === 'dipinjam')
                                    <form action="{{ route('peminjaman.kembalikan', $r->id_peminjaman) }}" method="POST"
                                        class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-green-700 hover:underline mr-2"
                                            onclick="return confirm('Konfirmasi pengembalian?')">
                                            Kembalikan
                                        </button>
                                    </form>
                                @endif

                                <button class="text-blue-600 hover:underline"
                                    onclick="document.getElementById('modal-{{ $r->id_peminjaman }}').classList.remove('hidden')">
                                    Edit
                                </button>
                                <!-- <form method="POST" action="{{ route('peminjaman.destroy', $r->id_peminjaman) }}"
                                    class="inline" onsubmit="return confirm('Hapus peminjaman ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form> -->
                            </td>
                        </tr>

                        {{-- MODAL EDIT --}}
                        <div id="modal-{{ $r->id_peminjaman }}" class="modal-overlay hidden"
                            onclick="if(event.target===this)this.classList.add('hidden')">
                            <div class="modal-panel animate-modal">
                                <div class="modal-header">
                                    <div class="modal-title">Edit Peminjaman</div>
                                    <button type="button" class="modal-close"
                                        onclick="document.getElementById('modal-{{ $r->id_peminjaman }}').classList.add('hidden')">✕</button>
                                </div>

                                <form method="POST" action="{{ route('peminjaman.update', $r->id_peminjaman) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="grid md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="form-label">Barang</label>
                                                <select name="id_barang" class="form-select" required>
                                                    @foreach ($barang as $b)
                                                        <option value="{{ $b->id_barang }}" @selected($b->id_barang == $r->id_barang)>
                                                            {{ $b->nama_barang }} (stok: {{ $b->qty }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Peminjam</label>
                                                <select name="id_users" class="form-select" required>
                                                    @foreach ($users as $u)
                                                        <option value="{{ $u->id_user }}" @selected($u->id_user == $r->id_users)>
                                                            {{ $u->nama_user }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Jumlah</label>
                                                <input type="number" min="1" name="jumlah"
                                                    value="{{ $r->jumlah }}" class="form-input" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Tanggal Pinjam</label>
                                                <input type="date" name="tanggal"
                                                    value="{{ \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d') }}"
                                                    class="form-input" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select"
                                                    id="status-{{ $r->id_peminjaman }}"
                                                    onchange="toggleKembali{{ $r->id_peminjaman }}(this.value)" required>
                                                    <option value="dipinjam" @selected($r->status === 'dipinjam')>Dipinjam</option>
                                                    <option value="dikembalikan" @selected($r->status === 'dikembalikan')>Dikembalikan
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Tanggal Kembali</label>
                                                <input type="date" name="tanggal_kembali"
                                                    id="tgl-{{ $r->id_peminjaman }}"
                                                    value="{{ $r->tanggal_kembali ? \Carbon\Carbon::parse($r->tanggal_kembali)->format('Y-m-d') : '' }}"
                                                    class="form-input"
                                                    {{ $r->status === 'dikembalikan' ? '' : 'disabled' }}>
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="form-label">Keterangan</label>
                                                <input name="keterangan" value="{{ $r->keterangan }}" class="form-input">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="document.getElementById('modal-{{ $r->id_peminjaman }}').classList.add('hidden')">Batal</button>
                                        <button class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <script>
                            function toggleKembali{{ $r->id_peminjaman }}(val) {
                                const el = document.getElementById('tgl-{{ $r->id_peminjaman }}');
                                if (val === 'dikembalikan') {
                                    el.removeAttribute('disabled');
                                    if (!el.value) {
                                        el.value = new Date().toISOString().slice(0, 10);
                                    }
                                } else {
                                    el.setAttribute('disabled', 'disabled');
                                    el.value = '';
                                }
                            }
                        </script>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREATE --}}
    <div id="modal-create" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="modal-panel animate-modal">
            <div class="modal-header">
                <div class="modal-title">Tambah Peminjaman</div>
                <button type="button" class="modal-close"
                    onclick="document.getElementById('modal-create').classList.add('hidden')">✕</button>
            </div>

            <form method="POST" action="{{ route('peminjaman.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Barang</label>
                            <select name="id_barang" class="form-select" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barang as $b)
                                    <option value="{{ $b->id_barang }}">{{ $b->nama_barang }} (stok:
                                        {{ $b->qty }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Peminjam</label>
                            <select name="id_users" class="form-select" required>
                                <option value="">-- Pilih User --</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id_user }}">{{ $u->nama_user }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Jumlah</label>
                            <input type="number" min="1" name="jumlah" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Tanggal Pinjam</label>
                            <input type="date" name="tanggal" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" id="create-status" class="form-select"
                                onchange="toggleCreateReturn(this.value)">
                                <option value="dipinjam" selected>Dipinjam</option>
                                <option value="dikembalikan">Dikembalikan</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" id="create-return" class="form-input" disabled>
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Keterangan</label>
                            <input name="keterangan" class="form-input" placeholder="Opsional">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-create').classList.add('hidden')">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCreateReturn(val) {
            const el = document.getElementById('create-return');
            if (val === 'dikembalikan') {
                el.removeAttribute('disabled');
                if (!el.value) {
                    el.value = new Date().toISOString().slice(0, 10);
                }
            } else {
                el.setAttribute('disabled', 'disabled');
                el.value = '';
            }
        }
    </script>

@endsection
