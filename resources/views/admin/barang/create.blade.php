@extends('admin.layout.index')
@section('title', 'Tambah Barang')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Tambah Barang</h2>
                <a href="{{ route('barang.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
            </div>

            {{-- Alert error singkat (jika ada) --}}
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-700 bg-red-100 border border-red-200 px-3 py-2 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('barang.store') }}" class="space-y-4">
                @csrf

                {{-- Nama Barang --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="nama_barang">
                        Nama Barang <span class="text-red-600">*</span>
                    </label>
                    <input id="nama_barang" name="nama_barang" type="text" value="{{ old('nama_barang') }}"
                        class="w-full rounded-md border @error('nama_barang') border-red-400 @else border-gray-300 @enderror px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="cth: SSD 512GB" required autofocus>
                    @error('nama_barang')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-xs text-gray-500">Gunakan nama yang mudah dicari.</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                        Simpan
                    </button>
                    <a href="{{ route('barang.index') }}" class="px-4 py-2 rounded-md border hover:bg-gray-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
