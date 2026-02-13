@extends('admin.layout.index')

@section('title', 'Tambah Tiket')

@section('content')
    <div class="bg-white p-6 rounded shadow max-w-xl">
        <h2 class="text-xl font-semibold mb-4">Input Tiket</h2>

        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf

            <label class="block mb-1">Judul</label>
            <input name="judul" class="w-full border rounded p-2 mb-3" required>

            <label class="block mb-1">Deskripsi</label>
            <textarea name="deskripsi" class="w-full border rounded p-2 mb-3" required></textarea>

            <label class="block mb-1">Nama Pelapor</label>
            <input name="nama_pelapor" class="w-full border rounded p-2 mb-3" required>

            <label class="block mb-1">Kontak Pelapor</label>
            <input name="kontak_pelapor" class="w-full border rounded p-2 mb-3">

            <label class="block mb-1">Unit Kerja</label>
            <select name="id_unit" class="w-full border rounded p-2 mb-4" required>
                <option value="">-- Pilih Unit --</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id_unit }}">{{ $u->unit_kerja }} ({{ $u->lokasi }})</option>
                @endforeach
            </select>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Simpan
            </button>
        </form>
    </div>
@endsection