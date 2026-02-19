@extends('admin.layout.index')
@section('title', 'Master Unit Kerja')

@section('content')

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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Master Unit Kerja</h2>
            <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                + Tambah
            </button>
        </div>

        @if (session('success'))
            <div class="mb-4 text-sm text-green-700 bg-green-100 p-3 rounded">{{ session('success') }}</div>
        @endif

        <form method="GET" class="mb-4">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari unit kerja..."
                class="border rounded px-3 py-2 w-full md:w-72">
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 border text-left">ID</th>
                        <th class="p-2 border text-left">Unit Kerja</th>
                        <th class="p-2 border text-left">Lokasi</th>
                        <th class="p-2 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $u)
                        <tr>
                            <td class="p-2 border">{{ $u->id_unit }}</td>
                            <td class="p-2 border">{{ $u->unit_kerja }}</td>
                            <td class="p-2 border">{{ $u->lokasi ?? '-' }}</td>
                            <td class="p-2 border text-center">
                                <!-- <button type="button" class="text-blue-600 hover:underline"
                                    onclick="document.getElementById('modal-{{ $u->id_unit }}').classList.remove('hidden')">Edit</button> -->
                                <button type="button"
                                    class="ml-2"
                                    onclick="document.getElementById('modal-{{ $u->id_unit }}').classList.remove('hidden')">
                                    <img src="https://www.svgrepo.com/show/313874/edit-solid.svg"
                                        class="w-5 h-5 text-blue-600" alt="Edit Icon">
                                </button>
                                <!-- <form action="{{ route('unit.destroy', $u->id_unit) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Hapus unit ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form> -->
                            </td>
                        </tr>

                        {{-- MODAL EDIT --}}
                        <div id="modal-{{ $u->id_unit }}" class="modal-overlay hidden"
                            onclick="if(event.target===this)this.classList.add('hidden')">
                            <div class="modal-panel animate-modal">
                                <div class="modal-header">
                                    <div class="modal-title">Edit Unit Kerja — {{ $u->unit_kerja }}</div>
                                    <button type="button" class="modal-close"
                                        onclick="document.getElementById('modal-{{ $u->id_unit }}').classList.add('hidden')">✕</button>
                                </div>

                                <form method="POST" action="{{ route('unit.update', $u->id_unit) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div>
                                            <label class="form-label">Nama Unit Kerja</label>
                                            <input name="unit_kerja" class="form-input" value="{{ $u->unit_kerja }}"
                                                required>
                                        </div>
                                        <div>
                                            <label class="form-label">Lokasi</label>
                                            <input name="lokasi" class="form-input" value="{{ $u->lokasi }}">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="document.getElementById('modal-{{ $u->id_unit }}').classList.add('hidden')">Batal</button>
                                        <button class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div id="modal-create" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="modal-panel animate-modal">
            <div class="modal-header">
                <div class="modal-title">Tambah Unit Kerja</div>
                <button type="button" class="modal-close"
                    onclick="document.getElementById('modal-create').classList.add('hidden')">✕</button>
            </div>

            <form method="POST" action="{{ route('unit.store') }}">
                @csrf
                <div class="modal-body">
                    <div>
                        <label class="form-label">Nama Unit Kerja</label>
                        <input name="unit_kerja" class="form-input" placeholder="Contoh: Gudang Utama" required>
                    </div>
                    <div>
                        <label class="form-label">Lokasi</label>
                        <input name="lokasi" class="form-input" placeholder="Contoh: Surabaya">
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

@endsection
