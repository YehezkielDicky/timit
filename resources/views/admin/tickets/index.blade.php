@extends('admin.layout.index')
@section('title', 'Tiket')

@section('content')

    @php
        $isOfficer = ($role ?? '') === 'officer';
    @endphp

    <div class="bg-white p-4 rounded shadow">

        {{-- Judul --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">
                {{ $isOfficer ? 'Riwayat Tiket Saya' : 'Pending Ticket' }}
            </h2>

            {{-- Officer boleh tambah tiket --}}
            @if ($isOfficer)
                <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
                    + Tambah Tiket
                </a>
            @endif
        </div>

        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-2 w-12">No</th>
                    <th class="border px-2 py-2">Ticket</th>
                    <th class="border px-2 py-2">Type</th>
                    <th class="border px-2 py-2">Nama Pelapor</th>
                    <th class="border px-2 py-2">Kontak</th>
                    <th class="border px-2 py-2">Deskripsi</th>
                    <th class="border px-2 py-2">Unit</th>
                    <th class="border px-2 py-2">Status</th>

                    @unless ($isOfficer)
                        <th class="border px-2 py-2 w-32">Aksi</th>
                    @endunless
                </tr>
            </thead>

            <tbody>
                @forelse ($tickets as $i => $t)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $t->ticket_number }}</td>
                        <td class="border px-2 py-1">{{ $t->judul }}</td>
                        <td class="border px-2 py-1">{{ $t->nama_pelapor }}</td>
                        <td class="border px-2 py-1">{{ $t->kontak_pelapor }}</td>
                        <td class="border px-2 py-1">{{ $t->deskripsi }}</td>
                        <div class="text-xs text-gray-600">{{ Str::limit($t->deskripsi, 80) }}</div>
                        <td class="border px-2 py-1">
                            {{ $t->unit ? $t->unit->unit_kerja . ' (' . $t->unit->lokasi . ')' : '-' }}
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <span
                                class="px-2 py-1 rounded text-xs
                            {{ $t->status === 'new' ? 'bg-yellow-200' : ($t->status === 'process' ? 'bg-blue-200' : 'bg-green-200') }}">
                                {{ strtoupper($t->status) }}
                            </span>
                        </td>

                        {{-- AKSI --}}
                        @unless ($isOfficer)
                            <td class="border px-2 py-1 text-center">
                                @if ($t->status === 'new')
                                    <a href="{{ route('tickets.take', $t->id_ticket) }}"
                                        class="px-3 py-1 bg-blue-600 text-white rounded text-xs">
                                        Ambil
                                    </a>
                                @elseif ($t->status === 'process')
                                    <a href="{{ route('tickets.done', $t->id_ticket) }}"
                                        class="px-3 py-1 bg-green-600 text-white rounded text-xs">
                                        Selesai
                                    </a>
                                @endif
                            </td>
                        @endunless
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isOfficer ? 5 : 6 }}" class="text-center py-4 text-gray-500">
                            Tidak ada tiket
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection