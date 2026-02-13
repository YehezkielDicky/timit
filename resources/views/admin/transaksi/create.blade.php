@extends('admin.layout.index')
@section('title', 'Tambah Transaksi')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Tambah Transaksi</h2>

    @if ($errors->any())
    <div class="mb-4 text-sm text-red-700 bg-red-100 p-3 rounded">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('transaksi.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- No Surat --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">No Surat</label>
            <input type="text" name="no_surat" class="border rounded w-full p-2" required>
        </div>

        {{-- Jenis Transaksi --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Jenis Transaksi</label>
            <select name="jenis" class="border rounded w-full p-2" required>
                <option value="masuk">Barang Masuk</option>
                <option value="keluar">Barang Keluar</option>
            </select>
        </div>

        {{-- Tanggal --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Tanggal</label>
            <input type="date" name="tanggal" class="border rounded w-full p-2" required>
        </div>

        {{-- Keterangan --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Keterangan</label>
            <textarea name="keterangan" class="border rounded w-full p-2" rows="2"></textarea>
        </div>

        {{-- Unit Kerja --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Unit Kerja</label>
            <select name="id_unit" class="border rounded w-full p-2">
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $u)
                <option value="{{ $u->id_unit }}">
                    {{ $u->unit_kerja }} @if ($u->lokasi) ({{ $u->lokasi }}) @endif
                </option>
                @endforeach
            </select>
        </div>

        <!-- {{-- Upload File --}}
        <div class="grid md:grid-cols-2 gap-4 mb-3">
            <div>
                <label class="block text-sm font-medium mb-1">Upload Tanda Terima</label>
                <input type="file" name="tanda_terima" accept=".pdf,.doc,.docx"
                    class="border rounded w-full p-2 bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Upload Berita Acara</label>
                <input type="file" name="berita_acara" accept=".pdf,.doc,.docx"
                    class="border rounded w-full p-2 bg-gray-50">
            </div>
        </div> -->
        
        {{-- Upload File BA & TT --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">
                Upload Dokumen (Tanda Terima & Berita Acara)
            </label>

            <input type="file"
                name="dokumen[]"
                accept=".pdf,.doc,.docx"
                class="border rounded w-full p-2 bg-gray-50"
                multiple>

            <p class="text-xs text-gray-600 mt-1">
                Upload MAX 2 file: 1 Tanda Terima, 1 Berita Acara.
                <br>Nama file harus mengandung kata <b>"TT"</b> untuk Tanda Terima dan <b>"BA"</b> untuk Berita Acara.
            </p>
        </div>

        <hr class="my-4">

        {{-- Barang --}}
        <h3 class="font-semibold mb-2">Barang yang Ditransaksikan</h3>

        <div id="item-container">
            <div class="item-row mb-2 flex gap-2 items-center">
                <select name="items[0][id_barang]" class="border rounded w-full p-2" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach ($barang as $b)
                    <option value="{{ $b->id_barang }}">{{ $b->nama_barang }}</option>
                    @endforeach
                </select>

                <input type="number"
                    name="items[0][qty]"
                    class="border rounded w-32 p-2"
                    placeholder="Qty"
                    min="1"
                    required>

                <button type="button"
                    class="remove-item bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                    ❌
                </button>
            </div>
        </div>

        <button type="button" id="addItem"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
            + Tambah Barang
        </button>

        <div class="mt-6">
            <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Simpan Transaksi
            </button>
            <a href="{{ route('transaksi.index') }}"
                class="ml-2 text-gray-700 hover:underline">
                Batal
            </a>
        </div>
    </form>
</div>

{{-- ================= JAVASCRIPT ================= --}}
<script>
    const container = document.getElementById('item-container');
    const addItemBtn = document.getElementById('addItem');

    addItemBtn.addEventListener('click', () => {
        const firstRow = container.querySelector('.item-row');
        const clone = firstRow.cloneNode(true);

        clone.querySelectorAll('select, input').forEach(el => el.value = '');
        container.appendChild(clone);

        reindexItems();
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const rows = container.querySelectorAll('.item-row');

            if (rows.length === 1) {
                alert('Minimal harus ada 1 barang');
                return;
            }

            e.target.closest('.item-row').remove();
            reindexItems();
        }
    });

    function reindexItems() {
        const rows = container.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            row.querySelectorAll('select, input').forEach(el => {
                el.name = el.name.replace(/\[\d+]/, `[${index}]`);
            });
        });
    }

    document.querySelector('form').addEventListener('submit', () => {
        reindexItems();
    });
    
</script>
@endsection