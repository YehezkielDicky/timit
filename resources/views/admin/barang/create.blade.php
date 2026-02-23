@extends('admin.layout.index')
@section('title', 'Tambah Barang')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Tambah Barang</h2>
                <a href="{{ route('barang.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
            </div>

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-700 bg-red-100 border border-red-200 px-3 py-2 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('barang.store') }}" class="space-y-4">
                @csrf

                {{-- Nama Barang --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang *</label>
                    <input name="nama_barang" type="text" value="{{ old('nama_barang') }}"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-indigo-500"
                        placeholder="cth: SSD 512GB" required>
                </div>

                {{-- Kategori Barang --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Kategori *</label>

                        <div class="flex gap-2">
                            <button type="button"
                                onclick="openKategoriModal()"
                                class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-2 py-1 rounded">
                                + Tambah
                            </button>

                            <button type="button"
                                onclick="openKelolaKategoriModal()"
                                class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded">
                                Kelola
                            </button>
                        </div>
                    </div>

                    <select name="id_kategori" id="kategori_select"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-indigo-500" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Simpan</button>
                    <a href="{{ route('barang.index') }}" class="px-4 py-2 rounded border hover:bg-gray-50">Batal</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== MODAL TAMBAH KATEGORI ==================== --}}
    <div id="modal-kategori" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-sm rounded-xl shadow p-5">
            <h3 class="text-lg font-semibold mb-3">Tambah Kategori</h3>

            <div>
                <label class="text-sm text-gray-700 mb-1">Nama Kategori</label>
                <input id="kategori_nama" class="w-full border rounded px-3 py-2" placeholder="cth: Kabel / SSD / RAM">
                <p id="kategori_error" class="text-red-600 text-xs mt-1 hidden"></p>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button onclick="closeKategoriModal()" class="px-3 py-2 rounded border">Batal</button>
                <button onclick="simpanKategori()"
                    class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL KELOLA KATEGORI ==================== --}}
    <div id="modal-kelola-kategori" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-md rounded-xl shadow p-5">
            <h3 class="text-lg font-semibold mb-3">Kelola Kategori</h3>

            <div id="kelola_list" class="space-y-2 text-sm">
                <!-- Diisi oleh AJAX -->
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button onclick="closeKelolaKategoriModal()" class="px-3 py-2 rounded border">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // ==================== MODAL TAMBAH ====================
        function openKategoriModal() {
            document.getElementById('modal-kategori').classList.remove('hidden');
            document.getElementById('modal-kategori').classList.add('flex');
        }

        function closeKategoriModal() {
            document.getElementById('modal-kategori').classList.add('hidden');
            document.getElementById('modal-kategori').classList.remove('flex');
            document.getElementById('kategori_error').classList.add('hidden');
        }

        async function simpanKategori() {
            const nama = document.getElementById('kategori_nama').value;
            const error = document.getElementById('kategori_error');

            if (!nama.trim()) {
                error.innerText = "Nama kategori tidak boleh kosong";
                error.classList.remove('hidden');
                return;
            }

            const res = await fetch("{{ route('kategori.store.ajax') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ nama_kategori: nama })
            });
            

            const json = await res.json();

            if (json.status == "ok") {
                const select = document.getElementById('kategori_select');
                const opt = document.createElement("option");

                opt.value = json.data.id_kategori;
                opt.textContent = json.data.nama_kategori;
                select.appendChild(opt);
                select.value = json.data.id_kategori;

                closeKategoriModal();
                document.getElementById('kategori_nama').value = "";
            } else {
                error.innerText = json.message;
                error.classList.remove('hidden');
            }
        }


        // ==================== MODAL KELOLA (EDIT + DELETE) ====================
        async function openKelolaKategoriModal() {
            const modal = document.getElementById('modal-kelola-kategori');
            const list = document.getElementById('kelola_list');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            list.innerHTML = "Memuat...";

            const res = await fetch("{{ route('kategori.list.ajax') }}");
            const json = await res.json();

            list.innerHTML = "";

            json.data.forEach(k => {
                const row = document.createElement("div");
                row.className = "flex items-center justify-between bg-gray-50 p-2 rounded";

                row.innerHTML = `
                    <span>${k.nama_kategori}</span>

                    <div class="flex gap-2">
                        <button class="px-2 py-1 text-xs bg-blue-600 text-white rounded"
                            onclick="editKategori(${k.id_kategori}, '${k.nama_kategori}')">Edit</button>

                        <button class="px-2 py-1 text-xs bg-red-600 text-white rounded"
                            onclick="hapusKategori(${k.id_kategori})">Hapus</button>
                    </div>
                `;

                list.appendChild(row);
            });
        }

        function closeKelolaKategoriModal() {
            document.getElementById('modal-kelola-kategori').classList.add('hidden');
            document.getElementById('modal-kelola-kategori').classList.remove('flex');
        }

        // EDIT
        async function editKategori(id, oldName) {
            const nama = prompt("Edit kategori:", oldName);
            if (!nama) return;

            const res = await fetch(`{{ url('/kategori/update-ajax') }}/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ nama_kategori: nama })
            });

            const json = await res.json();

            if (json.status == "ok") {
                alert("Kategori berhasil diupdate!");
                openKelolaKategoriModal();
                refreshKategoriDropdown();
            }
        }

        // DELETE
        async function hapusKategori(id) {
            if (!confirm("Yakin hapus kategori?")) return;

            const res = await fetch(`{{ url('/kategori/delete-ajax') }}/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            });

            const json = await res.json();

            if (json.status == "ok") {
                alert("Kategori berhasil dihapus!");
                openKelolaKategoriModal();
                refreshKategoriDropdown();
            }
        }

        // REFRESH DROPDOWN
        async function refreshKategoriDropdown() {
            const res = await fetch("{{ route('kategori.list.ajax') }}");
            const json = await res.json();

            const select = document.getElementById("kategori_select");
            select.innerHTML = `<option value="">Pilih kategori</option>`;

            json.data.forEach(k => {
                const opt = document.createElement("option");
                opt.value = k.id_kategori;
                opt.textContent = k.nama_kategori;
                select.appendChild(opt);
            });
        }
    </script>
@endsection